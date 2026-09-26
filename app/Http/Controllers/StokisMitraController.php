<?php

namespace App\Http\Controllers;

use App\Imports\StokisMitraImport;
use App\Models\StokisMitra;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class StokisMitraController extends Controller
{
    public function index(Request $request)
{
    $search = $request->get('search');
    $perPage = $request->get('per_page', 50);   // default 50

    $query = StokisMitra::query()
        ->where('status', 'AKTIF')          // <-- baris baru
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_cab', 'like', "%{$search}%")
                  ->orWhere('nama_stokis_db_kemitraan', 'like', "%{$search}%")
                  ->orWhere('nama_stokis_db_bimbashop', 'like', "%{$search}%")
                  ->orWhere('nama_mitra', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('ops_stokist', 'like', "%{$search}%");
            });
        });

    // Hitung total per kategori Ops Stokist (mengikuti filter pencarian yang sama, sebelum pagination)
    $totalOps = (clone $query)
        ->selectRaw('LOWER(TRIM(ops_stokist)) as kategori, COUNT(*) as jumlah')
        ->groupBy('kategori')
        ->pluck('jumlah', 'kategori');

    $totalActive = $totalOps->get('active', 0);
    $totalClosed = $totalOps->get('closed', 0);
    $totalVacuum = $totalOps->get('vacuum', 0);

    $stokis = $query->orderBy('no_cab')
        ->paginate($perPage)
        ->appends(['search' => $search, 'per_page' => $perPage]);

    return view('stokis.index', compact(
        'stokis', 'search', 'perPage', 'totalActive', 'totalClosed', 'totalVacuum'
    ));
}

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    Excel::import(new StokisMitraImport, $request->file('file'));
    return redirect()->route('stokis.index')
                ->with('success', '✅ Data berhasil diimport!');
}

    public function edit($id)
{
    $stokis = StokisMitra::findOrFail($id);

    return view('stokis.edit', compact('stokis'));
}

    public function update(Request $request, $id)
{
    $stokis = StokisMitra::findOrFail($id);

    $validated = $request->validate([
        'no_cab' => 'required|string|max:20|unique:stokis_mitra,no_cab,' . $stokis->id,
        'nama_stokis_db_kemitraan' => 'nullable|string|max:255',
        'nama_stokis_db_bimbashop' => 'nullable|string|max:255',
        'no_induk_mitra' => 'nullable|string|max:255',
        'nama_mitra' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'no_hp' => 'nullable|string|max:30',
        'related_form_pembukaan_unit_aktif' => 'nullable|string',
        'related_formulir_kerjasama_english' => 'nullable|string',
        'db_kemitraan_db_bimbashop' => 'nullable|string|max:255',
        'related_unit_bimba_aiueo' => 'nullable|string',
        'related_formulir_kerjasama_mk_mm' => 'nullable|string',
        'related_pengajuan_perubahan' => 'nullable|string',
        'item_sku' => 'nullable|string',
        'ops_stokist' => 'nullable|string|max:255',
        'status' => 'required|in:AKTIF,PASIF',
    ]);

    $stokis->update($validated);

    return redirect()->route('stokis.index')
                ->with('success', '✅ Data stokis berhasil diperbarui!');
}

    public function destroy($id)
{
    $stokis = StokisMitra::findOrFail($id);
    $stokis->delete();

    return redirect()->route('stokis.index')
                ->with('success', '✅ Data stokis berhasil dihapus!');
}
public function pasifkan($id)
{
    $stokis = StokisMitra::where('status', 'AKTIF')->findOrFail($id);

    // Sengaja tidak pakai update([...]) supaya aman walau $fillable model belum memuat kolom baru
    $stokis->status = 'PASIF';
    $stokis->tanggal_pasif = now();
    $stokis->save();

    return redirect()->route('stokis.index')
        ->with('success', 'Stokis ' . $stokis->no_cab . ' dipindahkan ke Stokis Pasif.');
}
}