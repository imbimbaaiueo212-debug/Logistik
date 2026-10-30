<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesananKalenderPuw1 extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender_puw1';

    protected $fillable = [
        'judul',
        'bulan',
        'tahun',
        'periode',
        'no_ps',
        'contact_person',
        'telepon_contact_person',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    /**
     * Relasi ke seluruh unit PUW1
     */
    public function units(): HasMany
    {
        return $this->hasMany(
            PesananKalenderUnitPuw1::class,
            'pesanan_kalender_puw1_id'
        );
    }
}