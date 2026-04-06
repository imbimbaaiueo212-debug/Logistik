<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'nullable|string|unique:products,sku',
            'jenis'         => 'nullable|string',
            'satuan'        => 'nullable|string',
            'berat_satuan'  => 'nullable|numeric|min:0',
            'isi'           => 'nullable|integer|min:1',
            'harga_beli'    => 'nullable|numeric|min:0',
            'status'        => 'nullable|string',
            'role'          => 'nullable|in:jual,tidak_dijual,stock',
            'tanggal_rilis' => 'nullable|date',
            'hal'           => 'nullable|integer',
            'lembar'        => 'nullable|integer',
            'kertas'        => 'nullable|string',
        ]);

        $data = $request->all();

        // Hapus 'label' karena sudah tidak dipakai
        unset($data['label']);

        // ====================== HITUNG BERAT PAKET ======================
        if (!empty($data['berat_satuan']) && !empty($data['isi'])) {
            $data['berat_paket'] = round($data['berat_satuan'] * $data['isi'], 3);
        }

        // ================== RUMUS HARGA JUAL OTOMATIS ==================
        if (!empty($data['harga_beli']) && !empty($data['isi']) && $data['isi'] > 0) {
            
            $hargaBeli = (float) $data['harga_beli'];
            $isi       = (int) $data['isi'];
            $jenis     = strtolower(trim($data['jenis'] ?? ''));

            $multiplier = (strpos($jenis, 'modul') !== false) ? 1.49 : 1.20;

            $hargaDasar = $hargaBeli * $multiplier;
            $hargaJual  = $hargaDasar * $isi;

            // Bulatkan ke atas ke kelipatan 50
            $data['harga_jual'] = ceil($hargaJual / 50) * 50;
        }

        Product::create($data);

        return redirect()->route('products.index')
                         ->with('success', 'Product berhasil ditambahkan');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'sku'           => 'nullable|string|unique:products,sku,' . $product->id,
            'jenis'         => 'nullable|string',
            'satuan'        => 'nullable|string',
            'berat_satuan'  => 'nullable|numeric|min:0',
            'isi'           => 'nullable|integer|min:1',
            'harga_beli'    => 'nullable|numeric|min:0',
            'status'        => 'nullable|string',
            'role'          => 'nullable|in:jual,tidak_dijual,stock',
            'tanggal_rilis' => 'nullable|date',
            'hal'           => 'nullable|integer',
            'lembar'        => 'nullable|integer',
            'kertas'        => 'nullable|string',
        ]);

        $data = $request->all();

        // Hapus 'label' karena sudah tidak dipakai
        unset($data['label']);

        // ====================== HITUNG BERAT PAKET ======================
        if (!empty($data['berat_satuan']) && !empty($data['isi'])) {
            $data['berat_paket'] = round($data['berat_satuan'] * $data['isi'], 3);
        }

        // ================== RUMUS HARGA JUAL OTOMATIS ==================
        if (!empty($data['harga_beli']) && !empty($data['isi']) && $data['isi'] > 0) {
            
            $hargaBeli = (float) $data['harga_beli'];
            $isi       = (int) $data['isi'];
            $jenis     = strtolower(trim($data['jenis'] ?? ''));

            $multiplier = (strpos($jenis, 'modul') !== false) ? 1.49 : 1.20;

            $hargaDasar = $hargaBeli * $multiplier;
            $hargaJual  = $hargaDasar * $isi;

            $data['harga_jual'] = ceil($hargaJual / 50) * 50;
        }

        $product->update($data);

        return redirect()->route('products.index')
                         ->with('success', 'Product berhasil diupdate');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
                         ->with('success', 'Product berhasil dihapus');
    }
}