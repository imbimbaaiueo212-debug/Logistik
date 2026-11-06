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
        'humas',
        'telepon',
        'alamat',
        'keterangan',
    ];

    public function periode()
    {
        return $this->belongsTo(KalenderPasifPeriode::class, 'pasif_periode_id');
    }

    /** RELAWAN @2 exp = relawan x 2 (tiap relawan mendapat 2 kalender). */
    public function getRelawanExpAttribute(): int
    {
        return (int) $this->bacaan_unit * 2;
    }

    /** TOTAL = MURID + RELAWAN @2 exp + HUMAS. */
    public function getTotalKalenderAttribute(): int
    {
        return (int) $this->qty + $this->relawan_exp + (int) $this->humas;
    }
}
