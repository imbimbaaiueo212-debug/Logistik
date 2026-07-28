<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananMajalahUnitPuw1 extends Model
{
    use HasFactory;

    protected $table = 'pesanan_majalah_unit_puw1';

    protected $fillable = [
        'pesanan_majalah_puw1_id',
        'no',
        'nama_unit',
        'no_cabang',
        'kabupaten_kota',
        'jumlah_pesanan',
        'alamat_unit',
        'telepon',
    ];

    protected $casts = [
        'pesanan_majalah_puw1_id' => 'integer',
        'no' => 'integer',
        'jumlah_pesanan' => 'decimal:2',
    ];

    /**
     * Relasi ke periode PUW1
     */
    public function pesananMajalahPuw1(): BelongsTo
    {
        return $this->belongsTo(
            PesananMajalahPuw1::class,
            'pesanan_majalah_puw1_id',
            'id'
        );
    }
}