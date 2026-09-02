<?php
// app/Models/ManualSertifikatOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualSertifikatOrder extends Model
{
    protected $table = 'manual_sertifikat_orders';

    // Semua kolom boleh diisi (sama seperti modul)
    protected $guarded = [];

    protected $casts = [
        'order_date'         => 'datetime',
        'payment_date'       => 'datetime',
        'processed_at'       => 'datetime',
        'estimasi_print_pl'  => 'datetime',
        'estimasi_persiapan' => 'datetime',
        'is_processed'       => 'boolean',
        'price'              => 'decimal:2',
        'total'              => 'decimal:2',
        'ship_total'         => 'decimal:2',
        'discount_total'     => 'decimal:2',
        'refunded_total'     => 'decimal:2',
    ];
}