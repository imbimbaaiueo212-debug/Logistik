<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderDlcPeriode extends Model
{
    protected $table = 'kalender_dlc_periodes';

    protected $fillable = [
        'edisi',
        'judul',
        'periode',
        'bulan',
        'tahun',
        'status',
        'created_by',
        'no_ps',
    ];

    public function pesanan()
    {
        return $this->hasMany(KalenderDlcPesanan::class, 'dlc_periode_id');
    }

    public function getTotalQtyAttribute(): int
    {
        return (int) $this->pesanan()->sum('qty');
    }
}
