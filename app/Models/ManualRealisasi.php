<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualRealisasi extends Model
{
    protected $table = 'manual_realisasi';

    protected $fillable = [
        'manual_order_id',
        'rekap_number',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'billing_last_name',
        'billing_company',
        'pengiriman',
        'service_pengiriman',
        'nama_barang',
        'product_id',
        'product_ids',
        'kategori_order',
        'tgl_bayar',
        'jumlah_bayar',
        'order_weight',
        'nama_stokis',
        'tgl_estimasi',
        'estimasi_hari',
        'penyebut',
        'pengambil',
        'ket',
        'is_processed',
        'printed_at',
        'picking_printed_at',
        'grup',
    ];

    protected $casts = [
        'tgl_turun_pl'       => 'datetime',
        'tgl_bayar'          => 'datetime',
        'tgl_estimasi'       => 'datetime',
        'printed_at'         => 'datetime',
        'picking_printed_at' => 'datetime',
        'is_processed'       => 'boolean',
        'product_ids'        => 'array',
        'jumlah_bayar'       => 'decimal:2',
        'order_weight'       => 'decimal:2',
    ];

    public function manualOrder()
    {
        return $this->belongsTo(ManualOrder::class);
    }

    public function picking()
    {
        return $this->hasOne(ManualPicking::class, 'manual_realisasi_id');
    }
}