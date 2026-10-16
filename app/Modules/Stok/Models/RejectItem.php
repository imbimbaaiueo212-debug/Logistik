<?php

namespace App\Modules\Stok\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;
use App\Modules\Master\Models\Warehouse;

class RejectItem extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty',
        'reason',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}