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

    // ==========================
    // Filter No Cabang
    // ==========================
    if ($request->filled('no_cab')) {
        $query->where('no_cab', 'like', '%' . trim($request->no_cab) . '%');
    }

    // ==========================
    // Filter Nama Mitra
    // ==========================
    if ($request->filled('nama_mitra')) {
        $query->where('nama_mitra', 'like', '%' . trim($request->nama_mitra) . '%');
    }

    // ==========================
    // Filter Status
    // ==========================
    if ($request->filled('status') && $request->status != 'all') {
        $query->where('status', $request->status);
    }

    // ==========================
    // Filter Status Pengelolaan
    // ==========================
    if ($request->filled('status_pengelolaan')
        && $request->status_pengelolaan != 'all') {

        $query->where('status_pengelolaan', $request->status_pengelolaan);
    }

    // ==========================
    // Filter Mitra Pengelolaan
    // ==========================
    if ($request->filled('mitra_pengelolaan')
        && $request->mitra_pengelolaan != 'all') {

        $query->where('mitra_pengelolaan', $request->mitra_pengelolaan);
    }

    // ==========================
    // Filter Provinsi
    // ==========================
    if ($request->filled('provinsi')) {
        $query->where('provinsi', 'like', '%' . trim($request->provinsi) . '%');
    }

    // ==========================
    // Filter Kabupaten / Kota
    // ==========================
    if ($request->filled('kab_kota')) {
        $query->where('kab_kota', 'like', '%' . trim($request->kab_kota) . '%');
    }

    // ==========================
    // Filter Awal Kontrak
    // ==========================
    if ($request->filled('awal_kontrak_start')) {
        $query->whereDate('awal_kontrak', '>=', $request->awal_kontrak_start);
    }

    if ($request->filled('awal_kontrak_end')) {
        $query->whereDate('awal_kontrak', '<=', $request->awal_kontrak_end);
    }

    // ==========================
    // Search
    // ==========================
    if ($request->filled('search')) {

        $search = trim($request->search);

        $query->where(function ($q) use ($search) {
            $q->where('no_cab', 'like', "%{$search}%")
              ->orWhere('nama_mitra', 'like', "%{$search}%")
              ->orWhere('bimba_aiueo_unit', 'like', "%{$search}%")
              ->orWhere('alamat_unit', 'like', "%{$search}%")
              ->orWhere('provinsi', 'like', "%{$search}%")
              ->orWhere('kab_kota', 'like', "%{$search}%");
        });
    }

    $unitKemitraans = $query
        ->orderBy('id_record', 'desc')
        ->paginate(20)
        ->appends($request->query());

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
        'no_cab'            => 'nullable|string|max:100',
        'bimba_aiueo_unit'  => 'nullable|string|max:150',
        'status'            => 'nullable|string|max:100',
        'no_telp_unit'      => 'nullable|string|max:50',
        'email_unit'        => 'nullable|email|max:150',
        'alamat_unit'       => 'nullable|string',

        'nama_mitra'        => 'nullable|string|max:150',
        'email'             => 'nullable|email|max:150',
        'no_hp'             => 'nullable|string|max:50',
        'no_rekening'       => 'nullable|string|max:100',

        'nilai_lisensi'     => 'nullable|numeric',
        'persen_mitra'      => 'nullable|numeric',
        'persen_ypai'       => 'nullable|numeric',

        'awal'              => 'nullable|date',
        'akhir'             => 'nullable|date',
    ]);

    // Isi otomatis Status Pengelolaan & Mitra Pengelolaan
    $validated['status_pengelolaan'] = $this->getStatusPengelolaan($validated['status'] ?? null);
    $validated['mitra_pengelolaan']  = $this->getMitraPengelolaan($validated['status'] ?? null);

    UnitKemitraan::create($validated);

    return redirect()
        ->route('unit-kemitraan.index')
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
        'no_cab'           => 'nullable|string|max:100',
        'bimba_aiueo_unit' => 'nullable|string|max:150',
        'status'           => 'nullable|string|max:100',
        'no_telp_unit'     => 'nullable|string|max:50',
        'email_unit'       => 'nullable|email|max:150',
        'alamat_unit'      => 'nullable|string',

        'nama_mitra'       => 'nullable|string|max:150',
        'email'            => 'nullable|email|max:150',
        'no_hp'            => 'nullable|string|max:50',

        // tambahkan validasi lain jika diperlukan
    ]);

    // Isi otomatis Status Pengelolaan & Mitra Pengelolaan
    $validated['status_pengelolaan'] = $this->getStatusPengelolaan($validated['status'] ?? null);
    $validated['mitra_pengelolaan']  = $this->getMitraPengelolaan($validated['status'] ?? null);

    $unitKemitraan->update($validated);

    return redirect()
        ->route('unit-kemitraan.index')
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
        'import_file' => 'required|mimes:xlsx,xls,csv|max:51200',
    ]);

    try {
        set_time_limit(900);
        ini_set('memory_limit', '1024M');

        $file = $request->file('import_file');

        Log::info('========== MULAI IMPORT UnitKemitraan ==========', [
            'filename' => $file->getClientOriginalName(),
            'size_mb'  => round($file->getSize() / (1024 * 1024), 2),
            'mime'     => $file->getMimeType(),
            'user'     => auth()->user()->name ?? 'guest',
        ]);

        $import = new \App\Imports\UnitKemitraanImport();

        // Jalankan import
        Excel::import($import, $file);

        $summary = $import->getSummary();

        Log::info('========== HASIL IMPORT SELESAI ==========', $summary);

        // Buat pesan yang jelas
        $message = sprintf(
            'Import selesai. Berhasil: %d | Gagal: %d | Dilewati: %d',
            $summary['success'],
            $summary['failed'],
            $summary['skipped']
        );

        if ($summary['failed'] > 0) {
            return redirect()
                ->route('unit-kemitraan.index')
                ->with('warning', $message)
                ->with('import_errors', $summary['failed_rows']);
        }

        if ($summary['success'] === 0 && $summary['skipped'] === 0) {
            return redirect()
                ->route('unit-kemitraan.index')
                ->with('error', 'Tidak ada data yang berhasil diimport. Cek format file atau header Excel.');
        }

        return redirect()
            ->route('unit-kemitraan.index')
            ->with('success', $message);

    } catch (\Throwable $e) {
        Log::error('Import UnitKemitraan FATAL', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return redirect()
            ->back()
            ->with('error', '❌ Gagal total: ' . $e->getMessage());
    }
}

private function getStatusPengelolaan(?string $status): ?string
{
    if (empty($status)) {
        return null;
    }

    $status = strtoupper(trim($status));

    // Daftar status yang dianggap AKTIF
    $statusAktif = [
        'MM', 'MM 1', 'AKTIF 1', 'MK', 'MK 1', 
        'MK RINDA', 'MKU', 'MKU 1', 'UNIT AKTIF', 
        'E-BIMBA AKTIF'
    ];

    if (in_array($status, $statusAktif)) {
        return 'Unit Aktif';
    }

    // === KONDISI KHUSUS BARU ===

    // Aktif Kab/Kota & Aktif Kecamatan → NULL / -
    if (str_contains($status, 'AKTIF KAB') || 
        str_contains($status, 'AKTIF KEC')) {
        return null;
    }

    // Pasif Kab/Kota & Pasif Kecamatan → NULL / -
    if (str_contains($status, 'PASIF KAB') || 
        str_contains($status, 'PASIF KEC')) {
        return null;
    }

    // Stockist (semua varian) → NULL / -
    if (str_contains($status, 'STOCKIST')) {
        return null;
    }

    // E-biMBA Pasif → Pasif
    if (str_contains($status, 'E-BIMBA PASIF')) {
        return 'Unit Pasif';
    }

    // Pasif umum
    if (str_contains($status, 'PASIF') || str_contains($status, 'Unit PASIF')) {
        return 'Unit Pasif';
    }

    return null;
}

private function getMitraPengelolaan(?string $status): ?string
{
    if (empty($status)) {
        return null;
    }

    $statusUpper = strtoupper(trim($status));

    // Jika mengandung "OPS1"
    if (str_contains($statusUpper, 'OPS1')) {
        return 'OPS1';
    }

    // Jika mengandung angka 1 (termasuk MM1, MK1, dll)
    if (preg_match('/1/', $statusUpper)) {
        return 'PUW1 | OPS1';
    }

    // Jika tidak ada angka 1
    return 'YPAI';
}

}