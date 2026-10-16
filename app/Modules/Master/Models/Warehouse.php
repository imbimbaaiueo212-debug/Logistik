<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location'
    ];

    public function isFrozen()
{
    return $this->is_freeze;
}
}