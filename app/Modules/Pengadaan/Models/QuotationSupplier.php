<?php

namespace App\Modules\Pengadaan\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Supplier;


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