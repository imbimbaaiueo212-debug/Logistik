<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\PickingItem;
use App\Models\JakartaAktif;
use App\Models\QcOutgoing;
use App\Models\BimbashopOrder;
use Illuminate\Support\Facades\Auth;   // ← TAMBAHKAN INI
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function jakartaAktif(Request $request)
{
    $query = Picking::with([
        'jakartaAktif',
        'pickingItems.product'
    ]);

    // ==================== FILTER KATEGORI ====================
    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    // ==================== FILTER LAINNYA ====================
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

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_order', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_order', '<=', $request->end_date);
    }

    $data = $query->orderBy('created_at', 'desc')
                  ->paginate(20)
                  ->appends($request->query());

    return view('picking.jakarta.aktif', compact('data'));
}

    /**
     * Generate Picking dari Realisasi Aktif
     */
    public function generatePicking($jakartaId)
    {
        $jakarta = JakartaAktif::with(['items.product'])->findOrFail($jakartaId);

        if ($jakarta->picking_generated) {
            return back()->with('warning', 'Picking sudah pernah dibuat.');
        }

        $allItems = $jakarta->items()->with('product')->get();

        if ($allItems->isEmpty()) {
            return back()->with('error', 'Tidak ada item untuk dibuat picking.');
        }

        // =============================================
        // PEMISAHAN KATEGORI (sama seperti OrderController)
        // =============================================
        $groups = [
            'Modul'      => collect(),
            'Majalah'    => collect(),
            'Sertifikat' => collect(),
            'Lainnya'    => collect(),
        ];

        foreach ($allItems as $item) {
            $kategori = trim($item->product?->kategori ?? $item->nama_produk ?? '');

            $kategoriLower = strtolower($kategori);

            if (str_contains($kategoriLower, 'majalah')) {
                $groups['Majalah']->push($item);
            } 
            elseif (str_contains($kategoriLower, 'sertifikat')) {
                $groups['Sertifikat']->push($item);
            } 
            elseif (str_contains($kategoriLower, 'modul') || str_contains($kategoriLower, 'bimba')) {
                $groups['Modul']->push($item);
            } else {
                $groups['Lainnya']->push($item);
            }
        }

        $createdCount = 0;

        DB::transaction(function () use ($jakarta, $groups, &$createdCount) {

            foreach ($groups as $kategoriOrder => $items) {

                if ($items->isEmpty()) continue;

                // Buat Picking per kategori
                $picking = Picking::create([
                    'jakarta_aktif_id'   => $jakarta->id,
                    'no_pl'              => $jakarta->id_pesan . '-' . strtoupper(substr($kategoriOrder, 0, 3)),
                    'tgl_order'          => $jakarta->tgl_pesan,
                    'tgl_picking'        => now()->toDateString(),
                    'jam_picking'        => now()->format('H:i:s'),
                    'id_pesan'           => $jakarta->id_pesan,
                    'kategori_order'     => $kategoriOrder,           // ← Penting

                    'nama_unit'          => $jakarta->nama_unit,
                    'billing_last_name'  => $jakarta->billing_last_name,
                    'billing_company'    => $jakarta->billing_company,

                    'kirim'              => $jakarta->kirim,
                    'no_telpon'          => $jakarta->no_telpon,
                    'alamat_kirim'       => $jakarta->alamat_kirim,
                    'kab_kota_provinsi'  => $jakarta->kab_kota_provinsi,

                    'ekspedisi'          => $jakarta->ekspedisi,
                    'service_pengiriman' => $jakarta->service_pengiriman,

                    'total_item'         => $items->count(),
                    'total_qty'          => $items->sum('qty'),

                    'status'             => 'completed',
                    'printed_at'         => now(),
                    'created_by'         => Auth::id(),
                    'catatan'            => "Auto generate - {$kategoriOrder}",
                ]);

                // Simpan item
                foreach ($items as $item) {
                    PickingItem::create([
                        'picking_id' => $picking->id,
                        'product_id' => $item->product_id ?? $item->product?->id,
                        'item_name'  => $item->product?->nama_produk ?? $item->nama_produk ?? $item->label ?? '-',
                        'item_sku'   => $item->sku,
                        'item_qty'   => (int) $item->qty,
                        'qty_picked' => 0,
                        'cek'        => false,
                    ]);
                }

                $createdCount++;
            }

            // Tandai sudah generate
            $jakarta->update(['picking_generated' => true]);
        });

        return redirect()
            ->route('picking.jakarta.aktif')
            ->with('success', "✅ Berhasil membuat {$createdCount} Picking List berdasarkan kategori.");
    }

    public function destroy($id)
{
    $picking = Picking::findOrFail($id);

    // Hapus data QC yang terkait
    QcOutgoing::where('picking_id', $picking->id)->delete();
    QcOutgoing::where('no_pl', $picking->no_pl)->delete();

    // Reset flag di Jakarta Aktif
    JakartaAktif::where('id', $picking->jakarta_aktif_id)
        ->update(['picking_generated' => false]);

    // Hapus Picking
    $picking->items()->delete();
    $picking->delete();

    return redirect()->back()
                     ->with('success', 'Picking List dan data QC terkait berhasil dihapus.');
}
public function updateChecklist(Request $request)
{
    try {
        $picking = Picking::findOrFail($request->id);
        $checked = $request->boolean('checked');

        if ($checked) {
            $picking->tgl_terima = now();
            // JANGAN otomatis set ke Sudah, biarkan user yang pilih status
            // $picking->status_persiapan = 'Sudah Disiapkan';
            $picking->tgl_picking = now()->toDateString();
        } else {
            $picking->tgl_terima = null;
            $picking->tgl_picking = null;
            $picking->status_persiapan = 'Belum';
        }

        $picking->save();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
    DB::beginTransaction();

    try {

        $request->validate([
            'id' => 'required|exists:pickings,id',
            'status_persiapan' => 'required|in:Belum,Sudah',
        ]);

        $picking = Picking::with(['items','jakartaAktif'])
            ->findOrFail($request->id);

        $picking->status_persiapan = $request->status_persiapan;

        if (!$picking->tgl_picking) {
            $picking->tgl_picking = now()->toDateString();
        }

        $picking->save();

        Log::info('STEP 1 : Picking berhasil disimpan');

        if ($request->status_persiapan == 'Sudah') {

            $ja = $picking->jakartaAktif;

            Log::info('STEP 2 : Relasi Jakarta Aktif', [
                'ada' => $ja ? true : false
            ]);
QcOutgoing::updateOrCreate(
    [
        'picking_id' => $picking->id,
    ],
    [
        'picking_id'     => $picking->id,
        'no_pl'          => $picking->no_pl,
        'tgl_turun_pl'   => $picking->tgl_picking,
        'nama_unit'      => $picking->nama_unit,

        'pengiriman' => $picking->ekspedisi,

        'nama_barang' => $picking->pesanan,

        'tgl_bayar'      => $ja?->payment_date,
        'jumlah_bayar'   => $ja?->harga ?? 0,
        'tgl_estimasi'   => $ja?->estimasi_persiapan,
        'nama_stokis'    => $ja?->nama_stokis ?? '-',
        'estimasi_hari'  => $ja?->estimasi_hari,

        'kode_qc' => null,

        'tgl_qc'      => now(),
        'status_qc'   => 'Pending',
        'keterangan'  => null,
        'created_by'  => Auth::id(),
    ]
);

            Log::info('STEP 3 : QC berhasil dibuat');
        }

        DB::commit();

        return response()->json([
            'success' => true
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error($e);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ],500);

    }
}

public function orderManual(Request $request)
{
    $query = \App\Models\ManualPicking::with(['manualRealisasi', 'pickingItems']);

    // Filter Kategori
    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    // Filter No PL / ID Pesan
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('no_pl', $search)
              ->orWhere('id_pesan', $search);
        });
    }

    // Filter Nama Unit
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', $request->nama_unit);
    }

    // Filter Grup
    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    // Filter Tanggal
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_order', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_order', '<=', $request->end_date);
    }

    $data = $query->orderBy('created_at', 'desc')
                  ->paginate(20)
                  ->appends($request->query());

    // ===== Data untuk Select2 =====
    $noPlList = \App\Models\ManualPicking::whereNotNull('no_pl')
        ->where('no_pl', '!=', '')
        ->distinct()
        ->orderBy('no_pl')
        ->pluck('no_pl');

    $namaUnitList = \App\Models\ManualPicking::whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $grupList = \App\Models\ManualPicking::whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    return view('picking.order-manual', compact('data', 'noPlList', 'namaUnitList', 'grupList'));
}

}