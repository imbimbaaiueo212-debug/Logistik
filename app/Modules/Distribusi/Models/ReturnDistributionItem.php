<?php

namespace App\Modules\Distribusi\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;

class ReturnDistributionItem extends Model
{
    protected $fillable = [
        'return_distribution_id',
        'product_id',
        'qty',
        'reason'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}