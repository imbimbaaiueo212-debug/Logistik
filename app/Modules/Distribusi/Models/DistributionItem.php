<?php

namespace App\Modules\Distribusi\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;

class DistributionItem extends Model
{
    protected $fillable = [
        'distribution_id',
        'product_id',
        'qty'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}