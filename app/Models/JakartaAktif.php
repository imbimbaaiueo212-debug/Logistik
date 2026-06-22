<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JakartaAktif extends Model
{
    protected $table = 'jakarta_aktif';

    protected $fillable = [
        'tgl_input', 
        'tgl_pesan', 
        'kirim', 
        'no_telpon', 
        'alamat_kirim',
        'kab_kota_provinsi', 
        'ekspedisi', 
        'ongkir', 
        'service_pengiriman',
        'tracking_number',
        'status_kirim',
        'estimasi_print_pl',
        'estimasi_persiapan',
        'billing_last_name',
        'billing_company',
        'distribusi_manual',
        'nama_distributor',
        'tgl_distribusi',
        'status_distribusi',
        'validasi', 
        'jenis_bank', 
        'status_pembayaran',
        'id_pesan', 
        'cabang', 
        'nama_unit', 
        'vendor', 
        'pesanan',
        'status_pesan', 
        'berat', 
        'harga', 
        'diskon', 
        'fee_payment',
        'total', 
        'status', 
        'sales', 
        'catatan',
        'payment_date',           // ← TAMBAHKAN INI
        'printed_at',
    ];

    protected $casts = [
        'tgl_input'      => 'date',
        'tgl_pesan'      => 'datetime',
        'tgl_distribusi' => 'date',
        'payment_date'   => 'datetime',     // ← TAMBAHKAN INI
        'berat'          => 'decimal:2',
        'harga'          => 'decimal:2',
        'diskon'         => 'decimal:2',
        'fee_payment'    => 'decimal:2',
        'total'          => 'decimal:2',
        'ongkir'         => 'decimal:2',
    ];

    // Relasi tetap dipertahankan
    public function casdana()
    {
        return $this->hasOne(CasdanaTransaction::class, 'invoice_number', 'id_pesan');
    }

    public function realisasi()
    {
        return $this->hasOne(RealisasiAktif::class, 'jakarta_aktif_id');
    }

    /**
     * Accessor payment_date (prioritas ke data lokal dulu)
     */
    public function getPaymentDateAttribute($value)
{
    // Jika ada di kolom lokal → pakai itu (prioritas utama)
    if ($value !== null) {
        return $value;
    }

    // Fallback ke relasi Casdana
    if ($this->relationLoaded('casdana') && $this->casdana?->payment_date) {
        return $this->casdana->payment_date;
    }

    // Query langsung
    $cas = CasdanaTransaction::where('invoice_number', $this->id_pesan)
            ->orWhere('invoice_number', 'IDB' . $this->id_pesan)
            ->orWhere('invoice_number', 'like', "%{$this->id_pesan}%")
            ->first();

    return $cas?->payment_date;
}

    /**
     * Optional: Mutator untuk otomatis sync dari Casdana
     */
    public function setPaymentDateAttribute($value)
    {
        $this->attributes['payment_date'] = $value;
    }

    // Tambahkan di dalam class JakartaAktif

/**
 * Accessor untuk Nama Stokis (prioritas ke kolom jika ada, fallback ke logic SKU)
 */
public function getNamaStokisAttribute()
{
    // Jika sudah ada di kolom nama_stokis (setelah sync/update)
    if (!empty($this->attributes['nama_stokis'] ?? $this->nama_stokis)) {
        return $this->attributes['nama_stokis'] ?? $this->nama_stokis;
    }

    // Gunakan logic yang sudah bagus di controller
    return $this->extractVendorFromSku($this->pesanan ?? '');
}

/**
 * Method extractVendorFromSku dipindahkan ke Model (supaya reusable)
 */
private function extractVendorFromSku($skuOrPesanan)
{
    if (empty($skuOrPesanan)) {
        return 'Stokis Jakarta';
    }

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

    foreach ($vendorMap as $code => $name) {
        if (stripos($skuUpper, $code) !== false) {
            return $name;
        }
    }

    return 'Stokis Jakarta'; // default
}
    
    
}