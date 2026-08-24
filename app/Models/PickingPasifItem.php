<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingPasifItem extends Model
{
    protected $table = 'picking_pasif_item';

    protected $fillable = [
        'picking_pasif_id',
        'product_id',
        'item_name',
        'item_sku',
        'item_qty',
        'qty_picked',
        'cek',
    ];

    protected $casts = [
        'cek'        => 'boolean',
        'item_qty'   => 'integer',
        'qty_picked' => 'integer',
    ];

    // ====================== RELATIONS ======================

    public function pickingPasif(): BelongsTo
    {
        return $this->belongsTo(PickingPasif::class, 'picking_pasif_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}