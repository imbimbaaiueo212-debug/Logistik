<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\ManualPicking;          // ← pastikan model ini ada
use App\Models\DistributionOrder;
use App\Models\QcOutgoing;
use App\Models\Packing;
use Illuminate\Support\Facades\Auth;

class QcOutgoingController extends Controller
{
    public function index()
    {
        return view('qc-outgoing.index');
    }

    public function jakartaAktif()
    {
        $data = QcOutgoing::with(['picking'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('qc-outgoing.jakarta-aktif', compact('data'));
    }

    // ===== METHOD BARU: Order Manual =====
    public function orderManual(Request $request)
{
    $query = \App\Models\ManualQcOutgoing::with(['manualPicking.pickingItems']);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', $search)
              ->orWhereHas('manualPicking', function ($q2) use ($search) {
                  $q2->where('id_pesan', $search);
              });
        });
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', $request->nama_unit);
    }

    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $data = $query->orderByDesc('created_at')
                  ->paginate(20)
                  ->appends($request->query());

    // Select2
    $noPlList = \App\Models\ManualQcOutgoing::whereNotNull('no_pl')
        ->where('no_pl', '!=', '')
        ->distinct()
        ->orderBy('no_pl')
        ->pluck('no_pl');

    $namaUnitList = \App\Models\ManualQcOutgoing::whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $grupList = \App\Models\ManualQcOutgoing::whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    return view('qc-outgoing.order-manual', compact(
        'data',
        'noPlList',
        'namaUnitList',
        'grupList'
    ));
}

    public function qcStore(Request $request)
    {
        $validated = $request->validate([
            'picking_id' => 'required|exists:pickings,id',
            'status_qc'  => 'required|in:Pending,Lolos,Reject,Revisi',
            'pic_qc'     => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $qc = QcOutgoing::where('picking_id', $validated['picking_id'])
            ->firstOrFail();

        if ($qc->status_qc === 'Lolos') {
            return back()->with('warning', 'Data QC yang sudah Lolos tidak dapat diubah.');
        }

        $update = [
            'status_qc'  => $validated['status_qc'],
            'keterangan' => $validated['keterangan'],
            'tgl_qc'     => now(),
        ];

        if (empty($qc->pic_qc) && !empty($validated['pic_qc'])) {
            $kodePic = explode(' - ', $validated['pic_qc'])[0] ?? '';
            $update['pic_qc']  = $validated['pic_qc'];
            $update['kode_qc'] = $kodePic;
        }

        $qc->update($update);

        if ($validated['status_qc'] === 'Lolos') {
            Packing::updateOrCreate(
                ['picking_id' => $validated['picking_id']],
                [
                    'qc_outgoing_id'   => $qc->id,
                    'no_pl'            => $qc->no_pl ?? null,
                    'tgl_turun_pl'     => $qc->tgl_turun_pl ?? null,
                    'nama_unit'        => $qc->nama_unit ?? null,
                    'pengiriman'       => $qc->pengiriman ?? null,
                    'nama_barang'      => $qc->nama_barang ?? null,
                    'tgl_bayar'        => $qc->tgl_bayar ?? null,
                    'jumlah_bayar'     => $qc->jumlah_bayar ?? null,
                    'tgl_estimasi'     => $qc->tgl_estimasi ?? null,
                    'berat'            => $qc->picking?->berat,
                    'status_packing'   => 'Pending',
                    'packing_by'       => Auth::id(),
                    'packing_at'       => now(),
                ]
            );
        }

        return back()->with('success', 'QC berhasil disimpan.');
    }
}