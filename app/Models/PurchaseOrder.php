<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    $total = 0;
    $received = 0;

    foreach ($this->items as $item) {
        $total += $item->qty;
        $received += $item->received_qty;
    }

    if ($received == 0) {
        return 'Draft';
    } elseif ($received < $total) {
        return 'Partial';
    } elseif ($received == $total) {
        return 'Completed';
    } else {
        return 'Over Received'; // 🔥 ini hasil override
    }
}
}