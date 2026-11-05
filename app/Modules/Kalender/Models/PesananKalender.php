<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Kalender\Support\KodeKalender;

class PesananKalender extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender';

    protected $fillable = [
        'judul',
        'bulan',
        'no_ps',
        'tahun',
        'periode',
    ];

    /**
     * Satu periode memiliki banyak kabupaten.
     */
    public function kabupaten()
    {
        return $this->hasMany(
            PesananKalenderKabupaten::class,
            'pesanan_kalender_id'
        );
    }
    public function kotamadya()
{
    return $this->hasMany(PesananKalenderKotamadya::class, 'pesanan_kalender_id');
}

    public function getKodeKalenderAttribute(): ?string
    {
        return KodeKalender::dariTahun($this->tahun);
    }
}
