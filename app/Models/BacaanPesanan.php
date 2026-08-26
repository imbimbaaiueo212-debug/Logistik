<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacaanPesanan extends Model
{
    protected $fillable = [
        'bacaan_periode_id', 'nama_unit', 'no_cab',
        'bacaan_unit', 'telepon', 'alamat', 'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(BacaanPeriode::class, 'bacaan_periode_id');
    }
}