<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\ManualPicking;          // ← pastikan model ini ada
use App\Models\ManualPacking;
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

    public function orderManual(Request $request)
{
    $query = ManualPicking::with(['manualQcOutgoing'])
        ->where('status_persiapan', 'Sudah')   // hanya yang sudah selesai picking
        ->orderByDesc('created_at');

    // ===== Filter Kategori (Modul / Majalah / Sertifikat) =====
    if ($request->filled('kategori')) {
        $kategori = $request->kategori;

        $query->where(function ($q) use ($kategori) {
            $q->where('kategori_order', 'like', "%{$kategori}%")
              ->orWhere('pesanan', 'like', "%{$kategori}%");
        });
    }

    // Filter lain
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', $search)
              ->orWhere('id_pesan', $search);
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

    $data = $query->paginate(20)->appends($request->query());

    // Data Select2
    $baseQuery = ManualPicking::where('status_persiapan', 'Sudah');

    $noPlList = (clone $baseQuery)->whereNotNull('no_pl')
        ->where('no_pl', '!=', '')
        ->distinct()->orderBy('no_pl')->pluck('no_pl');

    $namaUnitList = (clone $baseQuery)->whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()->orderBy('nama_unit')->pluck('nama_unit');

    $grupList = (clone $baseQuery)->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()->orderBy('grup')->pluck('grup');

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
    /**
 * Simpan / Update QC Outgoing - Order Manual
 */
public function storeManual(Request $request)
{
    $validated = $request->validate([
        'manual_picking_id' => 'required|exists:manual_pickings,id',
        'status_qc'         => 'required|in:Pending,Lolos,Reject,Revisi',
        'pic_qc'            => 'nullable|string|max:100',
        'keterangan'        => 'nullable|string|max:500',
    ]);

    $picking = ManualPicking::findOrFail($validated['manual_picking_id']);

    $qc = \App\Models\ManualQcOutgoing::where('manual_picking_id', $picking->id)->first();

    if ($qc && $qc->status_qc === 'Lolos') {
        return back()->with('warning', 'Data QC yang sudah Lolos tidak dapat diubah.');
    }

    $update = [
        'manual_picking_id' => $picking->id,
        'no_pl'             => $picking->no_pl,
        'nama_unit'         => $picking->nama_unit,
        'grup'              => $picking->grup,
        'kategori_order'    => $picking->kategori_order ?? $picking->pesanan ?? null,
        'status_qc'         => $validated['status_qc'],
        'keterangan'        => $validated['keterangan'] ?? null,
        'tgl_qc'            => now(),
        'created_by'        => Auth::id(),
    ];

    // PIC QC + Kode QC hanya diisi sekali
    if ((!$qc || empty($qc->pic_qc)) && !empty($validated['pic_qc'])) {
        $kodePic = explode(' - ', $validated['pic_qc'])[0] ?? '';
        $update['pic_qc']  = $validated['pic_qc'];
        $update['kode_qc'] = $kodePic;
    }

    $qc = \App\Models\ManualQcOutgoing::updateOrCreate(
        ['manual_picking_id' => $picking->id],
        $update
    );

    // ===== PINDAH KE PACKING JIKA LOLOS =====
    if ($validated['status_qc'] === 'Lolos') {
    ManualPacking::updateOrCreate(
        ['manual_picking_id' => $picking->id],
        [
            'manual_qc_outgoing_id' => $qc->id,
            'no_pl'                 => $picking->no_pl,
            'nama_unit'             => $picking->nama_unit,
            'grup'                  => $picking->grup,
            'kategori_order'        => $picking->kategori_order ?? $picking->pesanan ?? null,
            'pic_picking'           => $picking->pic,
            'status_packing'        => 'Pending',
            'packing_by'            => Auth::id(),
            'packing_at'            => now(),
            'created_by'            => Auth::id(),
        ]
    );
}

    return redirect()
        ->route('qc-outgoing.order-manual')
        ->with('success', 'QC Manual berhasil disimpan.');
}
}