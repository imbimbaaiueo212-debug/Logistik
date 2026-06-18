<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Models\RealisasiAktif;
use App\Imports\JakartaAktifImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
/**
 * Sync dari Bimbashop + Casdana 
 * Hanya JKT murni
 */
public function syncJktFromBimbashop()
{
    $count = 0;

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
                        ->get();

    foreach ($bimbashopOrders as $bimba) {
        if (JakartaAktif::where('id_pesan', $bimba->order_id)->exists()) {
            continue;
        }

        $casdana = CasdanaTransaction::where('invoice_number', $bimba->order_id)
                    ->orWhere('invoice_number', 'like', '%' . $bimba->order_id . '%')
                    ->latest('id')
                    ->first();

        if (!$casdana) continue;

        $statusCasdana = strtoupper(trim($casdana->status ?? ''));
        if (!in_array($statusCasdana, ['SUCCESS', 'SETTLED'])) {
            continue;
        }

        // === HITUNG ESTIMASI ===
        $paymentDate = $casdana->payment_date;
        $estimasiPrintPl = null;
        $estimasiPersiapan = null;

        if ($paymentDate) {
            $payment = \Carbon\Carbon::parse($paymentDate);
            $estimasiPrintPl   = $payment->copy()->addHours(24);
            $estimasiPersiapan = $payment->copy()->addHours(72);
        }

        // === NAMA UNIT ===
        $parts = [];
        if (!empty($bimba->billing_first_name)) $parts[] = $bimba->billing_first_name;
        if (!empty($bimba->billing_last_name))  $parts[] = $bimba->billing_last_name;
        if (!empty($bimba->billing_company))    $parts[] = $bimba->billing_company;

        $namaUnit = !empty($parts) 
            ? implode(' ', $parts) 
            : ($bimba->item_name ?? ($casdana->customer ?? '-'));

        // === ALAMAT KIRIM ===
        $kirim = trim(
            ($bimba->shipping_address_1 ?? '') .
            (!empty($bimba->shipping_address_2 ?? '') ? ', ' . $bimba->shipping_address_2 : '') .
            (!empty($bimba->shipping_city ?? '') ? ', ' . $bimba->shipping_city : '')
        );

        if (empty($kirim)) {
            $kirim = $bimba->item_name ?? $casdana->customer ?? '-';
        }

        $ongkir = (int) ($bimba->ship_total ?? 0);
        $statusKirim = ($ongkir > 0) ? 'Dikirim' : 'Diambil';

        $rawPesanan = trim($bimba->item_sku ?? $bimba->item_name ?? '');
        $pesanan = str_ireplace(['JKT', 'JKT-', '-JKT'], '', $rawPesanan);
        $pesanan = preg_replace('/\s+/', ' ', $pesanan);
        $pesanan = trim($pesanan, ' -');
        if (empty($pesanan)) $pesanan = 'STPB';

        // === DATA UTAMA ===
        $data = [
            'tgl_input'          => now()->format('Y-m-d'),
            'tgl_pesan'          => $bimba->order_date,
            
            'kirim'              => $kirim,
            'no_telpon'          => $bimba->shipping_phone ?? null,
            'alamat_kirim'       => $bimba->shipping_address_1 ?? null,
            'kab_kota_provinsi'  => $bimba->shipping_city ?? null,
            
            'ekspedisi'          => null,
            'ongkir'             => $ongkir,
            
            'nama_unit'          => $namaUnit,
            'pesanan'            => $pesanan,
            
            'harga'              => $bimba->item_price ?? 0,
            'berat'              => $bimba->order_weight ?? 0,
            'total'              => $casdana->amount ?? $bimba->order_total ?? 0,
            
            'jenis_bank'         => $casdana->payment_channel ?? $bimba->payment_method,
            
            'status_pembayaran'  => $statusCasdana,
            'status_pesan'       => $bimba->status,
            
            'id_pesan'           => $bimba->order_id,
            
            'validasi'           => null,
            'status'             => 'aktif',
            
            'payment_date'       => $paymentDate,
            'amount'             => $casdana->amount ?? 0,

            'billing_last_name'  => $bimba->billing_last_name ?? null,
            'status_kirim'       => $statusKirim,

            // === ESTIMASI BARU ===
            'estimasi_print_pl'  => $estimasiPrintPl,
            'estimasi_persiapan' => $estimasiPersiapan,

            'catatan'            => "Synced from Casdana | Status: {$casdana->status} | Channel: {$casdana->payment_channel}",
        ];

        JakartaAktif::create($data);
        $count++;
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "✅ Berhasil sync {$count} data JKT murni!");
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
        'alamat_pengiriman'  => 'nullable|string',           // ini input dari form
        'service_pengiriman' => 'nullable|string|max:100',
        'ekspedisi'          => 'nullable|string|max:100',
        'status_kirim'       => 'nullable|in:Dikirim,Diambil',
        'status_pembayaran'  => 'nullable|string|max:50',
        'validasi'           => 'nullable|string|max:50',
    ]);

    $item->update([
        'nama_unit'          => $request->nama_unit,
        'billing_last_name'  => $request->billing_last_name,
        'pesanan'            => $request->pesanan,
        'kirim'              => $request->alamat_pengiriman ?? $item->kirim,   // ← Update kolom yang ada
        'service_pengiriman' => $request->service_pengiriman,
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

        $jakarta = JakartaAktif::find($id);
        if (!$jakarta) continue;

        $statusKirim  = $item['status_kirim'] ?? $jakarta->status_kirim;
        $jasaKurir    = $item['jasa_kurir'] ?? $jakarta->ekspedisi;
        $serviceKurir = $item['service_kurir'] ?? $jakarta->service_pengiriman;
        $alamatBaru   = $item['kirim'] ?? $jakarta->kirim;           // ← Pakai kolom 'kirim'
        $catatan      = $item['catatan'] ?? null;

        // === UPDATE PAKAI RAW SQL (Aman & Cepat) ===
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
        if ($alamatBaru) {
            $setClauses[] = "kirim = ?";           // ← Hanya kolom yang ada
            $bindings[] = $alamatBaru;
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

        if ($updated > 0) {
            // === MASUKKAN KE REALISASI AKTIF ===
            if (!RealisasiAktif::where('jakarta_aktif_id', $jakarta->id)->exists()) {

                $estimasiHari = null;
                if ($jakarta->payment_date && $jakarta->estimasi_persiapan) {
                    $payment = \Carbon\Carbon::parse($jakarta->payment_date);
                    $persiapan = \Carbon\Carbon::parse($jakarta->estimasi_persiapan);
                    $estimasiHari = $payment->diffInDays($persiapan);
                }

                $namaStokis = $this->extractVendorFromSku($jakarta->pesanan ?? '');

                $pengiriman = $jasaKurir ?: ($jakarta->ekspedisi ?? ($statusKirim === 'Diambil' ? 'Ambil Sendiri' : '-'));

                RealisasiAktif::create([
                    'jakarta_aktif_id' => $jakarta->id,
                    'no_pl'            => $jakarta->id_pesan,
                    'tgl_turun_pl'     => $jakarta->tgl_pesan,
                    'nama_unit'        => $jakarta->nama_unit,
                    'pengiriman'       => $pengiriman,
                    'nama_barang'      => $jakarta->pesanan,
                    'tgl_bayar'        => $jakarta->tgl_pesan,
                    'jumlah_bayar'     => $jakarta->total ?? 0,
                    'nama_stokis'      => $namaStokis,
                    'tgl_estimasi'     => $jakarta->estimasi_persiapan,
                    'estimasi_hari'    => $estimasiHari,
                    'penyebut'         => $jakarta->nama_unit,
                    'pengambil'        => $statusKirim === 'Diambil' ? 'Ambil Sendiri' : null,
                    'ket'              => $jakarta->catatan,
                ]);
            }
            $successCount++;
        }
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "$successCount data berhasil dikunci dan dipindah ke Realisasi Aktif!");
}
// ====================== MENU PRINT (Sudah Diproses) ======================
public function jakartaPrinted(Request $request)
{
    $query = RealisasiAktif::query();   // ← Ganti ke RealisasiAktif

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

    $perPage = $request->get('per_page', 20);
    $perPage = in_array($perPage, [10, 20, 50, 100, 200]) ? $perPage : 20;

    $data = $query
        ->latest('created_at')           // atau created_at / tgl_turun_pl
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-printed', compact('data'));
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
            'kirim',                    // ← TAMBAHKAN INI
            'status_pembayaran', 
            'jenis_bank', 
            'pesanan',
            'status_kirim',
            'payment_date',
            'is_processed',
            'processed_at'
        ])
        ->get()
        ->map(function ($item) {
            $vendor = $this->extractVendorFromSku($item->pesanan ?? '');

            return [
                'id'                  => $item->id,
                'invoice'             => $item->id_pesan ?? '-',
                'to_customer'         => $item->nama_unit ?? '-',
                'kirim'               => $item->kirim ?? '-',           // ← TAMBAHKAN INI
                'payment_date'        => $item->payment_date 
                                        ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i') 
                                        : '-',
                'payment_channel'     => $item->jenis_bank ?? '-',
                'status_pembayaran'   => $item->status_pembayaran ?? '-',
                'status_kirim'        => $item->status_kirim ?? 'Dikirim',
                'vendor'              => $vendor,
                'is_processed'        => (bool) $item->is_processed,
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
        return 'Stokis Jakarta';
    }

    // Mapping lengkap dari data yang kamu berikan
    $vendorMap = [
        'JKT'    => 'Stokis Jakarta',
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
    return 'Stokis Jakarta';
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

    $data = $query->latest('tgl_turun_pl')->get();

    // === HANYA update printed_at jika benar-benar mau print ===
    if ($request->has('mark_printed') && $request->mark_printed == 'true') {
        $ids = $data->pluck('id')->filter();
        if ($ids->isNotEmpty()) {
            RealisasiAktif::whereIn('id', $ids)
                ->whereNull('printed_at')
                ->update(['printed_at' => now()]);
        }
    }

    $pdf = PDF::loadView('order.jakarta-printed-pdf', compact('data'))
               ->setPaper('A5', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
                   'isRemoteEnabled' => true,
                   'margin-top'    => 10,
                   'margin-right'  => 10,
                   'margin-bottom' => 10,
                   'margin-left'   => 10,
               ]);

    return $pdf->stream('Realisasi-Aktif-' . now()->format('d-m-Y_H-i') . '.pdf');
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

    $ids = $query->where('is_processed', 0)
                 ->pluck('id');

    return response()->json([
        'ids' => $ids,
        'count' => $ids->count()
    ]);
}
/**
 * Tandai SEMUA data sebagai sudah dicetak (dipanggil via AJAX)
 */
public function markAllAsPrinted(Request $request)
{
    $updated = RealisasiAktif::whereNull('printed_at')
                ->update(['printed_at' => now()]);

    return response()->json([
        'success' => true,
        'message' => "$updated data berhasil ditandai sebagai sudah dicetak.",
        'count'   => $updated
    ]);
}
}