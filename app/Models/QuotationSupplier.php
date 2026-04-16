<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class QuotationSupplier extends Model
{
    protected $fillable = ['quotation_id','supplier_id'];

public function supplier()
{
    return $this->belongsTo(Supplier::class);
}

public function items()
{
    return $this->hasMany(QuotationSupplierItem::class);
}
}