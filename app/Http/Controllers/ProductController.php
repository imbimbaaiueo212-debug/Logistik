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

    if (!in_array($perPage, [10, 20, 50, 100, 250, 500, 1000, 5000])) {
        $perPage = 20;
    }

    $jenis       = $request->input('jenis');
    $kategori    = $request->input('kategori');
    $subKategori = $request->input('sub_kategori');


    /*
    |--------------------------------------------------------------------------
    | QUERY UTAMA PRODUK
    |--------------------------------------------------------------------------
    |
    | Semua filter mengikuti nilai master produk secara langsung.
    |
    */

    $query = Product::query();

    if (!empty($jenis)) {
        $query->where('jenis', $jenis);
    }

    if (!empty($kategori)) {
        $query->where('kategori', $kategori);
    }

    if (!empty($subKategori)) {
        $query->where('sub_kategori', $subKategori);
    }

    $products = $query
        ->orderBy('id', 'desc')
        ->paginate($perPage)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | FILTER JENIS
    |--------------------------------------------------------------------------
    |
    | Jenis mengikuti Kategori + Sub Kategori
    |
    | Contoh:
    |
    | Kategori = Modul
    |
    | Maka Jenis hanya menampilkan:
    |
    | Modul biMBA Unit Reguler
    | Modul biMBA Unit English
    | Modul biMBA Unit English Online
    | Modul biMBA Unit InterVio
    |
    */

    $jenisQuery = Product::query();

    if (!empty($kategori)) {
        $jenisQuery->where(
            'kategori',
            $kategori
        );
    }

    if (!empty($subKategori)) {
        $jenisQuery->where(
            'sub_kategori',
            $subKategori
        );
    }

    $jenisList = $jenisQuery
        ->whereNotNull('jenis')
        ->where('jenis', '!=', '')
        ->distinct()
        ->orderBy('jenis')
        ->pluck('jenis');


    /*
    |--------------------------------------------------------------------------
    | FILTER KATEGORI
    |--------------------------------------------------------------------------
    |
    | Kategori mengikuti Jenis + Sub Kategori
    |
    | Contoh:
    |
    | Jenis = Modul biMBA Unit Reguler
    |
    | Maka kategori hanya milik jenis tersebut.
    |
    */

    $kategoriQuery = Product::query();

    if (!empty($jenis)) {
        $kategoriQuery->where(
            'jenis',
            $jenis
        );
    }

    if (!empty($subKategori)) {
        $kategoriQuery->where(
            'sub_kategori',
            $subKategori
        );
    }

    $kategoriList = $kategoriQuery
        ->whereNotNull('kategori')
        ->where('kategori', '!=', '')
        ->distinct()
        ->orderBy('kategori')
        ->pluck('kategori');


    /*
    |--------------------------------------------------------------------------
    | FILTER SUB KATEGORI
    |--------------------------------------------------------------------------
    |
    | Sub Kategori mengikuti:
    |
    | Jenis + Kategori
    |
    */

    $subKategoriQuery = Product::query();

    if (!empty($jenis)) {
        $subKategoriQuery->where(
            'jenis',
            $jenis
        );
    }

    if (!empty($kategori)) {
        $subKategoriQuery->where(
            'kategori',
            $kategori
        );
    }

    $subKategoriList = $subKategoriQuery
        ->whereNotNull('sub_kategori')
        ->where('sub_kategori', '!=', '')
        ->distinct()
        ->orderBy('sub_kategori')
        ->pluck('sub_kategori');


    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'products.index',
        compact(
            'products',
            'jenisList',
            'kategoriList',
            'subKategoriList',
            'perPage'
        )
    );
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
    // Method baru untuk mengambil data lama
public function getBulkEditData(Request $request)
{
    $ids = $request->input('selected_products', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    $products = Product::whereIn('id', $ids)->get();

    $fields = ['kode', 'kategori', 'sub_kategori', 'name', 'jenis', 'satuan', 
               'harga_beli', 'harga_jual', 'role', 'status', 'tanggal_rilis'];

    $result = [];

    foreach ($fields as $field) {
        $values = $products->pluck($field)->unique()->values();
        $result[$field] = $values->count() === 1 ? $values->first() : null;
    }

    return response()->json($result);
}

// Bulk Update (sudah diperbaiki)
public function bulkUpdate(Request $request)
{
    $request->validate([
        'selected_products' => 'required|array|exists:products,id',
    ]);

    $updateData = [];

    $fillable = ['kode', 'kategori', 'sub_kategori', 'name', 'jenis', 'satuan',
                 'harga_beli', 'harga_jual', 'role', 'status', 'tanggal_rilis'];

    foreach ($fillable as $field) {
        if ($request->filled($field)) {
            $updateData[$field] = $request->$field;
        }
    }

    if (empty($updateData)) {
        return redirect()->back()->with('error', 'Tidak ada data yang diubah.');
    }

    Product::whereIn('id', $request->selected_products)->update($updateData);

    return redirect()->route('products.index')
                     ->with('success', count($request->selected_products) . ' produk berhasil diperbarui.');
}
}