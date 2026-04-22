<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GoodsReceiptItem;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',   // ← Ini yang penting
        'date',          // ← Kalau kamu punya kolom date
        'total',         // ← Kolom total
        // tambahkan field lain yang ada di tabel purchase_orders
    ];
    protected $casts = [
        'date' => 'datetime',        // atau 'datetime:Y-m-d H:i:s' jika mau format default
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function getStatusAttribute()
    {
        $totalOrdered = $this->items()->sum('qty');

        $totalReceived = GoodsReceiptItem::whereHas('receipt', function ($q) {
            $q->where('purchase_order_id', $this->id);
        })->sum('qty_ok');

        if ($totalReceived == 0) {
            return 'Draft';
        } elseif ($totalReceived < $totalOrdered) {
            return 'Partial';
        } elseif ($totalReceived == $totalOrdered) {
            return 'Completed';
        } else {
            return 'Over';
        }
    }
}