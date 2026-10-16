<?php

namespace App\Modules\Distribusi\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Master\Models\Warehouse;

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