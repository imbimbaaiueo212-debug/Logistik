<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\QuotationItem;
use App\Models\QuotationSupplier;
use App\Models\QuotationSupplierItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
{
    $quotations = Quotation::with([
        'items.product',
        'suppliers.supplier',
        'supplierItems'
    ])->latest()->get();

    return view('quotation.index', compact('quotations'));
}

    public function create()
    {
        $products = Product::all();
        $suppliers = Supplier::all();

        return view('quotation.create', compact('products','suppliers'));
    }

    public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'date' => 'required|date',
        'items' => 'required|array|min:1'
    ]);

    DB::transaction(function () use ($request) {

        $quotation = Quotation::create([
            'number' => 'QT-' . time(),
            'date' => $request->date,
            'status' => 'draft',
            'total' => $request->total ?? 0
        ]);

        $qs = QuotationSupplier::create([
            'quotation_id' => $quotation->id,
            'supplier_id' => $request->supplier_id
        ]);

        foreach ($request->items as $item) {

            if (
                empty($item['product_id']) ||
                empty($item['qty']) ||
                $item['qty'] <= 0
            ) continue;

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty']
            ]);

            QuotationSupplierItem::create([
                'quotation_supplier_id' => $qs->id,
                'product_id' => $item['product_id'],
                'price' => isset($item['price']) ? (float)$item['price'] : 0
            ]);
        }
    });

    return redirect()->route('quotation.index')
        ->with('success', 'Quotation berhasil dibuat');
}

public function edit($id)
{
    $quotation = Quotation::with([
        'items.product',
        'suppliers.supplier',
        'suppliers.items'
    ])->findOrFail($id);

    $suppliers = Supplier::all();

    return view('quotation.edit', compact('quotation', 'suppliers'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'date' => 'required|date',
        'items' => 'required|array|min:1'
    ]);

    DB::transaction(function () use ($request, $id) {

        $quotation = Quotation::findOrFail($id);

        // update header
        $quotation->update([
            'date' => $request->date,
            'total' => $request->total ?? 0
        ]);

        // 🔥 HAPUS ITEM LAMA
        QuotationItem::where('quotation_id', $quotation->id)->delete();

        // 🔥 AMBIL supplier quotation
        $qs = QuotationSupplier::where('quotation_id', $quotation->id)->first();

        QuotationSupplierItem::where('quotation_supplier_id', $qs->id)->delete();

        // 🔥 SIMPAN ULANG
        foreach ($request->items as $item) {

            if (
                empty($item['product_id']) ||
                empty($item['qty']) ||
                $item['qty'] <= 0
            ) continue;

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty']
            ]);

            QuotationSupplierItem::create([
                'quotation_supplier_id' => $qs->id,
                'product_id' => $item['product_id'],
                'price' => $item['price'] ?? 0
            ]);
        }
    });

    return redirect()->route('quotation.show', $id)
        ->with('success', 'Quotation berhasil diupdate');
}

    public function show($id)
{
    $quotation = Quotation::with(
        'items.product',
        'suppliers.supplier',
        'suppliers.items.product'
    )->findOrFail($id);

    return view('quotation.show', compact('quotation'));
}

    // 🔥 CONVERT KE PO
   public function convertToPO($id, $supplierId)
{
    $quotation = Quotation::with([
        'items',
        'suppliers.items' // ambil harga supplier
    ])->findOrFail($id);

    // ❗ VALIDASI STATUS
    if ($quotation->status !== 'approved') {
        return back()->with('error', 'Quotation harus di-approve dulu');
    }

    DB::transaction(function () use ($quotation, $supplierId) {

    $po = PurchaseOrder::create([
        'supplier_id' => $supplierId,
        'date' => now(),
        'status' => 'draft',
        'total' => 0 // optional
    ]);

    $supplier = $quotation->suppliers
        ->where('supplier_id', $supplierId)
        ->first();

    if (!$supplier) {
        throw new \Exception('Supplier tidak ditemukan di quotation');
    }

    $total = 0;

    foreach ($quotation->items as $item) {

        $supplierItem = $supplier->items
            ->where('product_id', $item->product_id)
            ->first();

        $price = $supplierItem->price ?? 0;
        $subtotal = $item->qty * $price;

        $total += $subtotal;

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $item->product_id,
            'qty' => $item->qty,
            'price' => $price,
            'subtotal' => $subtotal
        ]);
    }

    // 🔥 FIX TOTAL DI SINI
    $po->update([
        'total' => $total
    ]);

    $quotation->update([
        'status' => 'created PO'
    ]);
});

    return back()->with('success', 'Berhasil convert ke Purchase Order');
}

   public function getProductsBySupplier($supplierId)
{
    $supplier = Supplier::with('products')->findOrFail($supplierId);

    $products = $supplier->products->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->pivot->price ?? 0
        ];
    });

    return response()->json($products);
}

public function send($id)
{
    $quotation = Quotation::findOrFail($id);

    $quotation->update([
        'status' => 'sent'
    ]);

    return back()->with('success', 'Quotation berhasil dikirim');
}

public function approve($id)
{
    $quotation = Quotation::findOrFail($id);

    if ($quotation->status !== 'sent') {
        return back()->with('error', 'Quotation belum dikirim');
    }

    $quotation->update([
        'status' => 'approved'
    ]);

    return back()->with('success', 'Quotation disetujui');
}

public function reject($id)
{
    $quotation = Quotation::findOrFail($id);

    $quotation->update([
        'status' => 'rejected'
    ]);

    return back()->with('success', 'Quotation ditolak');
}

}