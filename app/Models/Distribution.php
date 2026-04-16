<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $fillable = [
        'warehouse_id',
        'destination',
        'date',
        'status',
        'approved_at'
    ];

    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}