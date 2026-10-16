<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Import\Models\PasifPeriode;
use App\Modules\Import\Models\BacaanPeriode;
use App\Modules\Import\Models\SparePasif;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Import\Http\Controllers\Concerns\ManualOrderSyncHelpers;

class PasifController extends Controller
{
    use ManualOrderSyncHelpers;


// =========================================================
// PASIF - MENU
// =========================================================
public function pasifMenu()
{
    return view('import::import.pasif.menu');
}

// =========================================================
// PASIF - UNIT PASIF (Majalah / Qty)
// =========================================================
public function pasifIndex()
{
    $periodes = PasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('import::import.pasif.index', compact('periodes'));
}

public function pasifCreate()
{
    return view('import::import.pasif.create');
}

public function pasifStore(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'edisi'       => 'required|string|max:20',
        'periode'     => 'nullable|string|max:50',
        'bulan'       => 'nullable|string|max:20',
        'tahun'       => 'nullable|string|max:10',
        'no_ps'       => 'nullable|string|max:100',
    ]);

    try {
        $file = $request->file('import_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        $file->storeAs('imports/pasif', $filename, 'public');

        DB::beginTransaction();

        // 1. Periode Unit Pasif (Qty)
        $pasifPeriode = PasifPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => 'Majalah Sahabat biMBA ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => $request->no_ps,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 2. Periode Bacaan Unit (database terpisah, no_ps sendiri)
        $bacaanPeriode = BacaanPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => 'Bacaan Unit ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => null,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 3. Import ke kedua tabel
        Excel::import(
            new \App\Modules\Import\Imports\PasifImport($pasifPeriode->id, $bacaanPeriode->id),
            $file
        );

        DB::commit();

        return redirect()
            ->route('import.pasif.show', $pasifPeriode->id)
            ->with('success', '✅ Data Unit Pasif & Bacaan Unit berhasil diimport! File: ' . $originalName);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Pasif Import Error: " . $e->getMessage());
        return back()->with('error', '❌ Gagal import: ' . $e->getMessage());
    }
}

public function pasifShow($id)
{
    $periode = PasifPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');

    return view('import::import.pasif.show', compact('periode', 'total'));
}

public function pasifDestroy($id)
{
    PasifPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('import.pasif.list')
        ->with('success', '✅ Data Unit Pasif berhasil dihapus');
}

public function pasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = PasifPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

public function syncPasifToManual()
{
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $errors  = [];
    $skippedList = [];

    $periodes = PasifPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    DB::beginTransaction();

    try {
        foreach ($periodes as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if (($item->qty ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = $item->nama_unit . ' (Pasif)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab,
                    'jumlah_pesanan'    => $item->qty,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon,
                    'mitra_pengelolaan' => null,
                    'kabupaten_kota'    => 'PASIF',
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    'PASIF',
                    null,
                    'F',
                    $periode->no_ps
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        DB::commit();

        $message = "✅ Sync Unit Pasif ke Manual selesai.<br>"
                 . "Berhasil masuk: <strong>{$created}</strong><br>"
                 . "Berhasil di-update: <strong>{$updated}</strong><br>"
                 . "Dilewati: <strong>{$skipped}</strong>";

        return redirect()
            ->route('import.manual')
            ->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Sync Pasif gagal: ' . $e->getMessage());

        return redirect()
            ->route('import.pasif.list')
            ->with('error', 'Gagal sync: ' . $e->getMessage());
    }
}

// =========================================================
// PASIF - REKAP TOTAL
// =========================================================
public function pasifRekap()
{
    $periodes = PasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('import::import.pasif.rekap', compact('periodes'));
}

public function pasifRekapShow($id)
{
    $periode      = PasifPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan  = $periode->pesanan->sum('bacaan_unit');
    $totalMajalah = $periode->pesanan->sum('qty');

    return view('import::import.pasif.rekap-show', compact('periode', 'totalBacaan', 'totalMajalah'));
}



public function sparePasif()
{
    // ===== Auto generate / refresh =====
    $this->syncSparePasifData();

    $data = SparePasif::where('status', 'aktif')
        ->orderBy('edisi')
        ->get();

    return view('import::import.pasif.spare', compact('data'));
}

/**
 * Detail per edisi
 */
public function sparePasifShow($edisi)
{
    $edisi = strtoupper(trim($edisi));

    $item = SparePasif::where('edisi', $edisi)
        ->where('status', 'aktif')
        ->firstOrFail();

    return view('import::import.pasif.spare-show', [
        'edisi'       => $item->edisi,
        'dlc'         => $item->dlc_total,
        'pasif'       => $item->pasif_total,
        'bacaan'      => $item->bacaan_total,
        'grand_total' => $item->grand_total,
        'spare_raw'   => $item->spare_raw,
        'spare'       => $item->spare,
        'lembar'      => $item->lembar,
        'grup'        => $item->grup,
        'no_ps'       => $item->no_ps,
        'item'        => $item, // kalau view butuh full model
    ]);
}

/**
 * Update No PS Spare Pasif (opsional)
 */
public function sparePasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $item = SparePasif::findOrFail($id);
    $item->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $item->no_ps,
    ]);
}
}
