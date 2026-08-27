<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasifManualPeriode extends Model
{
    protected $fillable = [
        'edisi', 'judul', 'periode', 'bulan', 'tahun',
        'no_ps', 'grup', 'status', 'created_by'
    ];

    public function transaksis()
    {
        return $this->hasMany(PasifManualTransaksi::class, 'pasif_manual_periode_id');
    }
}