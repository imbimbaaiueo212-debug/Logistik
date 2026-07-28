<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananMajalahKotamadya extends Model
{
    use HasFactory;

    protected $table = 'pesanan_majalah_kotamadya';

    protected $fillable = [
        'pesanan_majalah_id',
        'nama_kotamadya',
        'contact_person',
        'telepon_contact_person',
        'urutan',
    ];

    protected $casts = [
        'pesanan_majalah_id' => 'integer',
        'urutan'             => 'integer',
    ];

    /**
     * Relasi ke Pesanan Majalah (parent)
     */
    public function pesananMajalah(): BelongsTo
    {
        return $this->belongsTo(
            PesananMajalah::class,
            'pesanan_majalah_id',
            'id'
        );
    }

    /**
     * Relasi ke Unit Kotamadya
     */
    public function units(): HasMany
    {
        return $this->hasMany(
            PesananMajalahUnitKotamadya::class,
            'pesanan_majalah_kotamadya_id',
            'id'
        );
    }
}