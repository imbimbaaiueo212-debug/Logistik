<?php

namespace App\Modules\Stok\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Warehouse;

class Transfer extends Model
{
    protected $fillable = [
    'from_warehouse_id',
    'to_warehouse_id',
    'date',
    'status',
    'approved_at',
    'note'
];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }
}