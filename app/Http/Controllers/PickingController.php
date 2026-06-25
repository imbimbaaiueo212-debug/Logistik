<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\PickingItem;
use App\Models\JakartaAktif;

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
        'status_kirim' => 'required',
    ]);

    $picking = Picking::create([
        'no_pl'              => 'PL-' . date('Ymd-His'),
        'tgl_order'          => $validated['tgl_order'],
        'nama_unit'          => $validated['nama_unit'],
        'billing_last_name'  => $validated['billing_last_name'],
        'billing_company'    => $validated['billing_company'],
        'status_kirim'       => $validated['status_kirim'],
        'total_item'         => 1,
        'total_qty'          => 1,
        'status'             => 'completed',
        'printed_at'         => now(),
        'created_by'         => auth()->id(),
        'catatan'            => 'Dibuat manual dari Jakarta Aktif',
    ]);

    // Simpan item
    PickingItem::create([
        'picking_id' => $picking->id,
        'item_name'  => 'Produk Utama',
        'item_sku'   => '-',
        'item_qty'   => 1,
        'qty_picked' => 1,
        'cek'        => true,
    ]);

    // === UPDATE STATUS DI JAKARTA AKTIF ===
    if ($request->filled('order_id')) {
        JakartaAktif::where('id', $request->order_id)
            ->update([
                'picking_generated' => true,
                'picking_id'        => $picking->id
            ]);
    }

    return redirect()->route('picking.jakarta.aktif')
                     ->with('success', 'Picking List berhasil dibuat dan status sudah diupdate!');
}

public function jakartaAktif()
{
    $data = Picking::with(['jakartaAktif', 'items'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('picking.jakarta.aktif', compact('data'));
}

    /**
     * Generate Picking untuk SEMUA order yang belum digenerate
     */
    public function generateAll()
{
    $orders = JakartaAktif::where('picking_generated', false)
                ->orWhereNull('picking_generated')
                ->with('realisasi')
                ->get();

    $successCount = 0;

    foreach ($orders as $order) {
        $result = $this->generatePickingFromOrder($order->id);
        if ($result) $successCount++;
    }

    return redirect()->route('picking.jakarta.aktif')
                     ->with('success', "$successCount Picking List berhasil digenerate!");
}

    public function generatePickingFromOrder($orderId)
{
    $order = JakartaAktif::with(['realisasi' => function($q) {
        $q->select('id', 'jakarta_aktif_id', 'no_pl', 'created_at');   // ← Ubah ke 'no_pl'
    }])->findOrFail($orderId);

    // Cegah duplikat
    if ($order->picking_generated == true) {
        return null;
    }

    $realisasi = $order->realisasi;

    // === no_pl DIAMBIL DARI realisasi_aktif.no_pl ===
    $noPl = $realisasi?->no_pl;   // ← Diubah ke no_pl

    $picking = Picking::create([
        'no_pl'              => $noPl,          // Langsung ambil no_pl dari realisasi
        'tgl_order'          => $order->tgl_pesan,
        'tgl_picking'        => $realisasi?->created_at ?? now(),
        'jam_picking'        => $realisasi?->created_at 
                                ? \Carbon\Carbon::parse($realisasi->created_at)->format('H:i') 
                                : now()->format('H:i'),
        'nama_unit'          => $order->nama_unit,
        'billing_last_name'  => $order->billing_last_name,
        'billing_company'    => $order->billing_company,
        'status_kirim'       => $order->status_kirim ?? 'Ambil Sendiri',
        'total_item'         => 1,
        'total_qty'          => 1,
        'status'             => 'completed',
        'printed_at'         => now(),
        'created_by'         => auth()->id(),
        'catatan'            => 'Auto generated from Jakarta Aktif',
    ]);

    // Simpan item
    PickingItem::create([
        'picking_id' => $picking->id,
        'item_name'  => $order->pesanan ?? 'Produk dari Order',
        'item_sku'   => '-',
        'item_qty'   => 1,
        'qty_picked' => 1,
        'cek'        => true,
    ]);

    // Update flag
    $order->update([
        'picking_generated' => true,
        'picking_id'        => $picking->id
    ]);

    return $picking;
}

/**
 * Hapus Picking List + Reset Flag di Jakarta Aktif
 */
/**
 * Hapus Picking List + Reset Flag di Jakarta Aktif
 */
public function destroy($id)
{
    $picking = Picking::findOrFail($id);

    // Reset flag di Jakarta Aktif
    JakartaAktif::where('picking_id', $picking->id)
                ->update([
                    'picking_generated' => false,
                    'picking_id'        => null
                ]);

    // Hapus item terlebih dahulu
    $picking->items()->delete();

    // Hapus picking
    $picking->delete();

    return redirect()->back()
                     ->with('success', 'Picking List berhasil dihapus dan flag Jakarta Aktif telah di-reset.');
}

}