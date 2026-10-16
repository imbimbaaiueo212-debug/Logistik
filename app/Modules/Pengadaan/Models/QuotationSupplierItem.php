<?php

namespace App\Modules\Pengadaan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;

class QuotationSupplierItem extends Model
{
    protected $fillable = ['quotation_supplier_id','product_id','price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}