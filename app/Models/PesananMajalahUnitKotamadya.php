<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananMajalahUnitKotamadya extends Model
{
    use HasFactory;

    /**
     * Nama tabel database
     */
    protected $table = 'pesanan_majalah_unit_kotamadya';

    /**
     * Kolom yang boleh diisi melalui mass assignment
     */
    protected $fillable = [
        'pesanan_majalah_kotamadya_id',
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
        'pesanan_majalah_kotamadya_id' => 'integer',
        'no' => 'integer',
        'jumlah_pesanan' => 'decimal:2',
    ];

    /**
     * Relasi ke Pesanan Majalah Kotamadya
     *
     * Satu unit Kotamadya dimiliki oleh
     * satu Pesanan Majalah Kotamadya.
     */
    public function pesananMajalahKotamadya(): BelongsTo
    {
        return $this->belongsTo(
            PesananMajalahKotamadya::class,
            'pesanan_majalah_kotamadya_id',
            'id'
        );
    }
}