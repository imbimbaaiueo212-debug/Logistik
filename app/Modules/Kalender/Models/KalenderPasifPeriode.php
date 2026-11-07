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
        'cetak_ke',
        'status',
        'created_by',
        'grup',
    ];

    /**
     * Total Spare Pasif 3% (input manual) untuk edisi periode ini.
     * Jika "Cetak ke" sudah diisi, hanya spare dengan tahap yang sama yang ikut dihitung.
     * Jika belum diisi, semua spare edisi ini dihitung.
     */
    public function spareQty(): int
    {
        $varian = \App\Modules\Kalender\Support\KodeKalender::varian($this->edisi);

        return (int) \App\Modules\Kalender\Models\KalenderSpareManual::query()
            ->whereRaw('UPPER(TRIM(edisi)) IN (' . implode(',', array_fill(0, count($varian), '?')) . ')', $varian)
            ->when($this->cetak_ke, fn ($q) => $q->where('tahap', (int) $this->cetak_ke))
            ->sum('qty');
    }

    public function pesanan()
    {
        return $this->hasMany(KalenderPasifPesanan::class, 'pasif_periode_id');
    }
}
