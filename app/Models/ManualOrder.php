<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualOrder extends Model
{
    protected $table = 'manual_orders'; // pastikan nama tabel benar

    protected $fillable = [
        'order_id',
        'order_date',
        'item_sku',
        'customer_name',
        'phone',
        'product_sku',
        'product_name',
        'qty',
        'price',
        'total',
        'address',
        'payment_method',
        'status',
        'notes',
        'item_name',
        'item_price',
        'item_qty',
        'order_total',
        'ship_total',
        'order_weight',
        'discount_total',
        'refunded_total',
        'billing_first_name',
        'billing_last_name',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'source',
        'order_id',
        'no_cabang',
        'mitra_pengelolaan',
        'bimba_order_id',
        'bimba_order_date',
        'pesanan_majalah_id',
        'pesanan_majalah_type',
        'pesanan_majalah_unit_id',
        'is_synced_bimba',
        'status_kirim',
        'ekspedisi',
        'service_pengiriman',
        'is_processed',
        'processed_at',
        'payment_date',
        'estimasi_print_pl',
        'estimasi_persiapan',
        'catatan',
        'notes',
        'grup',
        'no_ps',
        ];

    protected $casts = [
        'order_date' => 'date',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'item_price' => 'decimal:2',
        'order_total' => 'decimal:2',
        'ship_total' => 'decimal:2',
        'order_weight' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'refunded_total' => 'decimal:2',
        'bimba_order_date' => 'datetime',
        'is_synced_bimba'  => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_id)) {
                $lastId = self::max('id') ?? 0;
                $order->order_id = str_pad($lastId + 1, 8, '0', STR_PAD_LEFT);
            }
        });
    }
    public function realisasi()
{
    return $this->hasMany(ManualRealisasi::class);
}
}