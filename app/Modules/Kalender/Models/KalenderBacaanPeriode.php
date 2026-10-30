<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderBacaanPeriode extends Model
{
    protected $table = 'kalender_bacaan_periodes';

    protected $fillable = [
        'edisi',
        'judul',
        'periode',
        'bulan',
        'tahun',
        'no_ps',
        'status',
        'created_by',
        'grup',
    ];

    public function pesanan()
    {
        return $this->hasMany(KalenderBacaanPesanan::class, 'bacaan_periode_id');
    }
}
