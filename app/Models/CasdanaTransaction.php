<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasdanaTransaction extends Model
{
    use HasFactory;

    protected $table = 'casdana_transactions';

    protected $fillable = [
        'invoice_number',
        'merchant',
        'customer',
        'status',
        'payment_date',
        'payment_channel',
        'payment_code',
        'amount',
        'raw_data'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];
}