<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPuwPeriode extends Model
{
    protected $table = 'kalender_puw_periode';

    protected $fillable = ['edisi', 'tahun', 'qty', 'jenis_unit', 'no_induk_mitra', 'hanya_aktif'];

    protected $casts = ['hanya_aktif' => 'boolean'];

    public function units()
    {
        return $this->hasMany(KalenderPuwUnit::class, 'periode_id')->orderBy('no');
    }
}
