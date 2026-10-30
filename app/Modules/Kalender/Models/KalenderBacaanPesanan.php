<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderBacaanPesanan extends Model
{
    protected $table = 'kalender_bacaan_pesanans';

    protected $fillable = [
        'bacaan_periode_id',
        'nama_unit',
        'no_cab',
        'bacaan_unit',
        'telepon',
        'alamat',
        'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(KalenderBacaanPeriode::class, 'bacaan_periode_id');
    }
}
