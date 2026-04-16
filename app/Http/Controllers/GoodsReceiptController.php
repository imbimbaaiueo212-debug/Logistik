<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Warehouse;
use App\Models\RejectItem;
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
        $warehouses = Warehouse::all();

        return view('gr.create', compact('pos', 'warehouses'));
    }

    public function getPO($id)
    {
        $po = PurchaseOrder::with('items.product')->find($id);
        return response()->json($po);
    }

    /*
    |--------------------------------------------------
    | STORE (TANPA STOCK!)
    |--------------------------------------------------
    */
    public function store(Request $request)
{
    $request->validate([
        'po_id' => 'required|exists:purchase_orders,id',
        'warehouse_id' => 'required|exists:warehouses,id',
    ]);

    $gr = GoodsReceipt::create([
        'purchase_order_id' => $request->po_id,
        'date' => now(),
        'warehouse_id' => $request->warehouse_id,
    ]);

    foreach ($request->items as $item) {
        $qty = (int) ($item['qty'] ?? 0);

        if ($qty <= 0) continue;

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'product_id'       => $item['product_id'],
            'qty_received'     => $qty,                    // ← Gunakan ini
            'price'            => $item['price'] ?? 0,
            'qty_ok'           => 0,
            'qty_reject'       => 0,
            'qc_status'        => 'pending',               // ← Sesuaikan
        ]);
    }

    return redirect()->route('gr.index')
        ->with('success', 'GR dibuat. Lanjut QC.');
}

    /*
    |--------------------------------------------------
    | QC PAGE
    |--------------------------------------------------
    */
    public function qcPage($id)
    {
        $gr = GoodsReceipt::with('items.product')->findOrFail($id);
        return view('gr.qc', compact('gr'));
    }

    /*
    |--------------------------------------------------
    | QC PROCESS (INI INTI NYA)
    |--------------------------------------------------
    */
    public function qc(Request $request, $id)
{
    $item = GoodsReceiptItem::with('receipt')->findOrFail($id);

    $request->validate([
        'qty_ok' => 'required|integer|min:0',
        'qty_reject' => 'required|integer|min:0',
    ]);

    // ❌ jangan QC 2x
    if ($item->qc_status === 'done') {
        return response()->json(['error' => 'Sudah QC'], 400);
    }

    // ❌ validasi jumlah
    if (($request->qty_ok + $request->qty_reject) != $item->qty_received) {
        return response()->json(['error' => 'Total tidak sesuai'], 400);
    }

    DB::transaction(function () use ($item, $request) {

        // =========================
        // 1. UPDATE QC
        // =========================
        $item->update([
            'qty_ok' => $request->qty_ok,
            'qty_reject' => $request->qty_reject,
            'qc_status' => 'done',
        ]);

        // =========================
        // 2. STOCK MASUK (OK)
        // =========================
        if ($request->qty_ok > 0) {

            app(\App\Services\StockService::class)->stockIn(
                $item->product_id,
                $item->receipt->warehouse_id,
                $request->qty_ok,
                $item->receipt->id
            );
        }

        // =========================
        // 3. STOCK REJECT
        // =========================
        if ($request->qty_reject > 0) {

            $rejectWarehouseId = 99; // 🔥 ganti sesuai gudang reject

            // masuk ke gudang reject
            \App\Models\Stock::updateOrCreate(
                [
                    'warehouse_id' => $rejectWarehouseId,
                    'product_id' => $item->product_id
                ],
                [
                    'qty' => DB::raw("qty + {$request->qty_reject}")
                ]
            );

            // =========================
            // 4. SIMPAN KE reject_items
            // =========================
            \App\Models\RejectItem::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $item->receipt->warehouse_id,
                'qty' => $request->qty_reject,
                'reason' => $request->reason ?? null,
                'status' => 'pending'
            ]);
        }
    });

    return response()->json([
        'success' => true,
        'redirect' => route('gr.index')
    ]);
}

    /*
    |--------------------------------------------------
    | DELETE (HANYA YANG BELUM QC)
    |--------------------------------------------------
    */
    public function destroy($id)
    {
        $gr = GoodsReceipt::with('items')->findOrFail($id);

        foreach ($gr->items as $item) {
            if ($item->is_qc_done) {
                throw new \Exception('Tidak bisa hapus, sudah QC');
            }
        }

        $gr->items()->delete();
        $gr->delete();

        return redirect()->route('gr.index')
            ->with('success', 'Data berhasil dihapus');
    }

    public function edit($id)
{
    $gr = GoodsReceipt::with('items.product')->findOrFail($id);

    // ❌ CEK SUDAH QC
    foreach ($gr->items as $item) {
        if ($item->qc_status == 'done') {
            return back()->with('error', 'Tidak bisa edit, sudah QC');
        }
    }

    $pos = PurchaseOrder::all();
    $warehouses = Warehouse::all();

    return view('gr.edit', compact('gr', 'pos', 'warehouses'));
}

public function update(Request $request, $id)
{
    $gr = GoodsReceipt::with('items')->findOrFail($id);

    // ❌ CEK SUDAH QC
    foreach ($gr->items as $item) {
        if ($item->qc_status == 'done') {
            return back()->with('error', 'Tidak bisa update, sudah QC');
        }
    }

    DB::transaction(function () use ($request, $gr) {

        // 🔥 UPDATE HEADER
        $gr->update([
            'purchase_order_id' => $request->po_id,
            'warehouse_id' => $request->warehouse_id,
        ]);

        // 🔥 HAPUS ITEM LAMA
        $gr->items()->delete();

        // 🔥 INSERT ULANG
        foreach ($request->items as $item) {

            $qty = (int) ($item['qty'] ?? 0);

            if ($qty <= 0) continue;

            GoodsReceiptItem::create([
                'goods_receipt_id' => $gr->id,
                'product_id'       => $item['product_id'],
                'qty_received'     => $qty,
                'price'            => $item['price'] ?? 0,
                'qty_ok'           => 0,
                'qty_reject'       => 0,
                'qc_status'        => 'pending',
            ]);
        }
    });

    return redirect()->route('gr.index')
        ->with('success', 'GR berhasil diupdate');
}
}