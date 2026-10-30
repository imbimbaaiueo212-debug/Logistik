<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPasifPesanan extends Model
{
    protected $table = 'kalender_pasif_pesanans';

    protected $fillable = [
        'pasif_periode_id',
        'nama_unit',
        'no_cab',
        'qty',
        'bacaan_unit',
        'telepon',
        'alamat',
        'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(KalenderPasifPeriode::class, 'pasif_periode_id');
    }
}
