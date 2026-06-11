<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        
        // === SERVICE KURIR ===
        'service_pengiriman',      // Contoh: J&T REG, J&T YES, JNE REG, SICEPAT, dll
        'tracking_number',         // No Resi
        
        // === MANUAL DISTRIBUSI ===
        'distribusi_manual',       // Yes / No
        'nama_distributor',        // Nama orang / vendor yang mendistribusikan manual
        'tgl_distribusi',          // Tanggal distribusi manual
        'status_distribusi',       // Sudah Didistribusikan / Belum / Gagal
        
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
        'catatan'
    ];

    protected $casts = [
        'tgl_input'      => 'date',
        'tgl_pesan'      => 'datetime',
        'tgl_distribusi' => 'date',        // Tambahan cast
        'berat'          => 'decimal:2',
        'harga'          => 'decimal:2',
        'diskon'         => 'decimal:2',
        'fee_payment'    => 'decimal:2',
        'total'          => 'decimal:2',
        'ongkir'         => 'decimal:2',
    ];

    public function casdana()
{
    return $this->hasOne(CasdanaTransaction::class, 'invoice_number', 'id_pesan');
}

public function getPaymentDateAttribute()
{
    // Coba ambil dari relasi dulu
    if ($this->relationLoaded('casdana') && $this->casdana?->payment_date) {
        return $this->casdana->payment_date;
    }

    // Fallback query langsung (paling reliable)
    $cas = CasdanaTransaction::where('invoice_number', $this->id_pesan)
            ->orWhere('invoice_number', 'IDB' . $this->id_pesan)
            ->orWhere('invoice_number', 'like', "%{$this->id_pesan}%")
            ->first();

    return $cas?->payment_date;
}

}