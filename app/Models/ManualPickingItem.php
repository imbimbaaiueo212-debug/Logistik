<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPickingItem extends Model
{
    protected $table = 'manual_picking_items';

    protected $fillable = [
        'manual_picking_id',
        'product_id',
        'item_name',
        'item_sku',
        'item_qty',
        'qty_picked',
        'cek',
        'no_ps',
    ];

    protected $casts = [
        'cek' => 'boolean',
    ];

    public function picking()
    {
        return $this->belongsTo(ManualPicking::class, 'manual_picking_id');
    }
}