<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\BacaanPeriode;
use Illuminate\Http\Request;

class PasifBacaanController extends Controller
{


// =========================================================
// PASIF - BACAAN UNIT (database terpisah)
// =========================================================
public function pasifBacaan()
{
    $periodes = BacaanPeriode::withCount('pesanan')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('import::import.pasif.bacaan', compact('periodes'));
}

public function pasifBacaanShow($id)
{
    $periode     = BacaanPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan = $periode->pesanan->sum('bacaan_unit');

    return view('import::import.pasif.bacaan-show', compact('periode', 'totalBacaan'));
}

public function pasifBacaanUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = BacaanPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

public function pasifBacaanDestroy($id)
{
    BacaanPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('import.pasif.bacaan')
        ->with('success', '✅ Data Bacaan Unit berhasil dihapus');
}
}
