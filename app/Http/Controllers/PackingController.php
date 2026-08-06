<?php

namespace App\Http\Controllers;

use App\Models\Packing;
use App\Models\ManualPacking;          // ← tambahkan
use App\Models\JakartaAktif;
use App\Models\DistributionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingController extends Controller
{
    public function index()
    {
        return view('packing.index');
    }

    public function jakartaAktif()
    {
        $data = Packing::with('picking')
            ->orderBy('tgl_estimasi')
            ->paginate(20);

        return view('packing.jakarta-aktif', compact('data'));
    }

    // ===== METHOD BARU: Order Manual =====
    public function orderManual(Request $request)
{
    // Ambil dari ManualQcOutgoing yang sudah LOLOS
    $query = \App\Models\ManualQcOutgoing::with(['manualPicking', 'manualPacking'])
        ->where('status_qc', 'Lolos')
        ->orderByDesc('created_at');

    // ===== Filter Kategori =====
    if ($request->filled('kategori')) {
        $kategori = $request->kategori;
        $query->where(function ($q) use ($kategori) {
            $q->where('kategori_order', 'like', "%{$kategori}%");
        });
    }

    // Filter lain
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', $search)
              ->orWhere('nama_unit', 'like', "%{$search}%");
        });
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', $request->nama_unit);
    }

    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    if ($request->filled('status_packing')) {
        $query->whereHas('manualPacking', function ($q) use ($request) {
            $q->where('status_packing', $request->status_packing);
        });
    }

    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $data = $query->paginate(20)->appends($request->query());

    // Data filter
    $baseQuery = \App\Models\ManualQcOutgoing::where('status_qc', 'Lolos');

    $namaUnitList = (clone $baseQuery)
        ->whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $grupList = (clone $baseQuery)
        ->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    return view('packing.order-manual', compact(
        'data',
        'namaUnitList',
        'grupList'
    ));
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

    /**
     * Update Data Packing Order Manual
     */
    public function updateManual(Request $request, $id)
    {
        $packing = ManualPacking::findOrFail($id);

        if ($packing->status_packing === 'Selesai') {
            return back()->with('error', 'Data packing sudah selesai dan tidak dapat diedit lagi.');
        }

        $validated = $request->validate([
            'tgl_packing'        => 'nullable|date',
            'status_packing'     => 'required|in:Pending,Proses,Selesai,Batal',
            'nama_packer'        => 'nullable|string|max:100',
            'berat_aktual'       => 'nullable|numeric|min:0',
            'koli'               => 'nullable|string|max:50',
            'keterangan_packing' => 'nullable|string|max:255',
        ]);

        $packing->update([
            'tgl_packing'        => $validated['tgl_packing'] ?? null,
            'status_packing'     => $validated['status_packing'],
            'nama_packer'        => $validated['nama_packer'] ?? null,
            'berat_aktual'       => $validated['berat_aktual'] ?? null,
            'koli'               => $validated['koli'] ?? null,
            'keterangan_packing' => $validated['keterangan_packing'] ?? null,
            'packing_by'         => Auth::id(),
            'packing_at'         => now(),
        ]);

        return back()->with('success', 'Data Packing Manual berhasil disimpan.');
    }
}