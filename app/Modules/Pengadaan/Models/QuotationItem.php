<?php

namespace App\Modules\Pengadaan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Product;

class QuotationItem extends Model
{
    protected $fillable = [
    'quotation_id',
    'product_id',
    'qty'
];

public function product()
{
    return $this->belongsTo(Product::class);
}

// 🔥 ambil harga dari supplier item
public function supplierItem()
{
    return $this->hasOne(
        QuotationSupplierItem::class,
        'product_id',     // foreign key di supplier item
        'product_id'      // local key di quotation item
    );
}
}