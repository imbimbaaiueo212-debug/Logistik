<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananMajalahUnit extends Model
{
    use HasFactory;

    protected $table = 'pesanan_majalah_unit';

    protected $fillable = [
        'pesanan_majalah_kabupaten_id',
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
            PesananMajalahKabupaten::class,
            'pesanan_majalah_kabupaten_id'
        );
    }
}