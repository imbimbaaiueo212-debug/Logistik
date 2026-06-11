<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Imports\JakartaAktifImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
 * Hanya JKT murni (dikecualikan JKTP dan semua stokis lain)
 */
public function syncJktFromBimbashop()
{
    $count = 0;

    // Daftar SKU yang TIDAK boleh masuk
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

        // === CARI DATA CASDANA ===
        $casdana = CasdanaTransaction::where('invoice_number', $bimba->order_id)
                    ->orWhere('invoice_number', 'like', '%' . $bimba->order_id . '%')
                    ->latest('id')
                    ->first();

        // === NAMA UNIT (tetap gabungan first + last + company) ===
        $parts = [];
        if (!empty($bimba->billing_first_name)) $parts[] = $bimba->billing_first_name;
        if (!empty($bimba->billing_last_name))  $parts[] = $bimba->billing_last_name;
        if (!empty($bimba->billing_company))    $parts[] = $bimba->billing_company;

        $namaUnit = !empty($parts) 
            ? implode(' ', $parts) 
            : ($bimba->item_name ?? ($casdana->customer ?? '-'));

        // === DATA KIRIM ===
        $kirim = trim(
            ($bimba->shipping_address_1 ?? '') .
            (!empty($bimba->shipping_address_2 ?? '') ? ', ' . $bimba->shipping_address_2 : '') .
            (!empty($bimba->shipping_city ?? '') ? ', ' . $bimba->shipping_city : '')
        );

        if (empty($kirim)) {
            $kirim = $bimba->item_name ?? $casdana->customer ?? '-';
        }

        // === STATUS PEMBAYARAN ===
        $statusPembayaran = null;
        if ($casdana) {
            $statusCasdana = strtoupper(trim($casdana->status ?? ''));
            if (in_array($statusCasdana, ['SUCCESS', 'SETTLED'])) {
                $statusPembayaran = $statusCasdana;
            }
        }

        // === DATA UTAMA ===
        $data = [
            'tgl_input'         => now()->format('Y-m-d'),
            'tgl_pesan'         => $bimba->order_date,
            
            'kirim'             => $kirim,
            'no_telpon'         => $bimba->shipping_phone ?? null,
            'alamat_kirim'      => $bimba->shipping_address_1 ?? null,
            'kab_kota_provinsi' => $bimba->shipping_city ?? null,
            
            'ekspedisi'         => null,
            'ongkir'            => $bimba->ship_total ?? 0,
            
            'nama_unit'         => $namaUnit,
            'pesanan'           => $bimba->item_name ?? null,
            
            'harga'             => $bimba->item_price ?? 0,
            'berat'             => $bimba->order_weight ?? 0,
            'total'             => $casdana->amount ?? $bimba->order_total ?? 0,
            
            'jenis_bank'        => $casdana->payment_channel ?? $bimba->payment_method,
            
            'status_pembayaran' => $statusPembayaran,
            'status_pesan'      => $bimba->status,
            
            'id_pesan'          => $bimba->order_id,
            
            'validasi'          => 'Pending',
            'status'            => 'aktif',
            
            'payment_date'      => $casdana->payment_date ?? null,
            'amount'            => $casdana->amount ?? 0,

            // === CABANG DIAMBIL LANGSUNG DARI billing_last_name ===
            'billing_last_name' => $bimba->billing_last_name ?? null,

            'catatan'           => $casdana 
                ? "Synced from Casdana | Status: {$casdana->status} | Channel: {$casdana->payment_channel}"
                : "From Bimbashop | Status: {$bimba->status}",
        ];

        JakartaAktif::create($data);
        $count++;
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "✅ Berhasil sync {$count} data JKT murni!");
}

    public function unitAktif() { return view('order.unit-aktif'); }
    public function unitPasif() { return view('order.unit-pasif'); }
}