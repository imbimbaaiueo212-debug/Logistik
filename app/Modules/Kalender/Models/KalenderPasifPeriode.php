<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPasifPeriode extends Model
{
    protected $table = 'kalender_pasif_periodes';

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
        return $this->hasMany(KalenderPasifPesanan::class, 'pasif_periode_id');
    }
}
