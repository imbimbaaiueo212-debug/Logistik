<?php

namespace App\Modules\Packing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Packing\Models\Packing;
use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Distribusi\Models\DistributionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingController extends Controller
{

    public function index()
    {
        return view('packing::packing.index');
    }

    public function jakartaAktif()
    {
        $data = Packing::with('picking')
            ->orderBy('tgl_estimasi')
            ->paginate(20);

        return view('packing::packing.jakarta-aktif', compact('data'));
    }

    /**
     * Update Data Packing (Jakarta Aktif)
     */
    public function update(Request $request, $id)
    {
        $packing = Packing::findOrFail($id);

        if ($packing->status_packing === 'selesai') {
            return back()->with('error', 'Data packing sudah selesai dan tidak dapat diedit lagi.');
        }

        $validated = $request->validate([
            'tgl_packing'        => 'nullable|date',
            'status_packing'     => 'required|in:belum,proses,pending,selesai',
            'nama_packer'        => 'nullable|string|max:100',
            'berat_aktual'       => 'nullable|numeric|min:0',
            'koli'               => 'nullable|integer|min:1',
            'keterangan_packing' => 'nullable|string|max:255',
        ]);

        $packing->update([
            'tgl_packing'        => $validated['tgl_packing'],
            'status_packing'     => $validated['status_packing'],
            'nama_packer'        => $validated['nama_packer'] ?? null,
            'berat_aktual'       => $validated['berat_aktual'] ?? null,
            'koli'               => $validated['koli'] ?? null,
            'keterangan_packing' => $validated['keterangan_packing'] ?? null,
            'packing_by'         => Auth::id(),
            'packing_at'         => now(),
        ]);

        $packing->refresh();

        if ($packing->status_packing === 'selesai') {
            $jakartaAktif = JakartaAktif::where('id_pesan', $packing->no_pl)->first();

            DistributionOrder::updateOrCreate(
                ['packing_id' => $packing->id],
                [
                    'no_pl'             => $packing->no_pl,
                    'tgl_turun_pl'      => $packing->tgl_turun_pl,
                    'nama_unit'         => $packing->nama_unit,
                    'nama_barang'       => $packing->nama_barang,
                    'tgl_bayar'         => $packing->tgl_bayar,
                    'jumlah_bayar'      => $packing->jumlah_bayar,
                    'tgl_estimasi'      => $packing->tgl_estimasi,
                    'jenis_pengiriman'  => $jakartaAktif
                        ? ($jakartaAktif->status_kirim === 'Diambil' ? 'diambil_sendiri' : 'ekspedisi')
                        : null,
                    'ekspedisi'         => $jakartaAktif?->ekspedisi,
                    'service'           => $jakartaAktif?->service_pengiriman,
                    'status_pengiriman' => 'belum_pickup',
                    'distribution_at'   => now(),
                    'created_by'        => Auth::id(),
                    'updated_by'        => Auth::id(),
                ]
            );
        }

        return back()->with('success', 'Data Packing berhasil disimpan.');
    }
}
