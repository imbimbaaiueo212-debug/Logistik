<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananKalenderUnitKotamadya extends Model
{
    use HasFactory;

    /**
     * Nama tabel database
     */
    protected $table = 'pesanan_kalender_unit_kotamadya';

    /**
     * Kolom yang boleh diisi melalui mass assignment
     */
    protected $fillable = [
        'pesanan_kalender_kotamadya_id',
        'no',
        'nama_unit',
        'no_cabang',
        'jumlah_pesanan',
        'alamat_unit',
        'telepon',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'pesanan_kalender_kotamadya_id' => 'integer',
        'no' => 'integer',
        'jumlah_pesanan' => 'decimal:2',
    ];

    /**
     * Relasi ke Pesanan Kalender Kotamadya
     *
     * Satu unit Kotamadya dimiliki oleh
     * satu Pesanan Kalender Kotamadya.
     */
    public function pesananKalenderKotamadya(): BelongsTo
    {
        return $this->belongsTo(
            PesananKalenderKotamadya::class,
            'pesanan_kalender_kotamadya_id',
            'id'
        );
    }
}