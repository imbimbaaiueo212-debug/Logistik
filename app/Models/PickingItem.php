<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickingItem extends Model
{
    protected $table = 'picking_items';

    protected $fillable = [
        'picking_id',
        'product_id',
        'item_name',
        'item_sku',
        'item_qty',
        'qty_picked',        // jumlah yang sudah diambil
        'cek',               // checkbox (0/1)
    ];

    protected $casts = [
        'item_qty'   => 'integer',
        'qty_picked' => 'integer',
        'cek'        => 'boolean',
    ];

    public function picking()
    {
        return $this->belongsTo(Picking::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}