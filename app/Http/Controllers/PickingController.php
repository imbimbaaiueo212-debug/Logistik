<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\PickingItem;
use App\Models\JakartaAktif;
use App\Models\RealisasiAktif;   // ← TAMBAHKAN INI
use Illuminate\Support\Facades\Auth;   // ← TAMBAHKAN INI

class PickingController extends Controller
{
    public function index()
    {
        return view('picking.index');
    }

    public function create(Request $request)
    {
        $order = null;
        if ($request->has('order_id')) {
            $order = JakartaAktif::find($request->order_id);
        }

        return view('picking.create', compact('order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'nullable|exists:jakarta_aktif,id',
            'nama_unit' => 'required',
            'billing_last_name' => 'required',
            'billing_company' => 'required',
            'tgl_order' => 'required|date',
        ]);

        $order = $request->filled('order_id') 
                    ? JakartaAktif::find($request->order_id) 
                    : null;

        $picking = Picking::create([
            'jakarta_aktif_id'   => $request->order_id,
            'no_pl'              => 'PL-' . date('Ymd-His'),
            'tgl_order'          => $validated['tgl_order'],
            'nama_unit'          => $validated['nama_unit'],
            'billing_last_name'  => $validated['billing_last_name'],
            'billing_company'    => $validated['billing_company'],
            'kirim'              => $order?->kirim,
            'total_item'         => 1,
            'total_qty'          => 1,
            'status'             => 'completed',
            'printed_at'         => now(),
            'created_by' => Auth::id(),
            'catatan'            => 'Dibuat manual dari Jakarta Aktif',
        ]);

        // Simpan item
        PickingItem::create([
            'picking_id' => $picking->id,
            'item_name'  => $order?->pesanan ?? 'Produk Utama',
            'item_sku'   => '-',
            'item_qty'   => 1,
            'qty_picked' => 1,
            'cek'        => true,
        ]);

        // Update Jakarta Aktif
        if ($order) {
            $order->update([
                'picking_generated' => true,
                'picking_id'        => $picking->id
            ]);
        }

        return redirect()->route('picking.jakarta.aktif')
                         ->with('success', 'Picking List berhasil dibuat!');
    }

    public function jakartaAktif()
    {
        $data = Picking::with(['jakartaAktif', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('picking.jakarta.aktif', compact('data'));
    }

    // Generate All & Generate Single tetap sama (sudah cukup bagus)
        /**
     * Generate Picking untuk SEMUA data di Realisasi Aktif yang belum punya Picking
     */
    public function generateAll()
    {
        $realisasiList = RealisasiAktif::whereDoesntHave('picking')  // Belum ada relasi picking
                            ->orWhereNull('picking_id')             // Safety
                            ->get();

        $successCount = 0;

        foreach ($realisasiList as $realisasi) {
            if ($this->generatePickingFromRealisasi($realisasi->id)) {
                $successCount++;
            }
        }

        return redirect()->route('picking.jakarta.aktif')
                         ->with('success', "$successCount Picking List berhasil digenerate dari Realisasi Aktif!");
    }

    /**
     * Generate Picking dari Realisasi Aktif
     */
    public function generatePickingFromRealisasi($realisasiId)
    {
        $realisasi = RealisasiAktif::with('jakartaAktif')->findOrFail($realisasiId);

        // Cegah duplikat
        if ($realisasi->picking_id) {
            return null;
        }

        $jakarta = $realisasi->jakartaAktif;

        $picking = Picking::create([
            'jakarta_aktif_id'   => $jakarta?->id,
            'no_pl'              => $realisasi->no_pl,
            'tgl_order'          => $realisasi->tgl_turun_pl ?? $jakarta?->tgl_pesan,
            'tgl_picking'        => now(),
            'jam_picking'        => now()->format('H:i:s'),
            'id_pesan'           => $jakarta?->id_pesan ?? $realisasi->no_pl,
            'cabang'             => $jakarta?->cabang,
            'vendor'             => $jakarta?->vendor,
            'nama_unit'          => $realisasi->nama_unit,
            'billing_last_name'  => $realisasi->billing_last_name ?? $jakarta?->billing_last_name,
            'billing_company'    => $realisasi->billing_company ?? $jakarta?->billing_company,
            'kirim'              => $jakarta?->kirim,
            'no_telpon'          => $jakarta?->no_telpon,
            'alamat_kirim'       => $jakarta?->alamat_kirim,
            'kab_kota_provinsi'  => $jakarta?->kab_kota_provinsi,
            'ekspedisi'          => $realisasi->pengiriman,
            'service_pengiriman' => $realisasi->service_pengiriman,
            'pesanan'            => $realisasi->nama_barang,
            'harga'              => $jakarta?->harga ?? 0,
            'diskon'             => $jakarta?->diskon ?? 0,
            'ongkir'             => $jakarta?->ongkir ?? 0,
            'total'              => $realisasi->jumlah_bayar ?? $jakarta?->total ?? 0,
            'berat'              => $realisasi->order_weight ?? $jakarta?->berat ?? 0,
            'total_item'         => 1,
            'total_qty'          => 1,
            'status'             => 'completed',
            'printed_at'         => now(),
            'created_by'         => Auth::id(),           // ← Diubah jadi ini
            'catatan'            => 'Auto generated from Realisasi Aktif',
        ]);

        // Buat item picking
        PickingItem::create([
            'picking_id' => $picking->id,
            'item_name'  => $realisasi->nama_barang ?? 'Produk dari Realisasi',
            'item_sku'   => '-',
            'item_qty'   => 1,
            'qty_picked' => 1,
            'cek'        => true,
        ]);

        // Update Realisasi Aktif
        $realisasi->update([
            'picking_id' => $picking->id
        ]);

        return $picking;
    }

    public function destroy($id)
    {
        $picking = Picking::findOrFail($id);

        JakartaAktif::where('id', $picking->jakarta_aktif_id)
                    ->update([
                        'picking_generated' => false,
                        'picking_id'        => null
                    ]);

        $picking->items()->delete();
        $picking->delete();

        return redirect()->back()
                         ->with('success', 'Picking List berhasil dihapus dan flag Jakarta Aktif di-reset.');
    }
}