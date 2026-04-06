<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    protected $fillable = ['transfer_id', 'product_id', 'qty'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}