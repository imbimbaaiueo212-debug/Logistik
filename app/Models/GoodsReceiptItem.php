<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
    'goods_receipt_id',
    'product_id',
    'qty_received',   // kolom utama untuk jumlah diterima
    'price',
    'qty_ok',
    'qty_reject',
    'qc_status',
];

protected $attributes = [
    'qty_ok'     => 0,
    'qty_reject' => 0,
    'qc_status'  => 'pending',
];

protected $casts = [
    'qty_received' => 'integer',
    'price'        => 'decimal:2',
    'qty_ok'       => 'integer',
    'qty_reject'   => 'integer',
];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | HELPER (QC LOGIC)
    |--------------------------------------------------------------------------
    */

    public function isPendingQc()
    {
        return $this->qc_status === 'pending';
    }

    public function isQcDone()
    {
        return $this->qc_status === 'done';
    }

    public function totalChecked()
    {
        return ($this->qty_ok ?? 0) + ($this->qty_reject ?? 0);
    }
    public function getIsQcDoneAttribute()
{
    return $this->qc_status === 'done';
}
}