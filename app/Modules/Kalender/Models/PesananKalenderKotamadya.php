<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananKalenderKotamadya extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender_kotamadya';

    protected $fillable = [
        'pesanan_kalender_id',
        'nama_kotamadya',
        'contact_person',
        'telepon_contact_person',
        'urutan',
    ];

    protected $casts = [
        'pesanan_kalender_id' => 'integer',
        'urutan'             => 'integer',
    ];

    /**
     * Relasi ke Pesanan Kalender (parent)
     */
    public function pesananKalender(): BelongsTo
    {
        return $this->belongsTo(
            PesananKalender::class,
            'pesanan_kalender_id',
            'id'
        );
    }

    /**
     * Relasi ke Unit Kotamadya
     */
    public function units(): HasMany
    {
        return $this->hasMany(
            PesananKalenderUnitKotamadya::class,
            'pesanan_kalender_kotamadya_id',
            'id'
        );
    }
}