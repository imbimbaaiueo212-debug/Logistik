<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananMajalahKabupaten extends Model
{
    use HasFactory;

    protected $table = 'pesanan_majalah_kabupaten';

    protected $fillable = [
        'pesanan_majalah_id',
        'nama_kabupaten',
        'contact_person',
        'telepon_contact_person',
        'urutan',
    ];

    /**
     * Kabupaten milik satu periode pesanan majalah.
     */
    public function pesananMajalah()
    {
        return $this->belongsTo(
            PesananMajalah::class,
            'pesanan_majalah_id'
        );
    }

    /**
     * Kabupaten memiliki banyak unit.
     */
    public function units()
    {
        return $this->hasMany(
            PesananMajalahUnit::class,
            'pesanan_majalah_kabupaten_id'
        );
    }
}