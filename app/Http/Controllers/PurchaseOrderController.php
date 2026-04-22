<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::with('supplier')->latest()->get();
        return view('po.index', compact('pos'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('po.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'products'    => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty'        => 'required|integer|min:1',
            'products.*.price'      => 'required|integer|min:0',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $request->supplier_id,
            'date'        => now(),
            'total'       => 0,
        ]);

        $total = 0;

        foreach ($request->products as $p) {
            $subtotal = $p['qty'] * $p['price'];

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $p['product_id'],
                'qty'               => $p['qty'],
                'price'             => $p['price'],
                'subtotal'          => $subtotal,
            ]);

            $total += $subtotal;
        }

        $po->update(['total' => $total]);

        return redirect()->route('po.index')
                         ->with('success', 'Purchase Order berhasil dibuat');
    }

    // ================== EDIT ==================
    public function edit(PurchaseOrder $po)
{
    $suppliers = Supplier::all();
    // eager load items + product
    $po->load('items.product');   // atau sesuai relasi kamu

    return view('po.edit', compact('po', 'suppliers'));
}

    public function update(Request $request, PurchaseOrder $po)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'products'    => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty'        => 'required|integer|min:1',
            'products.*.price'      => 'required|integer|min:0',
        ]);

        // Update header PO
        $po->update([
            'supplier_id' => $request->supplier_id,
            'date'        => now(),
        ]);

        // Hapus semua item lama
        $po->items()->delete();

        // Insert item baru
        $total = 0;
        foreach ($request->products as $p) {
            $subtotal = $p['qty'] * $p['price'];

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $p['product_id'],
                'qty'               => $p['qty'],
                'price'             => $p['price'],
                'subtotal'          => $subtotal,
            ]);

            $total += $subtotal;
        }

        $po->update(['total' => $total]);

        return redirect()->route('po.index')
                         ->with('success', 'Purchase Order berhasil diupdate');
    }

    // ================== DELETE ==================
    public function destroy(PurchaseOrder $po)
    {
        // Karena migration sudah pakai cascadeOnDelete, items otomatis terhapus
        $po->delete();

        return redirect()->route('po.index')
                         ->with('success', 'Purchase Order berhasil dihapus');
    }

    public function show($id)
{
    $po = PurchaseOrder::with(['supplier', 'items.product'])->findOrFail($id);

    return view('po.show', compact('po'));
}

}