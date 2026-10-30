<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Kalender\Models\KalenderPasifPeriode;
use App\Modules\Kalender\Models\KalenderBacaanPeriode;
use App\Modules\Kalender\Models\KalenderSparePasif;
use App\Modules\Kalender\Models\KalenderDlcPeriode;
use App\Modules\Kalender\Models\KalenderDlcPesanan;
use App\Modules\Kalender\Models\KalenderPasifPesanan;
use App\Modules\Kalender\Models\KalenderBacaanPesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KalenderPasifController extends Controller
{

// =========================================================
// PASIF - MENU
// =========================================================
public function pasifMenu()
{
    return view('kalender::pasif.menu');
}

// =========================================================
// PASIF - UNIT PASIF (Kalender / Qty)
// =========================================================
public function pasifIndex()
{
    $periodes = KalenderPasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('kalender::pasif.index', compact('periodes'));
}

public function pasifCreate()
{
    return view('kalender::pasif.create');
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
        $file->storeAs('imports/kalender-pasif', $filename, 'public');

        DB::beginTransaction();

        // 1. Periode Unit Pasif (Qty)
        $pasifPeriode = KalenderPasifPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => 'Kalender biMBA ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => $request->no_ps,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 2. Periode Bacaan Unit (database terpisah, no_ps sendiri)
        $bacaanPeriode = KalenderBacaanPeriode::create([
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
            new \App\Modules\Kalender\Imports\KalenderPasifImport($pasifPeriode->id, $bacaanPeriode->id),
            $file
        );

        DB::commit();

        return redirect()
            ->route('kalender.pasif.show', $pasifPeriode->id)
            ->with('success', 'Data Unit Pasif & Bacaan Unit berhasil diimport! File: ' . $originalName);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Pasif Import Error: " . $e->getMessage());
        return back()->with('error', 'Gagal import: ' . $e->getMessage());
    }
}

public function pasifShow($id)
{
    $periode = KalenderPasifPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');

    return view('kalender::pasif.show', compact('periode', 'total'));
}

public function pasifDestroy($id)
{
    KalenderPasifPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('kalender.pasif.list')
        ->with('success', 'Data Unit Pasif berhasil dihapus');
}

public function pasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = KalenderPasifPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

// =========================================================
// PASIF - REKAP TOTAL
// =========================================================
public function pasifRekap()
{
    $periodes = KalenderPasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('kalender::pasif.rekap', compact('periodes'));
}

public function pasifRekapShow($id)
{
    $periode      = KalenderPasifPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan  = $periode->pesanan->sum('bacaan_unit');
    $totalKalender = $periode->pesanan->sum('qty');

    return view('kalender::pasif.rekap-show', compact('periode', 'totalBacaan', 'totalKalender'));
}



public function sparePasif()
{
    // ===== Auto generate / refresh =====
    $this->syncSparePasifData();

    $data = KalenderSparePasif::where('status', 'aktif')
        ->orderBy('edisi')
        ->get();

    return view('kalender::pasif.spare', compact('data'));
}

/**
 * Update No PS Spare Pasif (opsional)
 */
public function sparePasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $item = KalenderSparePasif::findOrFail($id);
    $item->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $item->no_ps,
    ]);
}

/**
 * Hitung & simpan Spare Pasif 3% per edisi (DLC + Unit Pasif + Bacaan Unit).
 */
private function syncSparePasifData(): void
{
    $edisiList = collect()
        ->merge(KalenderDlcPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(KalenderPasifPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(KalenderBacaanPeriode::where('status', 'aktif')->pluck('edisi'))
        ->map(fn ($e) => strtoupper(trim($e)))
        ->filter()
        ->unique()
        ->sort()
        ->values();

    foreach ($edisiList as $edisi) {
        $dlcTotal = (int) KalenderDlcPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $pasifTotal = (int) KalenderPasifPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $bacaanTotal = (int) KalenderBacaanPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('bacaan_unit');

        $grandTotal = $dlcTotal + $pasifTotal + $bacaanTotal;
        $spareRaw   = $grandTotal * 0.03;
        $spare      = (int) round($spareRaw);
        $lembar     = $spare <= 0 ? 0 : (int) ceil($spare / 200);

        KalenderSparePasif::updateOrCreate(
            ['edisi' => $edisi, 'status' => 'aktif'],
            [
                'judul'        => 'Spare Pasif 3% ' . $edisi,
                'dlc_total'    => $dlcTotal,
                'pasif_total'  => $pasifTotal,
                'bacaan_total' => $bacaanTotal,
                'grand_total'  => $grandTotal,
                'spare_raw'    => round($spareRaw, 3),
                'spare'        => $spare,
                'lembar'       => $lembar,
                'grup'         => 'A',
                'created_by'   => Auth::id(),
            ]
        );
    }
}
}
