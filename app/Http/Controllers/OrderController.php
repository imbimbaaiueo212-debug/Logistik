<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Models\RealisasiAktif;
use App\Models\Picking;          // Tambahkan
use App\Models\PickingItem;      // Tambahkan
use App\Models\JakartaAktifItem;
use App\Models\Product;

use App\Imports\JakartaAktifImport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // Tambahkan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        return view('order.index');
    }

public function jakartaAktif(Request $request)
{
    $query = JakartaAktif::query();

    // === FILTER (sama seperti sebelumnya) ===
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }
    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }
    if ($request->filled('status_pesan')) {
        $query->where('status_pesan', $request->status_pesan);
    }
    if ($request->filled('validasi')) {
        $query->where('validasi', $request->validasi);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }
    if ($request->filled('pesanan')) {
        $query->where('pesanan', 'like', '%' . $request->pesanan . '%');
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 5);
    $perPage = in_array($perPage, [5, 10, 20, 50, 100, 200, 500]) ? $perPage : 5;

    $data = $query
        ->with(['casdana' => function ($q) {
            $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer');
        }])
        ->selectRaw('*, payment_date')           // Pastikan kolom ikut ter-load
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}

    // Import Manual
    public function importJakartaAktif(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ]);

        try {
            Excel::import(new JakartaAktifImport, $request->file('file'));
            return redirect()->route('order.jakarta-aktif')
                             ->with('success', '✅ Data berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', '❌ Gagal import: ' . $e->getMessage());
        }
    }

       
/**
 * Sync dari Bimbashop + Casdana 
 * Hanya JKT murni (dikecualikan JKTP dan semua stokis lain)
 */
public function syncJktFromBimbashop()
{
    $totalOrder = 0;
    $skipExists = 0;
    $skipCasdana = 0;
    $skipStatus = 0;
    $inserted = 0;

    $excludedSkus = [
        'JKTP', 'PUA1', 'PUA2', 'PUA3', 'DPK1', 'SRG1', 'KWG1', 'BKS1', 
        'BGR1', 'TNG1', 'SNG', 'BGRT', 'PWK', 'TNG2', 'KNG', 'IDM', 
        'SKB1', 'SKB2', 'BDG1', 'BDG2', 'CIL1', 'SRG2', 'DPR1', 'KWG2', 
        'BGR3', '-LG', 'DLC', 'EBT', 'SMG', 'SBY', 'YYK', 'INV', 'SGN', 
        'YK1', 'GR2', 'ENB', 'RB1', 'TNG3'
    ];

    $bimbashopOrders = BimbashopOrder::where('item_sku', 'like', '%JKT%')
                        ->whereNotIn('item_sku', $excludedSkus)
                        ->where(function($q) use ($excludedSkus) {
                            foreach ($excludedSkus as $sku) {
                                $q->where('item_sku', 'not like', "%{$sku}%");
                            }
                        })
                        ->get()
                        ->groupBy('order_id');

    $totalOrder = $bimbashopOrders->count();

    foreach ($bimbashopOrders as $orderId => $items) {
        
        if (JakartaAktif::where('id_pesan', $orderId)->exists()) {
            $skipExists++;
            continue;
        }

        $firstItem = $items->first();

        // =====================================================
        // QUERY CASDANA (versi fleksibel)
        // =====================================================
        $casdana = CasdanaTransaction::where('invoice_number', 'like', "%{$orderId}%")
                    ->orWhere('invoice_number', $orderId)
                    ->latest('id')
                    ->first();

        if (!$casdana) {
            $skipCasdana++;
            continue;
        }

        $statusCasdana = strtoupper(trim($casdana->status ?? ''));
        if (!in_array($statusCasdana, ['SUCCESS', 'SETTLED'])) {
            $skipStatus++;
            continue;
        }

        // =====================================================
        // ESTIMASI WAKTU
        // =====================================================
        $paymentDate = $casdana->payment_date;
        $estimasiPrintPl = null;
        $estimasiPersiapan = null;

        if ($paymentDate) {
            $payment = Carbon::parse($paymentDate);
            $estimasiPrintPl = $payment->hour < 12 
                ? $payment->copy() 
                : $payment->copy()->addDay();

            while ($estimasiPrintPl->isSunday() || $this->isHoliday($estimasiPrintPl)) {
                $estimasiPrintPl->addDay();
            }

            $estimasiPersiapan = $this->addBusinessDays($estimasiPrintPl, 2);
        }

        // =====================================================
        // PRODUCT CACHE + KATEGORI LIST
        // =====================================================
        $productCache = [];
        $kategoriList = [];

        foreach ($items as $item) {
            $sku = strtoupper(trim($item->item_sku ?? ''));
            if (empty($sku)) continue;

            $searchCode = trim(explode('-', $sku)[0]);

            if (!isset($productCache[$searchCode])) {
                $productCache[$searchCode] = $this->findProductBySku($sku, $item->item_name ?? '');
            }

            $product = $productCache[$searchCode];

            if ($product) {
                $kategoriList[] = trim($product->sub_kategori ?? $product->kategori ?? $product->name ?? $product->label);
            } else {
                $itemName = trim($item->item_name ?? '');
                $clean = str_ireplace(['JKT', 'biMBA', 'Unit', 'Reguler'], '', $itemName);
                $clean = preg_replace('/\s+/', ' ', $clean);
                $kategoriList[] = trim($clean) ?: trim(preg_replace('/\s+/', ' ', str_ireplace(['JKT', '-JKT'], '', $sku)));
            }
        }

        $kategoriList = collect($kategoriList)->filter()->unique()->values();

        $pesanan = $kategoriList->isEmpty()
            ? 'Media Pembelajaran bimBA AIUEO'
            : ($kategoriList->count() > 6 
                ? $kategoriList->take(5)->implode(' | ') . ' + ...'
                : $kategoriList->implode(' | '));

        // NAMA UNIT
        $namaUnit = $firstItem->billing_company 
            ?: trim(($firstItem->billing_first_name ?? '') . ' ' . ($firstItem->billing_last_name ?? ''));

        $namaUnit = $namaUnit ?: ($firstItem->item_name ?? $casdana->customer ?? '-');

        // ALAMAT & STATUS KIRIM
        $kirim = trim(implode(', ', array_filter([
            $firstItem->shipping_address_1,
            $firstItem->shipping_address_2,
            $firstItem->shipping_city
        ]))) ?: $namaUnit;

        $ongkir = (int) ($firstItem->ship_total ?? 0);
        $statusKirim = $ongkir > 0 ? 'Dikirim' : 'Diambil';

        // DATA HEADER
        $data = [
            'tgl_input'          => now()->format('Y-m-d'),
            'tgl_pesan'          => $firstItem->order_date,
            'kirim'              => $kirim,
            'no_telpon'          => $firstItem->shipping_phone ?? null,
            'alamat_kirim'       => $firstItem->shipping_address_1 ?? null,
            'kab_kota_provinsi'  => $firstItem->shipping_city ?? null,
            'ongkir'             => $ongkir,
            'nama_unit'          => $namaUnit,
            'pesanan'            => $pesanan,
            'harga'              => $items->sum(fn($item) => ($item->item_price ?? 0) * ($item->item_qty ?? 1)),
            'berat'              => $firstItem->order_weight ?? 0,
            'item_qty'           => $items->sum('item_qty'),
            'total'              => $casdana->amount ?? $firstItem->order_total ?? 0,
            'jenis_bank'         => $casdana->payment_channel ?? $firstItem->payment_method,
            'status_pembayaran'  => $statusCasdana,
            'status_pesan'       => $firstItem->status,
            'id_pesan'           => $orderId,
            'status'             => 'aktif',
            'payment_date'       => $paymentDate,
            'amount'             => $casdana->amount ?? 0,
            'billing_last_name'  => $firstItem->billing_last_name ?? null,
            'billing_company'    => $firstItem->billing_company ?? null,
            'status_kirim'       => $statusKirim,
            'estimasi_print_pl'  => $estimasiPrintPl,
            'estimasi_persiapan' => $estimasiPersiapan,
        ];

        // TRANSACTION + CREATE
        DB::transaction(function () use ($data, $items, $productCache, &$inserted) {
            $jakarta = JakartaAktif::create($data);

            foreach ($items as $item) {
                $sku = strtoupper(trim($item->item_sku ?? ''));
                $searchCode = trim(explode('-', $sku)[0]);
                $product = $productCache[$searchCode] ?? null;

                $qty = (int) ($item->item_qty ?? 1);
                $harga = (float) ($item->item_price ?? 0);

                JakartaAktifItem::create([
                    'jakarta_aktif_id' => $jakarta->id,
                    'product_id'       => $product?->id,
                    'sku'              => $sku,
                    'label'            => $product?->label ?? $searchCode,
                    'nama_produk'      => $product?->name ?? $item->item_name,
                    'qty'              => $qty,
                    'harga'            => $harga,
                    'subtotal'         => $qty * $harga,
                ]);
            }

            $inserted++;
        });
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "✅ Berhasil sync {$inserted} data JKT murni!");
}

private function findProductBySku(string $sku, string $itemName = '')
{
    $searchCode = trim(explode('-', strtoupper($sku))[0] ?? '');

    if (empty($searchCode)) {
        return null;
    }

    // Prioritas utama: berdasarkan label
    $product = Product::where(function ($q) use ($searchCode) {
            $q->where('label', $searchCode)
              ->orWhere('label', 'like', $searchCode . '-%')
              ->orWhere('label', 'like', $searchCode . ' %');
        })->first();

    // Fallback ke nama produk hanya jika label tidak ditemukan
    if (!$product && !empty($itemName)) {
        $product = Product::where('name', $itemName)->first();
    }

    return $product;
}
/**
 * Hitung +3 Hari Kerja sambil mempertahankan jam & menit
 */
private function addBusinessDays(Carbon $startDate, int $days = 2)
{
    $date = $startDate->copy();

    $added = 0;

    while ($added < $days) {

        $date->addDay();

        if (
            $date->isSunday() ||
            $this->isHoliday($date)
        ) {
            continue;
        }

        $added++;
    }

    return $date;
}

/**
 * Daftar Hari Libur Nasional 2026
 */
private function isHoliday($date)
{
    $holidays = [
        '2026-01-01',
        '2026-01-16',
        '2026-02-17',
        '2026-03-19',
        '2026-03-21',
        '2026-03-22',
        '2026-04-03',
        '2026-04-05',
        '2026-05-01',
        '2026-05-14',
        '2026-05-27',
        '2026-06-01',
        '2026-06-16',
        '2026-08-17',
        '2026-12-25',
    ];

    return in_array($date->format('Y-m-d'), $holidays);
}

    public function unitAktif() { return view('order.unit-aktif'); }
    public function unitPasif() { return view('order.unit-pasif'); }

        // ====================== EDIT JAKARTA AKTIF ======================
    public function editJakartaAktif($id)
{
    $item = JakartaAktif::findOrFail($id);
    return view('order.jakarta-aktif-edit', compact('item'));
}

public function updateJakartaAktif(Request $request, $id)
{
    $item = JakartaAktif::findOrFail($id);

    $request->validate([
    'nama_unit'          => 'nullable|string|max:255',
    'billing_last_name'  => 'nullable|string|max:100',
    'pesanan'            => 'nullable|string|max:255',
    'alamat_pengiriman'  => 'nullable|string',
    'service_pengiriman' => 'nullable|string|max:100',     // ← BARU
    'ekspedisi'          => 'nullable|string|max:100',
    'status_kirim'       => 'nullable|in:Dikirim,Belum Dikirim',
    'status_pembayaran'  => 'nullable|string|max:50',
    'validasi'           => 'nullable|string|max:50',
]);

$item->update([
    'nama_unit'          => $request->nama_unit,
    'billing_last_name'  => $request->billing_last_name,
    'pesanan'            => $request->pesanan,
    'alamat_pengiriman'  => $request->alamat_pengiriman,
    'kirim'              => $request->alamat_pengiriman,
    'service_pengiriman' => $request->service_pengiriman,   // ← BARU
    'ekspedisi'          => $request->ekspedisi,
    'status_kirim'       => $request->status_kirim,
    'status_pembayaran'  => $request->status_pembayaran,
    'validasi'           => $request->validasi,
    'catatan'            => ($item->catatan ?? '') . "\n\nDiubah manual pada " . now()->format('d/m/Y H:i:s'),
]);

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', '✅ Data berhasil diupdate!');
}




public function bulkActionJakartaAktif(Request $request)
{
    $action = $request->input('action');
    $perItem = $request->input('per_item');

    if ($action !== 'processed' || empty($perItem)) {
        return redirect()->back()->with('error', 'Data tidak valid.');
    }

    $updates = json_decode($perItem, true);
    if (empty($updates)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $successCount = 0;

    foreach ($updates as $item) {
        $id = $item['id'] ?? null;
        if (!$id) continue;

        // === 1. AMBIL DATA ===
        $jakarta = JakartaAktif::find($id);
        if (!$jakarta) continue;

        $statusKirim  = $item['status_kirim'] ?? $jakarta->status_kirim;
        $jasaKurir    = $item['jasa_kurir'] ?? $jakarta->ekspedisi;
        $serviceKurir = $item['service_kurir'] ?? $jakarta->service_pengiriman;
        $catatan      = $item['catatan'] ?? null;

        // === 2. KUNCI DULU PAKAI RAW SQL (yang sudah terbukti aman) ===
        $setClauses = [];
        $bindings   = [];

        $setClauses[] = "is_processed = 1";
        $setClauses[] = "processed_at = ?";
        $setClauses[] = "updated_at = ?";
        $bindings[] = $now;
        $bindings[] = $now;

        if ($statusKirim) {
            $setClauses[] = "status_kirim = ?";
            $bindings[] = $statusKirim;
        }
        if ($jasaKurir) {
            $setClauses[] = "ekspedisi = ?";
            $bindings[] = $jasaKurir;
        }
        if ($serviceKurir) {
            $setClauses[] = "service_pengiriman = ?";
            $bindings[] = $serviceKurir;
        }
        if ($catatan) {
            $newNote = "\n\nDi proses bulk pada " . $now->format('d/m/Y H:i') . ": " . trim($catatan);
            $setClauses[] = "catatan = CONCAT(COALESCE(catatan, ''), ?)";
            $bindings[] = $newNote;
        }

        $sql = "UPDATE jakarta_aktif 
                SET " . implode(', ', $setClauses) . "
                WHERE id = ?";

        $updated = DB::update($sql, array_merge($bindings, [$id]));

        if ($updated) {
          // === MASUKKAN KE REALISASI AKTIF ===
if (!RealisasiAktif::where('jakarta_aktif_id', $jakarta->id)->exists()) {

    $estimasiHari = null;
    if ($jakarta->payment_date && $jakarta->estimasi_persiapan) {
        $payment = \Carbon\Carbon::parse($jakarta->payment_date);
        $persiapan = \Carbon\Carbon::parse($jakarta->estimasi_persiapan);
        $estimasiHari = $payment->diffInDays($persiapan);
    }

    $namaStokis = $this->extractVendorFromSku($jakarta->pesanan ?? '');

    $pengiriman   = $jasaKurir ?: ($jakarta->ekspedisi ?? ($statusKirim === 'Diambil' ? 'Diambil' : '-'));
    
    // === LOGIKA BARU: Isi service_pengiriman otomatis ===
    $servicePengiriman = $serviceKurir;
    if (empty($servicePengiriman) && in_array(strtolower($statusKirim), ['diambil', 'diambil'])) {
        $servicePengiriman = 'Diambil';
    }

    $realisasi = RealisasiAktif::create([
        'jakarta_aktif_id'   => $jakarta->id,
        'no_pl'              => $jakarta->id_pesan,
        'tgl_turun_pl'       => $jakarta->tgl_pesan,
        'nama_unit'          => $jakarta->nama_unit,
        'pengiriman'         => $pengiriman,
        'service_pengiriman' => $servicePengiriman,           // ← Diisi otomatis
        'nama_barang'        => $jakarta->pesanan,
        'tgl_bayar'          => $jakarta->payment_date,
        'jumlah_bayar'       => $jakarta->total ?? 0,
        'nama_stokis'        => $namaStokis,
        'tgl_estimasi'       => $jakarta->estimasi_persiapan,
        'estimasi_hari'      => $estimasiHari,
        'penyebut'           => $jakarta->nama_unit,
        'pengambil'          => $statusKirim === 'Diambil' ? 'Ambil Sendiri' : null,
        'ket'                => $jakarta->catatan,
        'order_weight' => $jakarta->berat ?? 0,
        // === TAMBAHKAN DUA BARIS INI ===
        'billing_last_name'  => $jakarta->billing_last_name ?? null,
        'billing_company'    => $jakarta->billing_company ?? null,
    ]);
    $this->createPicking($realisasi);
}
            $successCount++;
        }
    }

    $route = $request->input('redirect', 'order.jakarta-aktif');

return redirect()->route($route)
    ->with('success', "$successCount data berhasil dikunci dan dipindah ke Realisasi Aktif!");
}

private function createPicking(RealisasiAktif $realisasi)
{
    $jakarta = JakartaAktif::find($realisasi->jakarta_aktif_id);

    if (!$jakarta) {
        return;
    }

    $items = BimbashopOrder::where('order_id', $realisasi->no_pl)
                ->orderBy('item_sku')
                ->get();

    $picking = Picking::create([

    'jakarta_aktif_id'   => $realisasi->jakarta_aktif_id,

    'no_pl'              => $realisasi->no_pl,

    'payment_date'          => $realisasi->tgl_bayar,

    'waktu_estimasi_persiapan' => $jakarta->estimasi_persiapan
    ? Carbon::parse($jakarta->estimasi_persiapan)->toDateString()
    : now()->toDateString(),

    'jam_picking'        => now()->format('H:i:s'),

    'id_pesan'           => $realisasi->no_pl,

    'cabang'             => $jakarta->cabang ?? null,

    'vendor'             => $realisasi->nama_stokis,

    'nama_unit'          => $realisasi->nama_unit,

    'billing_last_name'  => $realisasi->billing_last_name,

    'billing_company'    => $realisasi->billing_company,

    'kirim'              => $jakarta->kirim,

    'no_telpon'          => $jakarta->no_telpon,

    'alamat_kirim'       => $jakarta->alamat_kirim,

    'kab_kota_provinsi'  => $jakarta->kab_kota_provinsi,

    'ekspedisi'          => $realisasi->pengiriman,

    'service_pengiriman' => $realisasi->service_pengiriman,

    'tracking_number'    => $jakarta->tracking_number,

    'pesanan'            => $realisasi->nama_barang,

    'jenis_bank'         => $jakarta->jenis_bank,

    'status_pembayaran'  => $jakarta->status_pembayaran,

    'harga'              => $jakarta->harga,

    'diskon'             => $jakarta->diskon ?? 0,

    'ongkir'             => $jakarta->ongkir,

    'fee_payment'        => $jakarta->fee_payment ?? 0,

    'total'              => $realisasi->jumlah_bayar,

    'berat'              => $realisasi->order_weight,

    'berat_bimbashop'    => $jakarta->berat,

    'berat_aktual'       => null,

    'total_item'         => $items->count(),

    'total_qty'          => $items->sum('item_qty'),

    'status'             => 'completed',

    'printed_at'         => now(),

    'created_by'         => Auth::id(),

    'catatan'            => 'Auto Generate',
]);

    foreach ($items as $item) {

        PickingItem::create([

            'picking_id' => $picking->id,

            'item_name' => trim(
                preg_replace('/-?JKT$/i', '', $item->item_name)
            ),

            'item_sku' => trim(
                preg_replace('/-?JKT$/i', '', $item->item_sku)
            ),

            'item_qty' => $item->item_qty,

            'qty_picked' => 0,

            'cek' => false,

        ]);
    }

    // Jika order tidak punya item
    if ($items->isEmpty()) {

        PickingItem::create([
            'picking_id' => $picking->id,
            'item_name'  => $realisasi->nama_barang,
            'item_sku'   => '-',
            'item_qty'   => 1,
            'qty_picked' => 0,
            'cek'        => false,
        ]);
    }
}
// ====================== MENU PRINT (Sudah Diproses) ======================
public function jakartaPrinted(Request $request)
{
    $query = RealisasiAktif::query();

    // Filter tetap sama...
    if ($request->filled('id_pesan')) {
        $query->where('no_pl', 'like', '%' . $request->id_pesan . '%');
    }
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_turun_pl', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tgl_turun_pl', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 20);
    $perPage = in_array($perPage, [10, 20, 50, 100, 200]) ? $perPage : 20;

    $data = $query
        ->latest('created_at')
        ->paginate($perPage)
        ->appends($request->query());

    // Generate nomor SEKALI dan simpan
    $docNumber = $this->generateRekapNumber();

    // Simpan ke data yang belum punya nomor
    if ($data->isNotEmpty()) {
        $idsToUpdate = $data->pluck('id')->toArray();
        
        DB::table('realisasi_aktif')
            ->whereIn('id', $idsToUpdate)
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $docNumber,
                'updated_at'   => now()
            ]);
    }

    return view('order.jakarta-printed', compact('data', 'docNumber'));
}
/**
 * Hapus data dari Realisasi Aktif (Jakarta Printed)
 */
public function deleteRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);   // ← Ganti ke RealisasiAktif

    $item->delete();

    return redirect()->route('order.jakarta-printed')
                     ->with('success', '✅ Data berhasil dihapus dari Realisasi Aktif!');
}


public function getModalData(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    $data = JakartaAktif::whereIn('id', $ids)
        ->select([
            'id', 
            'id_pesan', 
            'nama_unit', 
            'status_pembayaran', 
            'jenis_bank', 
            'pesanan',
            'status_kirim',
            'payment_date',
            'is_processed',      // ← TAMBAHKAN
            'processed_at'       // ← TAMBAHKAN (opsional)
        ])
        ->get()
        ->map(function ($item) {
            $vendor = $this->extractVendorFromSku($item->pesanan ?? '');

            return [
                'id'                  => $item->id,
                'invoice'             => $item->id_pesan ?? '-',
                'to_customer'         => $item->nama_unit ?? '-',
                'payment_date'        => $item->payment_date 
                                        ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i') 
                                        : '-',
                'payment_channel'     => $item->jenis_bank ?? '-',
                'status_pembayaran'   => $item->status_pembayaran ?? '-',
                'status_kirim'        => $item->status_kirim ?? 'Dikirim',
                'vendor'              => $vendor,
                'is_processed'        => (bool) $item->is_processed,   // ← TAMBAHKAN
                'processed_at'        => $item->processed_at 
                                        ? \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i') 
                                        : null,
            ];
        });

    return response()->json($data);
}

private function extractVendorFromSku($skuOrPesanan)
{
    if (empty($skuOrPesanan)) {
        return 'Stokis Jakarta Aktif';
    }

    // Mapping lengkap dari data yang kamu berikan
    $vendorMap = [
        'JKT'    => 'Stokis Jakarta Aktif',
        'JKTP'   => 'Stokis Jakarta Pasif',
        'LG'     => 'Stokis Logistik',
        '-LG'    => 'Stokis Logistik',
        
        'UA1'    => 'PUA1',
        'UA2'    => 'PUA2',
        'UA3'    => 'PUA3',
        'DPK1'   => 'Stokis Depok 1',
        'SRG1'   => 'Stokis Serang 1',
        'KWG1'   => 'Stokis Karawang 1',
        'BKS1'   => 'Stokis Bekasi 1',
        'BGR1'   => 'Stokis Bogor 1',
        'TNG1'   => 'Stokis Tangerang 1',
        'SNG'    => 'Stokis Subang',
        'BGRT'   => 'Stokis Bogor 2',
        'PWK'    => 'Stokis Purwakarta 1',
        'TNG2'   => 'Stokis Tangerang 2',
        'KNG'    => 'Stokis Kuningan 1',
        'IDM'    => 'Stokis Indramayu 1',
        'SKB1'   => 'Stokis Sukabumi 1',
        'SKB2'   => 'Stokis Sukabumi 2',
        'BDG1'   => 'Stokis Bandung 1',
        'BDG2'   => 'Stokis Bandung 2',
        'CIL1'   => 'Stokis Cilincing 1',
        'SRG2'   => 'Stokis Serang 2',
        'DPR1'   => 'Stokis Denpasar',
        'KWG2'   => 'Stokis Karawang 2',
        'BGR3'   => 'Stokis Bogor 3',
        'DLC'    => 'Stokis Intervio',
        'EBT'    => 'Stokis English biMBA Talk',
        'SMG'    => 'Stokis Semarang',
        'SBY'    => 'Stokis Surabaya',
        'YYK'    => 'Stokis Yogyakarta',
        'INV'    => 'Stokis Inventori',
        'SGN'    => 'Stokis Sragen',
        'YK1'    => 'Stokis Yogyakarta 1',
        'ENB'    => 'Stokis English',
        'RB1'    => 'Stokis Cirebon 1',
        'TNG3'   => 'Stokis Tangerang 3',
    ];

    $skuUpper = strtoupper(trim($skuOrPesanan));

    // Cek satu per satu (prioritas urutan penting)
    foreach ($vendorMap as $code => $name) {
        if (stripos($skuUpper, $code) !== false) {
            return $name;
        }
    }

    // Jika tidak ada kode yang cocok → default JKT
    return 'Stokis Jakarta Aktif';
}

public function printRealisasiPdf(Request $request)
{
    $query = RealisasiAktif::query();

    // Filter
    if ($request->filled('id_pesan')) {
        $query->where('no_pl', 'like', '%' . $request->id_pesan . '%');
    }
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_turun_pl', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tgl_turun_pl', '<=', $request->end_date);
    }

    // Ambil ID dulu
    $ids = (clone $query)->pluck('id');

    // Generate nomor
    $docNumber = $this->generateRekapNumber();

    if ($ids->isNotEmpty()) {

        DB::table('realisasi_aktif')
            ->whereIn('id', $ids)
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $docNumber,
                'updated_at' => now(),
            ]);

        if ($request->boolean('mark_printed')) {
            DB::table('realisasi_aktif')
                ->whereIn('id', $ids)
                ->whereNull('printed_at')
                ->update([
                    'printed_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    // BARU ambil data setelah update selesai
    $data = $query->latest('tgl_turun_pl')->get();

    $pdf = PDF::loadView('order.jakarta-printed-pdf', compact('data', 'docNumber'))
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
        ]);

    return $pdf->stream('Realisasi-Aktif-' . now()->format('d-m-Y_H-i') . '.pdf');
}


private function generateRekapNumber()
{
    // Ambil nomor tertinggi yang pernah ada (bukan hanya hari ini)
    $lastNumber = DB::table('realisasi_aktif')
                    ->whereNotNull('rekap_number')
                    ->max(DB::raw('CAST(SUBSTRING(rekap_number, 2) AS UNSIGNED)'));

    $next = ($lastNumber ?? 0) + 1;

    return '#' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

public function printSingleRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);

    if (!$item->printed_at) {
        $item->update(['printed_at' => now()]);
    }

    $data = collect([$item]); // agar view PDF tetap bisa pakai @foreach

    $pdf = PDF::loadView('order.jakarta-printed-pdf', compact('data'))
               ->setPaper('A5', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
                   'isRemoteEnabled' => true,
               ]);

    return $pdf->stream('Realisasi-' . $item->no_pl . '.pdf');
}

// === TAMBAHKAN METHOD INI DI DALAM CLASS OrderController ===
public function getFilteredIds(Request $request)
{
    $query = JakartaAktif::query();

    // ==========================
    // FILTER BERDASARKAN MENU
    // ==========================
    switch ($request->route) {

        case 'order.modul':

            $query->where('pesanan', 'not like', '%M159%')
                  ->where('pesanan', 'not like', '%STA%')
                  ->where('pesanan', 'not like', '%STPB%');

            break;

        case 'order.majalah':

            $query->where('pesanan', 'like', '%M159%');

            break;

        case 'order.sertifikat':

            $query->where(function ($q) {
                $q->where('pesanan', 'like', '%STA%')
                  ->orWhere('pesanan', 'like', '%STPB%');
            });

            break;

        // order.jakarta-aktif
        default:
            break;
    }

    // ==========================
    // FILTER YANG SUDAH ADA
    // ==========================

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('pesanan')) {
        $query->where('pesanan', 'like', '%' . $request->pesanan . '%');
    }

    $ids = $query
        ->where('is_processed', 0)
        ->pluck('id');

    return response()->json([
        'ids'   => $ids,
        'count' => $ids->count(),
    ]);
}
// Tambahkan method ini
public function markPickingPrinted($id)
{
    $item = RealisasiAktif::findOrFail($id);

    $item->update([
        'picking_printed_at' => now()
    ]);

    return response()->json([
        'success' => true
    ]);
}

public function exportJakartaAktif(Request $request)
{
    $filename = 'Jakarta_Aktif_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(
        new \App\Exports\JakartaAktifExport($request), 
        $filename
    );
}
/**
 * Print Picking List - Multi Item dari BimbashopOrder
 */
public function printPickingList($id)
{
    $main = RealisasiAktif::findOrFail($id);

    if (!$main->picking_printed_at) {
        $main->update([
            'picking_printed_at' => now()
        ]);
    }

    $items = BimbashopOrder::where('order_id', $main->no_pl)
            ->orderBy('item_sku')
            ->get()
            ->transform(function ($item) {

                // Hapus JKT pada SKU
                $item->item_sku = preg_replace('/-?JKT$/i', '', trim($item->item_sku));

                // Hapus JKT pada Nama Produk
                $item->item_name = preg_replace('/-?JKT$/i', '', trim($item->item_name));
                $item->item_name = preg_replace('/JKT$/i', '', trim($item->item_name));

                // Rapikan spasi
                $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name));

                return $item;
            });

    if ($items->isEmpty()) {
        $items = collect([
            (object)[
                'item_name' => $main->nama_barang ?? '-',
                'item_sku'  => '-',
                'item_qty'  => 1,
            ]
        ]);
    }

    return view('order.picking-list', [
        'item'              => $main,                    // RealisasiAktif
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name, // ← TAMBAHKAN INI
        'billing_company'    => $main->billing_company,   // ← tambahkan
    ]);
}


public function printPickingListPdf($id)
{
    $main = RealisasiAktif::findOrFail($id);

    // Tandai sudah dicetak
    if (!$main->picking_printed_at) {
        $main->update(['picking_printed_at' => now()]);
    }

    $items = BimbashopOrder::where('order_id', $main->no_pl)
            ->orderBy('item_sku')
            ->get()
            ->transform(function ($item) {

                // Hapus JKT pada SKU
                $item->item_sku = preg_replace('/-?JKT$/i', '', trim($item->item_sku));

                // Hapus JKT pada Nama Produk
                $item->item_name = preg_replace('/-?JKT$/i', '', trim($item->item_name));
                $item->item_name = preg_replace('/JKT$/i', '', trim($item->item_name));

                // Rapikan spasi
                $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name));

                return $item;
            });

    if ($items->isEmpty()) {
        $items = collect([
            (object)[
                'item_name' => $main->nama_barang ?? '-',
                'item_sku'  => '-',
                'item_qty'  => 1,
            ]
        ]);
    }

    $pdf = Pdf::loadView('order.picking-list-pdf', [
        'item'              => $main,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
    ]);

    $pdf->setPaper('A5', 'portrait');
    
    $pdf->setOptions([
        'margin-top'           => 8,
        'margin-right'         => 6,
        'margin-bottom'        => 18,     // diperbesar untuk page number
        'margin-left'          => 6,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled'         => true,   // ← INI YANG PENTING
    ]);

    $filename = 'Picking_List_' . ($main->no_pl ?? 'unknown') . '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->stream($filename);
}
/**
 * Print QC
 */
public function printQC(Request $request)
{
    $ids = explode(',', $request->get('ids', ''));
    
    $data = RealisasiAktif::whereIn('id', $ids)
                ->orderBy('no_pl')
                ->get();

    $docNumber = $this->generateRekapNumber();

    $pdf = PDF::loadView('order.print-qc', compact('data', 'docNumber'))
               ->setPaper('A4', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
               ]);

    return $pdf->stream('QC-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print Pemesanan (RA Picking)
 */
public function printPemesanan(Request $request)
{
    $ids = explode(',', $request->get('ids', ''));

    $data = RealisasiAktif::whereIn('id', $ids)
                ->orderBy('no_pl')
                ->get();

    $docNumber = $this->generateRekapNumber();

    $pdf = PDF::loadView('order.print-pemesanan', compact('data', 'docNumber'))
               ->setPaper('A4', 'landscape')   // Ubah ke landscape jika terlalu lebar
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
               ]);

    return $pdf->stream('Pemesanan-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print Packing
 */
public function printPacking(Request $request)
{
    $ids = explode(',', $request->get('ids', ''));

    $data = RealisasiAktif::whereIn('id', $ids)
                ->orderBy('no_pl')
                ->get();

    $docNumber = $this->generateRekapNumber();

    $pdf = PDF::loadView('order.print-packing', compact('data', 'docNumber'))
               ->setPaper('A4', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
               ]);

    return $pdf->stream('Packing-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print Distribusi / Ekspedisi
 */
public function printEkspedisi(Request $request)
{
    $ids = explode(',', $request->get('ids', ''));
    
    $data = RealisasiAktif::whereIn('id', $ids)
                ->orderBy('no_pl')
                ->get();

    $docNumber = $this->generateRekapNumber();

    $pdf = PDF::loadView('order.print-ekspedisi', compact('data', 'docNumber'))
               ->setPaper('A4', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
               ]);

    return $pdf->stream('Ekspedisi-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
}


public function modul(Request $request)
{
    $query = JakartaAktif::query()
        ->with([
            'casdana',
            'items.product'
        ]);

    // ==========================
    // HANYA ORDER YANG MEMILIKI ITEM MODUL
    // ==========================
    $query->whereHas('items', function ($q) {

        $q->where(function ($x) {

            // jika product sudah terhubung
            $x->whereHas('product', function ($p) {
                $p->where('kategori', 'Modul');
            });

            // fallback jika product_id masih null
            $x->orWhere('nama_produk', 'like', '%Modul%');
            $x->orWhere('label', 'like', '%Modul%');
            $x->orWhere('sku', 'like', '%MOD%');
        });

    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', "%{$request->id_pesan}%");
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', "%{$request->kirim}%");
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', "%{$request->nama_unit}%");
    }

    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }

    if ($request->filled('status_pesan')) {
        $query->where('status_pesan', $request->status_pesan);
    }

    if ($request->filled('validasi')) {
        $query->where('validasi', $request->validasi);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}

public function majalah(Request $request)
{
    $query = JakartaAktif::query();

    // ==========================
    // HANYA MAJALAH (M159)
    // ==========================
    $query->where('pesanan', 'like', '%M159%');

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }

    if ($request->filled('status_pesan')) {
        $query->where('status_pesan', $request->status_pesan);
    }

    if ($request->filled('validasi')) {
        $query->where('validasi', $request->validasi);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->with('casdana')
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}


public function sertifikat(Request $request)
{
    $query = JakartaAktif::query();

    // ==========================
    // HANYA SERTIFIKAT
    // STA dan STPB
    // ==========================
    $query->where(function ($q) {
        $q->where('pesanan', 'like', '%STA%')
          ->orWhere('pesanan', 'like', '%STPB%');
    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }

    if ($request->filled('status_pesan')) {
        $query->where('status_pesan', $request->status_pesan);
    }

    if ($request->filled('validasi')) {
        $query->where('validasi', $request->validasi);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->with('casdana')
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}


}