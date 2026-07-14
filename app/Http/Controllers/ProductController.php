<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    public function index(Request $request)
{
    $perPage = (int) $request->get('per_page', 20);

    if (!in_array($perPage, [10,20,50,100,250,500,1000,5000])) {
        $perPage = 20;
    }

    $query = Product::with('category');

    if ($request->filled('jenis')) {
        $query->where('jenis', $request->jenis);
    }

    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    if ($request->filled('sub_kategori')) {
        $query->where('sub_kategori', $request->sub_kategori);
    }

    $products = $query
        ->latest()
        ->paginate($perPage)
        ->withQueryString();

    // ================== DATA UNTUK FILTER ==================
    $jenisList = Product::select('jenis')
        ->whereNotNull('jenis')
        ->where('jenis', '<>', '')
        ->distinct()
        ->orderBy('jenis')
        ->pluck('jenis');

    $kategoriList = Product::select('kategori')
        ->whereNotNull('kategori')
        ->where('kategori', '<>', '')
        ->distinct()
        ->orderBy('kategori')
        ->pluck('kategori');

    // ✅ TAMBAHKAN INI
    $subKategoriList = Product::select('sub_kategori')
        ->whereNotNull('sub_kategori')
        ->where('sub_kategori', '<>', '')
        ->distinct()
        ->orderBy('sub_kategori')
        ->pluck('sub_kategori');

    return view('products.index', compact(
        'products',
        'perPage',
        'jenisList',
        'kategoriList',
        'subKategoriList'     // ← ini yang penting
    ));
}

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function edit(Product $product)
    {
        // ✅ FIX kirim categories ke edit
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
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
            'kode'          => 'nullable|string',
            'sub_kategori'   => 'nullable|string',

            // ❌ HAPUS 'kategori'
            // 'kategori' => 'nullable|string',

            // ✅ WAJIB
            'kategori_id'   => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        unset($data['label']);

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
            'kode'          => 'nullable|string',
            'sub_kategori'   => 'nullable|string',

            // ❌ HAPUS
            // 'kategori' => 'nullable|string',

            // ✅ TAMBAHKAN INI
            'kategori_id'   => 'nullable|exists:categories,id',
        ]);

        $data = $request->all();
        unset($data['label']);

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

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    // Tambahkan ini
    ini_set('max_execution_time', 300);   // 5 menit
    ini_set('memory_limit', '512M');

    Excel::import(new ProductsImport, $request->file('file'));

    return redirect()->back()->with('success', 'Import berhasil');
}

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}