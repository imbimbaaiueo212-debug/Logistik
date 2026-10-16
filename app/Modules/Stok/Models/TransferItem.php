<?php

namespace App\Modules\Stok\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;

class TransferItem extends Model
{
    protected $fillable = ['transfer_id', 'product_id', 'qty'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}