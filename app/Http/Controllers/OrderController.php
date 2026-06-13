<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Imports\JakartaAktifImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return view('order.index');
    }

public function jakartaAktif(Request $request)
{
    $query = JakartaAktif::query();

    // === FILTER ===
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

    // === PAGINATION ===
    $perPage = $request->get('per_page', 5);
    $perPage = in_array($perPage, [5, 10, 20, 50, 100, 200, 500]) ? $perPage : 5;

    $data = $query
    ->with(['casdana' => function ($q) {
        $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer');
    }])
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


/**
 * Bulk Action untuk Jakarta Aktif (Per Item)
 */
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

        $statusKirim  = $item['status_kirim'] ?? null;
        $jasaKurir    = $item['jasa_kurir'] ?? null;
        $serviceKurir = $item['service_kurir'] ?? null;
        $catatan      = $item['catatan'] ?? null;

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
            $newNote = "\n\nDi proses bulk pada " . $now->format('d/m/Y H:i:s') . ": " . trim($catatan);
            $setClauses[] = "catatan = CONCAT(COALESCE(catatan, ''), ?)";
            $bindings[] = $newNote;
        }

        $sql = "UPDATE jakarta_aktif 
                SET " . implode(', ', $setClauses) . "
                WHERE id = ?";

        $updated = DB::update($sql, array_merge($bindings, [$id]));

        if ($updated) $successCount++;
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "$successCount data berhasil diproses dan dikunci.");
}


}