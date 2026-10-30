<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan nama unit di Excel yang tidak cocok dengan master Unit Kemitraan.
 * Terpisah dari unit_nama_mismatches (milik Majalah) agar data kalender
 * dan majalah tidak saling tercampur.
 */
class PesananKalenderMismatch extends Model
{
    protected $table = 'pesanan_kalender_mismatches';

    protected $fillable = [
        'no_cab',
        'nama_excel',
        'nama_master',
        'sumber',
        'pesanan_kalender_id',
        'periode',
        'is_resolved',
        'catatan',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function pesananKalender()
    {
        return $this->belongsTo(PesananKalender::class, 'pesanan_kalender_id');
    }
}
