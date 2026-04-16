<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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