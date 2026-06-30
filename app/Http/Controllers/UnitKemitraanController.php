<?php

namespace App\Http\Controllers;

use App\Models\UnitKemitraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Imports\UnitKemitraanImport;
use Maatwebsite\Excel\Facades\Excel;

class UnitKemitraanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = UnitKemitraan::query();

    // Filter No Cabang
    if ($request->filled('no_cab')) {
        $query->where('no_cab', 'like', '%' . $request->no_cab . '%');
    }

    // Filter Nama Mitra
    if ($request->filled('nama_mitra')) {
        $query->where('nama_mitra', 'like', '%' . $request->nama_mitra . '%');
    }

    // Filter Status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter Provinsi
    if ($request->filled('provinsi')) {
        $query->where('provinsi', 'like', '%' . $request->provinsi . '%');
    }

    // Filter Kab/Kota
    if ($request->filled('kab_kota')) {
        $query->where('kab_kota', 'like', '%' . $request->kab_kota . '%');
    }

    // Filter Tanggal Awal Kontrak
    if ($request->filled('awal_kontrak_start')) {
        $query->whereDate('awal_kontrak', '>=', $request->awal_kontrak_start);
    }
    if ($request->filled('awal_kontrak_end')) {
        $query->whereDate('awal_kontrak', '<=', $request->awal_kontrak_end);
    }

    // Search umum (bisa cari di beberapa kolom)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('no_cab', 'like', "%{$search}%")
              ->orWhere('nama_mitra', 'like', "%{$search}%")
              ->orWhere('bimba_aiueo_unit', 'like', "%{$search}%")
              ->orWhere('alamat_unit', 'like', "%{$search}%");
        });
    }

    $unitKemitraans = $query->latest()->paginate(20)->appends($request->all());

    return view('unit_kemitraan.index', compact('unitKemitraans'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unit_kemitraan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_cab' => 'nullable|string|max:100',
            'bimba_aiueo_unit' => 'nullable|string|max:150',
            'status' => 'nullable|string|max:100',
            'no_telp_unit' => 'nullable|string|max:50',
            'email_unit' => 'nullable|email|max:150',
            'alamat_unit' => 'nullable|string',
            // ... Anda bisa tambahkan validasi lain sesuai kebutuhan
            'nama_mitra' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'no_hp' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:100',
            'nilai_lisensi' => 'nullable|numeric',
            'persen_mitra' => 'nullable|numeric',
            'persen_ypai' => 'nullable|numeric',
            'awal' => 'nullable|date',
            'akhir' => 'nullable|date',
            // tambahkan validasi lain jika diperlukan
        ]);

        UnitKemitraan::create($validated);

        return redirect()->route('unit-kemitraan.index')
                         ->with('success', 'Data Unit Kemitraan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitKemitraan $unitKemitraan)
    {
        return view('unit_kemitraan.show', compact('unitKemitraan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitKemitraan $unitKemitraan)
    {
        return view('unit_kemitraan.edit', compact('unitKemitraan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitKemitraan $unitKemitraan)
    {
        $validated = $request->validate([
            'no_cab' => 'nullable|string|max:100',
            'bimba_aiueo_unit' => 'nullable|string|max:150',
            'status' => 'nullable|string|max:100',
            'no_telp_unit' => 'nullable|string|max:50',
            'email_unit' => 'nullable|email|max:150',
            'alamat_unit' => 'nullable|string',
            'nama_mitra' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'no_hp' => 'nullable|string|max:50',
            // tambahkan validasi lain sesuai kebutuhan
        ]);

        $unitKemitraan->update($validated);

        return redirect()->route('unit-kemitraan.index')
                         ->with('success', 'Data Unit Kemitraan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitKemitraan $unitKemitraan)
    {
        $unitKemitraan->delete();

        return redirect()->route('unit-kemitraan.index')
                         ->with('success', 'Data Unit Kemitraan berhasil dihapus.');
    }

    public function importForm()
{
    return view('unit_kemitraan.import');
}


   public function import(Request $request)
{
    $request->validate([
        'import_file' => 'required|mimes:xlsx,xls,csv|max:51200', // 50MB
    ]);

    try {
        set_time_limit(900); // 15 menit
        ini_set('memory_limit', '1024M');

        $file = $request->file('import_file');

        Log::info('Mulai Import Besar', [
            'filename' => $file->getClientOriginalName(),
            'size' => round($file->getSize() / (1024*1024), 2) . ' MB'
        ]);

        Excel::import(new UnitKemitraanImport, $file);

        return redirect()->route('unit-kemitraan.index')
                         ->with('success', '✅ Import 6000+ data berhasil!');

    } catch (\Exception $e) {
        Log::error('Import Error Besar', [
            'message' => $e->getMessage(),
            'line' => $e->getLine()
        ]);

        return redirect()->back()
                         ->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}
}