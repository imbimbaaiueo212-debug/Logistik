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

        if ($request->filled('id_pesan')) {
            $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
        }
        if ($request->filled('kirim')) {
            $query->where('kirim', 'like', '%' . $request->kirim . '%');
        }
        if ($request->filled('nama_unit')) {
            $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
        }

        $data = $query->latest()->paginate(20)->appends($request->query());

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
    $count = 0;

    // Daftar SKU yang TIDAK boleh masuk ke Jakarta Aktif
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
        // Skip jika sudah ada
        if (JakartaAktif::where('id_pesan', $bimba->order_id)->exists()) {
            continue;
        }

        $data = [
            'tgl_input'         => now()->format('Y-m-d'),
            'tgl_pesan'         => $bimba->order_date,
            
            // Data Penerima / Kirim
            'kirim'             => trim(($bimba->shipping_first_name ?? '') . ' ' . ($bimba->shipping_last_name ?? '')),
            'no_telpon'         => $bimba->shipping_phone ?? null,
            'alamat_kirim'      => $bimba->shipping_address_1,
            'kab_kota_provinsi' => $bimba->shipping_city,
            
            // Ekspedisi & Ongkir
            'ekspedisi'         => 'J&T',
            'ongkir'            => $bimba->ship_total ?? 0,
            
            // Unit / Produk
            'nama_unit'         => $bimba->item_name,           // ← Ini yang kamu maksud
            'pesanan'           => $bimba->item_name,
            
            // Harga & Total
            'harga'             => $bimba->item_price ?? 0,
            'berat'             => $bimba->order_weight ?? 0,
            'total'             => $bimba->order_total ?? 0,
            
            // Pembayaran
            'jenis_bank'        => $bimba->payment_method,
            'status_pembayaran' => $bimba->status == 'completed' ? 'Lunas' : 'Pending',
            'id_pesan'          => $bimba->order_id,
            
            'validasi'          => 'Pending',
            'status'            => 'aktif',
        ];

        // Ambil data tambahan dari Casdana
        $casdana = CasdanaTransaction::where('invoice_number', $bimba->order_id)
                    ->orWhere('invoice_number', 'like', '%' . $bimba->order_id . '%')
                    ->first();

        if ($casdana) {
            $data['status_pembayaran'] = $casdana->status == 'success' ? 'Lunas' : 'Pending';
            $data['jenis_bank']        = $casdana->merchant ?? $data['jenis_bank'];
            $data['payment_date']      = $casdana->payment_date ?? null;   // ← Ditambahkan
            $data['amount']            = $casdana->amount ?? 0;           // ← Ditambahkan
            $data['catatan']           = "Synced from Casdana: " . ($casdana->customer ?? '');
        }

        JakartaAktif::create($data);
        $count++;
    }

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', "✅ Berhasil sync {$count} data JKT murni!");
}

    public function unitAktif() { return view('order.unit-aktif'); }
    public function unitPasif() { return view('order.unit-pasif'); }
}