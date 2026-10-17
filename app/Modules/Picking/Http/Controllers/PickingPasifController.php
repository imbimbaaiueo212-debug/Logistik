<?php

namespace App\Modules\Picking\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Picking\Models\Picking;
use App\Modules\Picking\Models\PickingPasif;
use Illuminate\Support\Facades\Auth;   // ← TAMBAHKAN INI
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PickingPasifController extends Controller
{


/**
 * List Picking Jakarta Pasif
 */
public function jakartaPasif(Request $request)
{
    $query = PickingPasif::with(['items', 'realisasiPasif']);

    // Jakarta Pasif dibagi 3 jenis seperti di Penjualan: JKTP (Jakarta Pasif), DLC, EBT.
    // Data lama tanpa jenis dianggap JKTP.
    $jenis = strtoupper((string) $request->get('jenis', 'JKTP'));
    if (! in_array($jenis, ['JKTP', 'DLC', 'EBT', 'INV', 'BSS'], true)) {
        $jenis = 'JKTP';
    }

    // Persiapan hanya menampilkan picking yang Realisasi (RA)-nya sudah di-print.
    $query->whereHas('realisasiPasif', function ($r) use ($jenis) {
        $r->whereNotNull('printed_at')
          ->where(function ($q) use ($jenis) {
              $q->where('jenis', $jenis);

              if ($jenis === 'JKTP') {
                  $q->orWhereNull('jenis');
              }
          });
    });

    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', 'like', "%{$search}%")
              ->orWhere('id_pesan', 'like', "%{$search}%")
              ->orWhere('nama_unit', 'like', "%{$search}%");
        });
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_order', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_order', '<=', $request->end_date);
    }

    $data = $query->orderBy('created_at', 'desc')
                  ->paginate(20)
                  ->appends($request->query());

    // Kolom tambahan Persiapan Pasif diambil dari RA Pasif di Penjualan
    $data->getCollection()->each(function ($row) {
        $ra = $row->realisasiPasif;
        $row->setAttribute('nama_stokis', $ra?->nama_stokis);
        $row->setAttribute('penyebut',    $ra?->penyebut);
        $row->setAttribute('pengambil',   $ra?->pengambil);
        $row->setAttribute('ket',         $ra?->ket);
    });

    return view('picking::picking.persiapan', [
        'data'            => $data,
        'judul'           => 'Picking List - ' . ['JKTP' => 'Jakarta Pasif', 'DLC' => 'Jakarta Pasif - DLC', 'EBT' => 'Jakarta Pasif - EBT', 'INV' => 'Jakarta Pasif - INV', 'BSS' => 'Jakarta Pasif - BSS'][$jenis],
        'sub'             => 'Daftar Persiapan ' . $jenis . ' (masuk setelah RA Pasif ' . $jenis . ' di-print)',
        'jenisAktif'      => $jenis,
        'jenisTabs'       => [
            'JKTP' => ['label' => 'Jakarta Pasif', 'aktif' => 'bg-blue-600 text-white border-blue-600'],
            'DLC'  => ['label' => 'DLC',           'aktif' => 'bg-amber-500 text-white border-amber-500'],
            'EBT'  => ['label' => 'EBT',           'aktif' => 'bg-emerald-600 text-white border-emerald-600'],
            'INV'  => ['label' => 'INV',           'aktif' => 'bg-violet-600 text-white border-violet-600'],
            'BSS'  => ['label' => 'BSS',           'aktif' => 'bg-rose-600 text-white border-rose-600'],
        ],
        'routeIndex'      => 'picking.jakarta.pasif',
        'routeChecklist'  => 'picking.pasif.checklist',
        'routePic'        => 'picking.pasif.pic',
        'routeStatus'     => 'picking.pasif.status',
        'relItems'        => 'items',
        'extraCols'       => true,
        'showEdit'        => false,
        'emptyText'       => "Belum ada Persiapan {$jenis}. Data masuk setelah RA Pasif {$jenis} di-print.",
    ]);
}

// ==================== JAKARTA PASIF - UPDATE ====================

public function updateChecklistPasif(Request $request)
{
    try {
        $picking = PickingPasif::findOrFail($request->id);
        $checked = $request->boolean('checked');

        if ($checked) {
            $picking->tgl_terima  = now();
            $picking->tgl_picking = now()->toDateString();
        } else {
            $picking->tgl_terima       = null;
            $picking->tgl_picking      = null;
            $picking->status_persiapan = 'Belum';
        }

        $picking->save();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function updatePicPasif(Request $request)
{
    $request->validate([
        'id'  => 'required|exists:picking_pasif,id',   // ← diperbaiki
        'pic' => 'nullable|in:Asep,Arif,Rama,Riky',
    ]);

    $picking = PickingPasif::findOrFail($request->id);
    $picking->pic = $request->pic;
    $picking->save();

    return response()->json(['success' => true]);
}

public function updateStatusPasif(Request $request)
{
    DB::beginTransaction();

    try {
        $request->validate([
            'id'               => 'required|exists:picking_pasif,id',   // ← diperbaiki
            'status_persiapan' => 'required|in:Belum,Sudah',
        ]);

        $picking = PickingPasif::with(['items', 'realisasiPasif'])
            ->findOrFail($request->id);

        $picking->status_persiapan = $request->status_persiapan;

        if (!$picking->tgl_picking) {
            $picking->tgl_picking = now()->toDateString();
        }

        $picking->save();

        // ===== SAAT STATUS = SUDAH → BUAT / UPDATE QC OUTGOING PASIF =====
    if ($request->status_persiapan === 'Sudah') {

        $rp = $picking->realisasiPasif;

        \App\Modules\Qc\Models\QcOutgoingPasif::updateOrCreate(
            [
                'picking_pasif_id' => $picking->id,
            ],
            [
                'picking_pasif_id' => $picking->id,
                'no_pl'            => $picking->no_pl,
                'tgl_turun_pl'     => $picking->tgl_picking,
                'nama_unit'        => $picking->nama_unit,
                'pengiriman'       => $picking->ekspedisi ?? null,
                'nama_barang'      => $picking->pesanan ?? $picking->kategori_order ?? null,

                'tgl_bayar'        => $picking->payment_date ?? $rp?->payment_date,
                'jumlah_bayar'     => $rp?->harga ?? $picking->total ?? 0,
                'tgl_estimasi'     => $picking->waktu_estimasi_persiapan ?? $rp?->estimasi_persiapan,
                'nama_stokis'      => $rp?->nama_stokis ?? '-',
                'estimasi_hari'    => $rp?->estimasi_hari,

                'kode_qc'          => null,
                'tgl_qc'           => now(),
                'status_qc'        => 'Pending',
                'pic_qc'           => null,
                'keterangan'       => 'Dari Jakarta Pasif',
                'created_by'       => Auth::id(),
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
