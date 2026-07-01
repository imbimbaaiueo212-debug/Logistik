<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\PickingItem;
use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
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

    /**
     * Generate Picking dari Realisasi Aktif
     */
    public function generatePicking($jakartaId)
{
    $jakarta = JakartaAktif::findOrFail($jakartaId);

    if ($jakarta->picking_generated) {
        return back()->with('warning', 'Picking sudah dibuat.');
    }

    $items = BimbashopOrder::where('order_id', $jakarta->id_pesan)
                ->orderBy('item_sku')
                ->get();

    $picking = Picking::create([
        'jakarta_aktif_id'   => $jakarta->id,
        'no_pl'              => $jakarta->id_pesan,
        'tgl_order'          => $jakarta->tgl_pesan,
        'tgl_picking'        => now()->toDateString(),
        'jam_picking'        => now()->format('H:i:s'),

        'id_pesan'           => $jakarta->id_pesan,

        'nama_unit'          => $jakarta->nama_unit,
        'billing_last_name'  => $jakarta->billing_last_name,
        'billing_company'    => $jakarta->billing_company,

        'kirim'              => $jakarta->kirim,
        'no_telpon'          => $jakarta->no_telpon,
        'alamat_kirim'       => $jakarta->alamat_kirim,
        'kab_kota_provinsi'  => $jakarta->kab_kota_provinsi,

        'ekspedisi'          => $jakarta->ekspedisi,
        'service_pengiriman' => $jakarta->service_pengiriman,

        'harga'              => $jakarta->harga,
        'ongkir'             => $jakarta->ongkir,
        'berat'              => $jakarta->berat,
        'total'              => $jakarta->total,

        'total_item'         => $items->count(),
        'total_qty'          => $items->sum('item_qty'),

        'status'             => 'completed',

        'printed_at'         => now(),
        'created_by'         => Auth::id(),

        'catatan'            => 'Generate otomatis dari Jakarta Aktif',
    ]);

    foreach ($items as $item) {

        PickingItem::create([
            'picking_id' => $picking->id,
            'item_name'  => $item->item_name,
            'item_sku'   => $item->item_sku,
            'item_qty'   => $item->item_qty,
            'qty_picked' => 0,
            'cek'        => false,
        ]);

    }

    $jakarta->update([
        'picking_generated' => true,
    ]);

    return redirect()
        ->route('picking.jakarta.aktif')
        ->with('success', 'Picking berhasil dibuat.');
}

    public function destroy($id)
    {
        $picking = Picking::findOrFail($id);

        JakartaAktif::where('id', $picking->jakarta_aktif_id)
    ->update([
        'picking_generated' => false,
    ]);

        $picking->items()->delete();
        $picking->delete();

        return redirect()->back()
                         ->with('success', 'Picking List berhasil dihapus dan flag Jakarta Aktif di-reset.');
    }
public function updateChecklist(Request $request)
{
    try {

        $picking = Picking::findOrFail($request->id);

        $checked = $request->boolean('checked');

        // Hanya simpan waktu terima
        $picking->tgl_terima = $checked ? now() : null;

        $picking->save();

        return response()->json([
            'success' => true,
            'tanggal' => optional($picking->tgl_terima)->format('d/m/Y'),
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ],500);

    }
}
public function updatePic(Request $request)
{
    $request->validate([
        'id'  => 'required|exists:pickings,id',
        'pic' => 'nullable|in:Asep,Arif,Rama,Riky',
    ]);

    $picking = Picking::findOrFail($request->id);

    $picking->pic = $request->pic;

    $picking->save();

    return response()->json([
        'success' => true
    ]);
}

public function updateStatus(Request $request)
{
    $request->validate([
        'id' => 'required|exists:pickings,id',
        'status_persiapan' => 'required|in:Belum Dipersiapkan,On Proses,Hold,Sudah Disiapkan',
    ]);

    $picking = Picking::findOrFail($request->id);

    $picking->status_persiapan = $request->status_persiapan;

    $picking->save();

    return response()->json([
        'success' => true
    ]);
}
}