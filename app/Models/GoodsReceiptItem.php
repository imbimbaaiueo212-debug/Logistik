<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
   protected $fillable = ['goods_receipt_id', 'product_id', 'qty', 'price',];

public function product()
{
    return $this->belongsTo(Product::class);
}
public function poItem()
{
    return $this->belongsTo(PurchaseOrderItem::class, 'product_id', 'product_id');
}
public function receipt()
{
    return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
}
}
