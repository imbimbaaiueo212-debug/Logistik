<?php

namespace App\Modules\Picking\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Picking\Models\Picking;
use Illuminate\Support\Facades\Auth;   // ← TAMBAHKAN INI
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PickingManualController extends Controller
{


public function orderManual(Request $request)
{
    $query = \App\Modules\Picking\Models\ManualPicking::with(['manualRealisasi', 'pickingItems']);

    // Filter Kategori
    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    // Filter No PL / ID Pesan
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', $search)
              ->orWhere('id_pesan', $search);
        });
    }

    // Filter Nama Unit
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', $request->nama_unit);
    }

    // Filter Grup
    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    // Filter Tanggal
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_order', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_order', '<=', $request->end_date);
    }

    $data = $query->orderBy('created_at', 'desc')
                  ->paginate(20)
                  ->appends($request->query());

    // ===== Data untuk Select2 =====
    $noPlList = \App\Modules\Picking\Models\ManualPicking::whereNotNull('no_pl')
        ->where('no_pl', '!=', '')
        ->distinct()
        ->orderBy('no_pl')
        ->pluck('no_pl');

    $namaUnitList = \App\Modules\Picking\Models\ManualPicking::whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $grupList = \App\Modules\Picking\Models\ManualPicking::whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    return view('picking::picking.order-manual', compact('data', 'noPlList', 'namaUnitList', 'grupList'));
}

// ==================== MANUAL PICKING ====================

public function updateChecklistManual(Request $request)
{
    try {
        $picking = \App\Modules\Picking\Models\ManualPicking::findOrFail($request->id);
        $checked = $request->boolean('checked');

        if ($checked) {
            $picking->tgl_terima  = now();
            $picking->tgl_picking = now()->toDateString();
        } else {
            $picking->tgl_terima        = null;
            $picking->tgl_picking       = null;
            $picking->status_persiapan  = 'Belum';
        }

        $picking->save();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function updatePicManual(Request $request)
{
    $request->validate([
        'id'  => 'required|exists:manual_pickings,id',
        'pic' => 'nullable|in:Asep,Arif,Rama,Riky',
    ]);

    $picking = \App\Modules\Picking\Models\ManualPicking::findOrFail($request->id);
    $picking->pic = $request->pic;
    $picking->save();

    return response()->json(['success' => true]);
}

public function updateStatusManual(Request $request)
{
    DB::beginTransaction();

    try {
        $request->validate([
            'id'               => 'required|exists:manual_pickings,id',
            'status_persiapan' => 'required|in:Belum,Sudah',
        ]);

        $picking = \App\Modules\Picking\Models\ManualPicking::with('pickingItems')
            ->findOrFail($request->id);

        $picking->status_persiapan = $request->status_persiapan;

        if (!$picking->tgl_picking) {
            $picking->tgl_picking = now()->toDateString();
        }

        $picking->save();

        // ===== SAAT STATUS = SUDAH → BUAT QC MANUAL =====
        if ($request->status_persiapan === 'Sudah') {

            \App\Modules\Qc\Models\ManualQcOutgoing::updateOrCreate(
                [
                    'manual_picking_id' => $picking->id,
                ],
                [
                    'manual_picking_id' => $picking->id,
                    'no_pl'             => $picking->no_pl,
                    'no_ps'             => $picking->no_ps,
                    'nama_unit'         => $picking->nama_unit,
                    'grup'              => $picking->grup,
                    'kategori_order'    => $picking->kategori_order ?? $picking->pesanan,
                    'status_qc'         => 'Pending',
                    'tgl_qc'            => now(),
                    'created_by'        => Auth::id(),
                ]
            );
        }

        DB::commit();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error($e);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
}
