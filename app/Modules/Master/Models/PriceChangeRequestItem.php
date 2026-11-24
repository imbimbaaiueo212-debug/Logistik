<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;

class PriceChangeRequestItem extends Model
{
    protected $table = 'price_change_request_items';

    protected $fillable = [
        'request_id', 'product_id',
        'harga_beli_lama', 'harga_beli_baru', 'harga_jual_lama', 'harga_jual_baru',
    ];

    public function request() { return $this->belongsTo(PriceChangeRequest::class, 'request_id'); }
    public function product() { return $this->belongsTo(Product::class, 'product_id'); }
}
