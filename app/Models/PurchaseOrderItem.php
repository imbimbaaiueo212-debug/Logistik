<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Optional: tambahkan relasi ke PurchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    public function receivedItems()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'product_id', 'product_id');
    }
    public function receipts()
{
    return $this->hasMany(GoodsReceiptItem::class, 'product_id', 'product_id');
}
public function getReceivedQtyAttribute()
{
    return $this->receipts->sum('qty');
}

public function getSisaAttribute()
{
    return $this->qty - $this->received_qty;
}
}