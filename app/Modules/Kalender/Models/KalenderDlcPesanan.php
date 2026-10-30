<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderDlcPesanan extends Model
{
    protected $table = 'kalender_dlc_pesanan';

    protected $fillable = [
        'dlc_periode_id',
        'nama_unit',
        'qty',
        'no_cab',
        'alamat',
        'telepon',
        'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(KalenderDlcPeriode::class, 'dlc_periode_id');
    }
}
