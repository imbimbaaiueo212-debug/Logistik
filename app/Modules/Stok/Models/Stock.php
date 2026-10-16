<?php

namespace App\Modules\Stok\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;
use App\Modules\Master\Models\Warehouse;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qty'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getLabelAttribute()
    {
        return ($this->product->name ?? '-') . ' - ' . ($this->warehouse->code ?? '-');
    }
}