<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kalender\Models\KalenderBacaanPeriode;
use Illuminate\Http\Request;

class KalenderPasifBacaanController extends Controller
{


// =========================================================
// PASIF - BACAAN UNIT (database terpisah)
// =========================================================
public function pasifBacaan()
{
    $periodes = KalenderBacaanPeriode::withCount('pesanan')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('kalender::pasif.bacaan', compact('periodes'));
}

public function pasifBacaanShow($id)
{
    $periode     = KalenderBacaanPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan = $periode->pesanan->sum('bacaan_unit');

    return view('kalender::pasif.bacaan-show', compact('periode', 'totalBacaan'));
}

public function pasifBacaanUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = KalenderBacaanPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

public function pasifBacaanDestroy($id)
{
    KalenderBacaanPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('kalender.pasif.bacaan')
        ->with('success', 'Data Bacaan Unit berhasil dihapus');
}
}
