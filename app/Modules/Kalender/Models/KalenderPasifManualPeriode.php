<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPasifManualPeriode extends Model
{
    protected $table = 'kalender_pasif_manual_periodes';

    protected $fillable = [
        'edisi',
        'judul',
        'periode',
        'bulan',
        'tahun',
        'no_ps',
        'grup',
        'status',
        'created_by',
    ];

    public function transaksis()
    {
        return $this->hasMany(KalenderPasifManualTransaksi::class, 'pasif_manual_periode_id');
    }
}
