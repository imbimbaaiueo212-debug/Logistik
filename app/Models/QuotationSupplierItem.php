<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationSupplierItem extends Model
{
    protected $fillable = ['quotation_supplier_id','product_id','price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}