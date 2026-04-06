<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
    'nama_supplier',
    'kode_supplier',
    'email',
    'phone',
    'address'
];


//relasi di dalam sini
    public function products()
{
    return $this->belongsToMany(Product::class)->withPivot('price');
}

public function getNameAttribute()
{
    return $this->nama_supplier;
}
}
