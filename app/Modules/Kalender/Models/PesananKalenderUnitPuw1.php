<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananKalenderUnitPuw1 extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender_unit_puw1';

    protected $fillable = [
        'pesanan_kalender_puw1_id',
        'no',
        'nama_unit',
        'no_cabang',
        'kabupaten_kota',
        'jumlah_pesanan',
        'alamat_unit',
        'telepon',
    ];

    protected $casts = [
        'pesanan_kalender_puw1_id' => 'integer',
        'no' => 'integer',
        'jumlah_pesanan' => 'decimal:2',
    ];

    /**
     * Relasi ke periode PUW1
     */
    public function pesananKalenderPuw1(): BelongsTo
    {
        return $this->belongsTo(
            PesananKalenderPuw1::class,
            'pesanan_kalender_puw1_id',
            'id'
        );
    }
}