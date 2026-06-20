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
    public function index()
    {
        $unitKemitraans = UnitKemitraan::latest()->paginate(20);
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

public function importStore(Request $request)
{
    $request->validate([
        'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
    ]);

    try {
        Excel::import(new UnitKemitraanImport, $request->file('import_file'));

        return redirect()->route('unit-kemitraan.index')
                         ->with('success', '✅ Data Unit Kemitraan berhasil diimport!');
    } catch (\Exception $e) {
        Log::error('Import Error: ' . $e->getMessage());
        return redirect()->back()
                         ->with('error', '❌ Gagal import: ' . $e->getMessage());
    }
}
}