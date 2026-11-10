<?php

namespace App\Modules\OrderManual\Models;

use Illuminate\Database\Eloquent\Model;

class ManualCetakOrder extends Model
{
    protected $table = 'manual_cetak_orders';

    protected $guarded = [];

    protected $casts = [
        'order_date'          => 'datetime',
        'processed_at'        => 'datetime',
        'synced_penjualan_at' => 'datetime',
        'is_processed'        => 'boolean',
        'price'               => 'decimal:2',
        'total'               => 'decimal:2',
    ];

    public const JENIS = ['kalender', 'majalah'];
}
