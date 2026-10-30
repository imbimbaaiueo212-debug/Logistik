<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananKalenderUnit extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender_unit';

    protected $fillable = [
        'pesanan_kalender_kabupaten_id',
        'no',
        'nama_unit',
        'no_cabang',
        'jumlah_pesanan',
        'alamat_unit',
        'telepon',
    ];

    /**
     * Unit milik satu kabupaten.
     */
    public function kabupaten()
    {
        return $this->belongsTo(
            PesananKalenderKabupaten::class,
            'pesanan_kalender_kabupaten_id'
        );
    }
}