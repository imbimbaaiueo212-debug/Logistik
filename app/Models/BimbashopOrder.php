<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BimbashopOrder extends Model
{
    use HasFactory;

    protected $table = 'bimbashop_orders';

    protected $fillable = [
        'order_id', 'order_date', 'status', 'order_total', 'ship_total',
        'discount_total', 'refunded_total', 'payment_method', 'order_weight',
        'item_sku', 'item_name', 'item_price', 'item_qty',
        'billing_first_name', 'billing_last_name', 'billing_company',
        'shipping_first_name', 'shipping_last_name',
        'shipping_address_1', 'shipping_address_2', 'shipping_city',
        'partial_pay_wallet_id'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'order_total' => 'decimal:2',
        'ship_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'refunded_total' => 'decimal:2',
        'item_price' => 'decimal:2',
        'order_weight' => 'decimal:2',
    ];
}