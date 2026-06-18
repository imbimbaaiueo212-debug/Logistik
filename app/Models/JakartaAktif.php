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
    
}