<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;

class SupplierProductController extends Controller
{
    /**
     * Tampilkan halaman index (semua supplier beserta produknya)
     */
    public function index()
    {
        $suppliers = Supplier::with('products')->latest()->get();
        $products = Product::orderBy('name')->get();   // sesuaikan dengan nama kolom di tabel products

        return view('supplier_product.index', compact('suppliers', 'products'));
    }

    /**
     * Simpan relasi supplier - product + harga
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id'  => 'required|exists:products,id',
            'price'       => 'required|numeric|min:0',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);

        // Tambahkan atau update harga jika sudah ada
        $supplier->products()->syncWithoutDetaching([
            $request->product_id => [
                'price' => $request->price
            ]
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan ke supplier');
    }

    /**
     * Update harga relasi (jika diperlukan)
     */
    public function update(Request $request, $supplierId, $productId)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $supplier = Supplier::findOrFail($supplierId);

        $supplier->products()->updateExistingPivot($productId, [
            'price' => $request->price
        ]);

        return back()->with('success', 'Harga berhasil diperbarui');
    }

    /**
     * Hapus relasi supplier - product
     */
    public function destroy($supplierId, $productId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        $supplier->products()->detach($productId);

        return back()->with('success', 'Relasi produk berhasil dihapus dari supplier');
    }

    /**
     * Optional: Tampilkan form edit harga (jika ingin halaman terpisah)
     */
    public function edit($supplierId, $productId)
    {
        $supplier = Supplier::with('products')->findOrFail($supplierId);
        $product = $supplier->products()->where('product_id', $productId)->firstOrFail();

        return view('supplier_product.edit', compact('supplier', 'product'));
    }
}