<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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