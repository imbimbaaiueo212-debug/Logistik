<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisasiPasif extends Model
{
    protected $table = 'realisasi_pasif';

    protected $fillable = [
        'jakarta_pasif_id',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'pengiriman',
        'service_pengiriman',
        'nama_barang',
        'kategori_order',
        'product_id',
        'product_ids',
        'tgl_bayar',
        'jumlah_bayar',
        'nama_stokis',
        'tgl_estimasi',
        'estimasi_hari',
        'penyebut',
        'pengambil',
        'ket',
        'order_weight',
        'billing_last_name',
        'billing_company',
        'rekap_number',
        'printed_at',
        'picking_printed_at',
    ];

    protected $casts = [
        'tgl_turun_pl'       => 'datetime',
        'tgl_bayar'          => 'datetime',
        'tgl_estimasi'       => 'datetime',
        'printed_at'         => 'datetime',
        'picking_printed_at' => 'datetime',
        'product_ids'        => 'array',
        'jumlah_bayar'       => 'decimal:2',
        'order_weight'       => 'decimal:2',
    ];

    public function jakartaPasif()
    {
        return $this->belongsTo(JakartaPasif::class, 'jakarta_pasif_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pickingPasif()
    {
        return $this->hasOne(PickingPasif::class, 'realisasi_pasif_id');
    }
    // Di model RealisasiPasif

    public function getPickingPrintedAtAttribute()
    {
        return $this->pickingPasif?->printed_at;
    }

// Kalau sudah ada kolom printed_at di realisasi_pasif, biarkan saja
}