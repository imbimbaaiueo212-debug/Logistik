<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasifPesanan extends Model
{
    protected $fillable = [
        'pasif_periode_id', 'nama_unit', 'no_cab',
        'qty', 'bacaan_unit', 'telepon', 'alamat', 'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(PasifPeriode::class, 'pasif_periode_id');
    }
}