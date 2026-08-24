<?php

namespace App\Http\Controllers;

use App\Models\DistributionOrder;
use App\Models\ManualDistributionOrder;
use App\Models\ManualPacking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributionOrderController extends Controller
{
    /**
     * Halaman utama pilihan Distribution Order
     */
    public function index()
    {
        return view('distribution-order.index');
    }

    /**
     * Distribution Order Jakarta Aktif
     */
    public function jakartaAktif(Request $request)
{
    $query = DistributionOrder::with(['jakartaAktif', 'packing'])
        ->whereHas('jakartaAktif');   // ← use relationship instead of non-existent column

    if ($request->filled('status_pengiriman')) {
        $query->where('status_pengiriman', $request->status_pengiriman);
    }

    if ($request->filled('jenis_pengiriman')) {
        $query->where('jenis_pengiriman', $request->jenis_pengiriman);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', 'like', "%{$search}%")
              ->orWhere('nama_barang', 'like', "%{$search}%")
              ->orWhere('nama_unit', 'like', "%{$search}%");
        });
    }

    $distributionOrders = $query->orderByDesc('created_at')
        ->paginate(25)
        ->appends($request->query());

    return view('distribution-order.jakarta-aktif', compact('distributionOrders'));
}

    /**
     * Distribution Order Jakarta Pasif
     */
    public function jakartaPasif(Request $request)
    {
        // Sesuaikan query sesuai kebutuhan
        $distributionOrders = collect(); // ganti dengan query asli
        return view('distribution-order.jakarta-pasif', compact('distributionOrders'));
    }

    /**
     * Distribution Order InterVio (DLC)
     */
    public function intervio(Request $request)
    {
        $distributionOrders = collect();
        return view('distribution-order.intervio', compact('distributionOrders'));
    }

    /**
     * Distribution Order English biMBA Talk (EBT)
     */
    public function ebt(Request $request)
    {
        $distributionOrders = collect();
        return view('distribution-order.ebt', compact('distributionOrders'));
    }

    /**
     * Form create Distribution Order
     */
    public function create()
    {
        return view('distribution-order.create');
    }

    /**
     * Simpan Distribution Order baru
     */
    public function store(Request $request)
    {
        // Sesuaikan validasi & logic sesuai kebutuhan
        $validated = $request->validate([
            'no_pl' => 'required|string',
            // tambahkan field lain
        ]);

        DistributionOrder::create($validated);

        return redirect()
            ->route('distribution-order.index')
            ->with('success', 'Distribution Order berhasil ditambahkan');
    }

    /**
     * Detail Distribution Order
     */
    public function show($id)
    {
        $distributionOrder = DistributionOrder::findOrFail($id);
        return view('distribution-order.show', compact('distributionOrder'));
    }

    /**
     * Form edit Distribution Order
     */
    public function edit($id)
    {
        $distributionOrder = DistributionOrder::findOrFail($id);
        return view('distribution-order.edit', compact('distributionOrder'));
    }

    /**
     * Update Distribution Order
     */
    public function update(Request $request, $id)
    {
        $distributionOrder = DistributionOrder::findOrFail($id);

        $distributionOrder->update($request->all());

        return redirect()
            ->back()
            ->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Hapus Distribution Order
     */
    public function destroy($id)
    {
        $distributionOrder = DistributionOrder::findOrFail($id);
        $distributionOrder->delete();

        return redirect()
            ->back()
            ->with('success', 'Data berhasil dihapus');
    }

    // =========================================================
    // DISTRIBUTION MANUAL
    // =========================================================

    /**
     * Halaman list Distribution Manual
     */
    public function manual(Request $request)
    {
        $query = ManualDistributionOrder::query()
            ->orderByDesc('created_at');

        // Filter Status
        if ($request->filled('status_distribusi')) {
            $query->where('status_distribusi', $request->status_distribusi);
        }

        // Filter Kategori
        if ($request->filled('kategori')) {
            $query->where('kategori_order', $request->kategori);
        }

        // Filter Grup
        if ($request->filled('grup')) {
            $query->where('grup', $request->grup);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_pl', 'like', "%{$search}%")
                  ->orWhere('no_ps', 'like', "%{$search}%")
                  ->orWhere('nama_unit', 'like', "%{$search}%");
            });
        }

        $data = $query->paginate(25)->appends($request->query());

        return view('distribution-order.manual', compact('data'));
    }

    /**
     * Update data Distribution Manual (AJAX)
     */
    public function updateManual(Request $request, $id)
    {
        try {
            $item = ManualDistributionOrder::findOrFail($id);

            $item->update([
                'tgl_kirim'         => $request->tgl_kirim ?: null,
                'no_resi'           => $request->no_resi,
                'status_distribusi' => $request->status_distribusi ?? $item->status_distribusi,
                'keterangan'        => $request->keterangan,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            Log::error('updateManual gagal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Buat Distribution Manual dari Packing yang sudah selesai
     * (bisa dipanggil dari controller Packing Manual)
     */
    public static function createFromPacking(ManualPacking $packing): ManualDistributionOrder
    {
        return ManualDistributionOrder::firstOrCreate(
            [
                'manual_packing_id' => $packing->id,
            ],
            [
                'manual_picking_id'  => $packing->manual_picking_id,
                'no_pl'              => $packing->no_pl,
                'no_ps'              => $packing->no_ps,
                'nama_unit'          => $packing->nama_unit,
                'grup'               => $packing->grup,
                'kategori_order'     => $packing->kategori_order,
                'ekspedisi'          => optional($packing->manualPicking)->ekspedisi,
                'service_pengiriman' => optional($packing->manualPicking)->service_pengiriman,
                'status_kirim'       => optional($packing->manualPicking)->status_kirim ?? 'Dikirim',
                'berat'              => $packing->berat,
                'berat_aktual'       => $packing->berat_aktual,
                'koli'               => $packing->koli,
                'status_distribusi'  => 'Pending',
                'created_by'         => Auth::id(),
            ]
        );
    }
}