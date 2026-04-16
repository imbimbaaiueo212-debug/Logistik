<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 
        'date', 
        'status', 
        'total'
    ];

    protected $casts = [
        'date'  => 'date',
        'total' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function suppliers()
    {
        return $this->hasMany(QuotationSupplier::class);
    }

    /**
     * Semua item + harga dari supplier (paling penting)
     */
    public function supplierItems()
    {
        return $this->hasManyThrough(
            QuotationSupplierItem::class,
            QuotationSupplier::class,
            'quotation_id',          // Foreign key di QuotationSupplier
            'quotation_supplier_id', // Foreign key di QuotationSupplierItem
            'id',                    // Local key di Quotation
            'id'                     // Local key di QuotationSupplier
        );
    }

    /**
     * Helper: Ambil supplier utama (karena form kamu pakai 1 supplier)
     */
    public function supplier()
    {
        return $this->hasOne(QuotationSupplier::class)
                    ->with('supplier');
    }

    /**
     * Helper: Ambil semua item beserta harganya dalam 1 query
     */
    public function itemsWithPrice()
    {
        return $this->hasMany(QuotationItem::class)
                    ->with(['product', 'supplierItem']);
    }
}