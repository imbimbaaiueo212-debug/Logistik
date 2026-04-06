<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $receipts = GoodsReceipt::with(['items.product', 'purchaseOrder'])->latest()->get();

        return view('gr.index', compact('receipts'));
    }
    public function create()
{
    $pos = PurchaseOrder::all();
    $warehouses = Warehouse::all(); // 🔥 tambah ini

    return view('gr.create', compact('pos', 'warehouses'));
}

    public function getPO($id)
    {
        $po = PurchaseOrder::with('items.product')->find($id);
        return response()->json($po);
    }

    public function store(Request $request)
{
    $request->validate([
        'po_id' => 'required|exists:purchase_orders,id',
        'warehouse_id' => 'required|exists:warehouses,id',
    ]);

    $poId        = $request->po_id;
    $warehouseId = $request->warehouse_id;
    $allowOver   = $request->boolean('allow_over');   // Lebih bersih

    // Ambil data PO Items
    $poItems = \App\Models\PurchaseOrderItem::where('purchase_order_id', $poId)
                ->pluck('qty', 'product_id');

    // Hitung total yang sudah diterima sebelumnya
    $currentReceived = GoodsReceiptItem::whereHas('receipt', function ($q) use ($poId) {
            $q->where('purchase_order_id', $poId);
        })
        ->selectRaw('product_id, SUM(qty) as total')
        ->groupBy('product_id')
        ->pluck('total', 'product_id');

    // === VALIDASI SEMUA ITEM DULU (PENTING!) ===
    foreach ($request->items as $index => $item) {
        if (empty($item['qty']) || ($item['qty'] ?? 0) <= 0) continue;

        $productId = $item['product_id'];
        $qtyInput  = (int) $item['qty'];

        $qtyPO           = $poItems[$productId] ?? 0;
        $alreadyReceived = $currentReceived[$productId] ?? 0;
        $sisa            = $qtyPO - $alreadyReceived;

        if ($qtyInput > $sisa && !$allowOver) {
            return back()
                ->withInput()                    // Penting: supaya form tetap terisi
                ->with('error', "Qty untuk produk ID {$productId} melebihi sisa PO ({$sisa}). 
                       Centang 'Izinkan kelebihan barang' jika ingin melanjutkan.");
        }
    }

    // === BARU SIMPAN SETELAH SEMUA VALIDASI LOLOS ===
    $gr = GoodsReceipt::create([
        'purchase_order_id' => $poId,
        'date'              => now(),
        'warehouse_id'      => $warehouseId,
    ]);

    foreach ($request->items as $index => $item) {
        if (($item['qty'] ?? 0) <= 0) continue;

        $productId = $item['product_id'];
        $qtyInput  = (int) $item['qty'];

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'product_id'       => $productId,
            'qty'              => $qtyInput,
            'price'            => $item['price'] ?? 0,
        ]);

        // Update Stock
        $stock = Stock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['qty' => 0]
        );
        $stock->increment('qty', $qtyInput);
    }

    return redirect()->route('gr.index')
        ->with('success', 'Goods Receipt berhasil disimpan & stok bertambah.');
}


    public function edit($id)
{
    $gr = GoodsReceipt::with('items.product')->findOrFail($id);
    return view('gr.edit', compact('gr'));
}
public function update(Request $request, $id)
{
    $gr = GoodsReceipt::findOrFail($id);

    foreach ($request->items as $item) {

        $grItem = GoodsReceiptItem::find($item['id']);

        $diff = $item['qty'] - $grItem->qty;

        $stock = Stock::where('product_id', $grItem->product_id)
            ->where('warehouse_id', $gr->warehouse_id) // 🔥 WAJIB
            ->first();

        if ($stock) {
            $stock->qty += $diff;
            $stock->save();
        }

        $grItem->update([
            'qty' => $item['qty']
        ]);
    }

    return redirect()->route('gr.index')->with('success', 'Data berhasil diupdate');
}
public function destroy($id)
{
    $gr = GoodsReceipt::with('items')->findOrFail($id);

    foreach ($gr->items as $item) {

        $stock = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $gr->warehouse_id) // 🔥 WAJIB
            ->first();

        if ($stock) {
            $stock->qty -= $item->qty;
            $stock->save();
        }
    }

    $gr->items()->delete();
    $gr->delete();

    return redirect()->route('gr.index')->with('success', 'Data berhasil dihapus');
}
}