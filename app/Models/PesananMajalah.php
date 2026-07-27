<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananMajalah extends Model
{
    use HasFactory;

    protected $table = 'pesanan_majalah';

    protected $fillable = [
        'judul',
        'bulan',
        'tahun',
        'periode',
    ];

    /**
     * Satu periode memiliki banyak kabupaten.
     */
    public function kabupaten()
    {
        return $this->hasMany(
            PesananMajalahKabupaten::class,
            'pesanan_majalah_id'
        );
    }
}