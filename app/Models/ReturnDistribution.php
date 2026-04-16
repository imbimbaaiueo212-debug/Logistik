<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReturnDistribution extends Model
{
    protected $fillable = [
        'distribution_id',
        'warehouse_id',
        'date',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(ReturnDistributionItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }
}