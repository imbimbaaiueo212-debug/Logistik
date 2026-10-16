<?php

namespace App\Modules\Packing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Packing\Models\Packing;
use App\Modules\Packing\Models\PackingPasif;
use App\Modules\Distribusi\Models\DistributionPasif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingPasifController extends Controller
{


public function jakartaPasif(Request $request)
{
    $query = PackingPasif::with(['pickingPasif', 'qcOutgoingPasif'])
        ->orderBy('created_at', 'desc');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', 'like', "%{$search}%")
              ->orWhere('nama_unit', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status_packing')) {
        $query->where('status_packing', $request->status_packing);
    }

    $data = $query->paginate(20)->appends($request->query());

    // Optional: force load accessor (untuk debugging)
    // $data->getCollection()->transform(function ($item) {
    //     $item->berat_bimbashop; // trigger accessor
    //     return $item;
    // });

    return view('packing::packing.jakarta-pasif', compact('data'));
}

public function updatePasif(Request $request, $id)
{
    $packing = PackingPasif::with(['pickingPasif', 'qcOutgoingPasif'])->findOrFail($id);

    if (strtolower($packing->status_packing ?? '') === 'selesai') {
        return back()->with('error', 'Data packing sudah selesai dan tidak dapat diedit lagi.');
    }

    $validated = $request->validate([
        'tgl_packing'        => 'nullable|date',
        'status_packing'     => 'required|in:Pending,Proses,Selesai',
        'nama_packer'        => 'nullable|string|max:100',
        'berat_aktual'       => 'nullable|numeric|min:0',
        'koli'               => 'nullable|string|max:50',
        'keterangan_packing' => 'nullable|string|max:255',
    ]);

    $data = [
        'tgl_packing'        => $validated['tgl_packing'] ?? null,
        'status_packing'     => $validated['status_packing'],
        'nama_packer'        => $validated['nama_packer'] ?? null,
        'pic_packing'        => $validated['nama_packer'] ?? $packing->pic_packing,
        'berat_aktual'       => $validated['berat_aktual'] ?? null,
        'koli'               => $validated['koli'] ?? null,
        'keterangan_packing' => $validated['keterangan_packing'] ?? null,
        'keterangan'         => $validated['keterangan_packing'] ?? $packing->keterangan,
        'packing_by'         => Auth::id(),
        'packing_at'         => now(),
    ];

    // Generate kode_packing saat status Selesai (hanya sekali)
    if ($validated['status_packing'] === 'Selesai' && empty($packing->kode_packing)) {
        $data['kode_packing'] = 'PK-P-' . now()->format('YmdHis') . '-' . $packing->id;
    }

    // =====================================================
    // AMBIL BERAT DARI BERBAGAI SUMBER (selalu dijalankan)
    // =====================================================
    $picking = $packing->pickingPasif;

    $berat = $packing->berat
        ?? $picking?->berat
        ?? $picking?->total_berat
        ?? null;

    // Kalau masih kosong, ambil dari biMBA Shop
    if (empty($berat)) {
        $orderIds = array_filter([
            $packing->no_pl,
            $picking?->id_pesan,
            $picking?->no_pl,
        ]);

        if (!empty($orderIds)) {
            $bimbashop = \App\Modules\Import\Models\BimbashopOrder::whereIn('order_id', $orderIds)->first();

            if ($bimbashop) {
                // Kolom yang benar: order_weight
                $berat = $bimbashop->order_weight
                      ?? $bimbashop->berat
                      ?? $bimbashop->weight
                      ?? null;

                // Jika masih string seperti "19 gr", bersihkan
                if (is_string($berat)) {
                    $berat = (float) preg_replace('/[^0-9.]/', '', $berat);
                }
            }
        }
    }

    // Simpan berat ke packing jika berhasil didapat
    if (!empty($berat) && empty($packing->berat)) {
        $data['berat'] = $berat;
    }

    // Update packing
    $packing->update($data);
    $packing->refresh();

    // =====================================================
    // JIKA SELESAI → MASUK DISTRIBUSI PASIF
    // =====================================================
    if ($validated['status_packing'] === 'Selesai') {

        // Pastikan berat terbaru
        $berat = $packing->berat ?? $berat;

        DistributionPasif::updateOrCreate(
            ['packing_pasif_id' => $packing->id],
            [
                'picking_pasif_id'     => $packing->picking_pasif_id,
                'qc_outgoing_pasif_id' => $packing->qc_outgoing_pasif_id,
                'no_pl'                => $packing->no_pl,
                'tgl_turun_pl'         => $packing->tgl_turun_pl,
                'nama_unit'            => $packing->nama_unit,
                'nama_barang'          => $packing->nama_barang,
                'tgl_bayar'            => $packing->tgl_bayar,
                'jumlah_bayar'         => $packing->jumlah_bayar,
                'tgl_estimasi'         => $packing->tgl_estimasi,
                'pengiriman'           => $packing->pengiriman,
                'ekspedisi'            => $picking?->ekspedisi,
                'service'              => $picking?->service_pengiriman,
                'jenis_pengiriman'     => ($picking?->kirim === 'Diambil') ? 'diambil_sendiri' : 'ekspedisi',
                'berat'                => $berat,
                'berat_aktual'         => $packing->berat_aktual,
                'koli'                 => $packing->koli,
                'status_distribusi'    => 'Pending',
                'status_pengiriman'    => 'belum_pickup',
                'distribution_at'      => now(),
                'created_by'           => Auth::id(),
                'updated_by'           => Auth::id(),
            ]
        );
    }

    return back()->with('success', 'Data Packing Pasif berhasil disimpan.');
}
}
