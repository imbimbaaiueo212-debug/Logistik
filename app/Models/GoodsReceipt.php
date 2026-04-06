<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'date',
        'warehouse_id',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'date' => 'datetime',   // 🔥 Penting untuk menghindari error format()
    ];

    /**
     * Relationships
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // Opsional: Jika sering pakai shorthand
    public function po()
    {
        return $this->purchaseOrder();
    }
}