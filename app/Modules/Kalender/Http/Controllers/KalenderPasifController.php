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
use App\Modules\Kalender\Support\KodeKalender;
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

    // Spare Pasif 3% + Total Kalender (murid + relawan x2 + humas) per periode
    $periodes->getCollection()->each(function ($p) {
        $p->spare_qty = $p->spareQty();
        $p->total_kalender = (int) $p->pesanan()->sum('qty')
            + (int) $p->pesanan()->sum('bacaan_unit') * 2
            + (int) $p->pesanan()->sum('humas');
        $p->grand_total = $p->total_kalender + $p->spare_qty;
    });

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
        $edisi = KodeKalender::normalize($request->edisi);

        $file = $request->file('import_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        $file->storeAs('imports/kalender-pasif', $filename, 'public');

        DB::beginTransaction();

        // 1. Periode Unit Pasif (Qty)
        $pasifPeriode = KalenderPasifPeriode::create([
            'edisi'      => $edisi,
            'judul'      => 'Kalender biMBA ' . $edisi,
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
            'edisi'      => $edisi,
            'judul'      => 'Bacaan Unit ' . $edisi,
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
    $periode  = KalenderPasifPeriode::with('pesanan')->findOrFail($id);
    $total    = $periode->pesanan->sum('qty');
    $spareQty = $periode->spareQty();

    return view('kalender::pasif.show', compact('periode', 'total', 'spareQty'));
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

public function pasifUpdateCetak(Request $request, $id)
{
    $request->validate([
        'cetak_ke' => 'nullable|integer|min:1|max:20',
    ]);

    $periode = KalenderPasifPeriode::findOrFail($id);
    $periode->update(['cetak_ke' => $request->filled('cetak_ke') ? (int) $request->cetak_ke : null]);

    // Spare mengikuti Cetak ke, jadi dikirim balik agar tabel di halaman langsung berubah
    $spare = $periode->spareQty();
    $total = (int) $periode->pesanan()->sum('qty')
        + (int) $periode->pesanan()->sum('bacaan_unit') * 2
        + (int) $periode->pesanan()->sum('humas');

    return response()->json([
        'success'     => true,
        'message'     => 'Cetak ke berhasil diupdate',
        'cetak_ke'    => $periode->cetak_ke,
        'spare_qty'   => number_format($spare),
        'grand_total' => number_format($total + $spare),
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
        ->withSum('pesanan', 'humas')
        ->latest()
        ->paginate(20);

    // Grand total seluruh periode (bukan hanya halaman yang tampil)
    $grand = [
        'periode' => KalenderPasifPeriode::count(),
        'unit'    => (int) KalenderPasifPesanan::count(),
        'murid'   => (int) KalenderPasifPesanan::sum('qty'),
        'relawan' => (int) KalenderPasifPesanan::sum('bacaan_unit'),
        'humas'   => (int) KalenderPasifPesanan::sum('humas'),
    ];
    $grand['relawanx2'] = $grand['relawan'] * 2;
    $grand['total']     = $grand['murid'] + $grand['relawanx2'] + $grand['humas'];

    return view('kalender::pasif.rekap', compact('periodes', 'grand'));
}

public function pasifRekapShow($id)
{
    $periode      = KalenderPasifPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan  = $periode->pesanan->sum('bacaan_unit');
    $totalKalender = $periode->pesanan->sum('qty');

    return view('kalender::pasif.rekap-show', compact('periode', 'totalBacaan', 'totalKalender'));
}



public function sparePasif(Request $request)
{
    // Daftar periode Unit Pasif aktif (terbaru dulu) untuk pilihan di halaman
    $periodes = KalenderPasifPeriode::where('status', 'aktif')
        ->orderByDesc('id')
        ->get();

    $periode = $request->filled('periode')
        ? $periodes->firstWhere('id', (int) $request->periode)
        : $periodes->first();

    $rows = $periode
        ? KalenderPasifPesanan::where('pasif_periode_id', $periode->id)->orderBy('id')->get()
        : collect();

    // Ringkasan: TOTAL per unit = MURID + (RELAWAN x 2) + HUMAS
    $sum = [
        'unit'      => $rows->count(),
        'murid'     => (int) $rows->sum('qty'),
        'relawan'   => (int) $rows->sum('bacaan_unit'),
        'relawanx2' => (int) $rows->sum('bacaan_unit') * 2,
        'humas'     => (int) $rows->sum('humas'),
    ];
    $sum['total']  = $sum['murid'] + $sum['relawanx2'] + $sum['humas'];
    $sum['spare']  = (int) round($sum['total'] * 0.03);
    $sum['lembar'] = $sum['spare'] <= 0 ? 0 : (int) ceil($sum['spare'] / 200);

    return view('kalender::pasif.spare', compact('periodes', 'periode', 'rows', 'sum'));
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
        ->map(fn ($e) => KodeKalender::normalize($e))
        ->filter()
        ->unique()
        ->sort()
        ->values();

    foreach ($edisiList as $edisi) {
        $varian = KodeKalender::varian($edisi);
        $in     = implode(',', array_fill(0, count($varian), '?'));
        $cocok  = fn ($q) => $q->where('status', 'aktif')->whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian);

        $dlcTotal    = (int) KalenderDlcPesanan::whereHas('periode', $cocok)->sum('qty');
        $pasifTotal  = (int) KalenderPasifPesanan::whereHas('periode', $cocok)->sum('qty');
        $bacaanTotal = (int) KalenderBacaanPesanan::whereHas('periode', $cocok)->sum('bacaan_unit');

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
