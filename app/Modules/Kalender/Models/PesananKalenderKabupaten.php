<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananKalenderKabupaten extends Model
{
    use HasFactory;

    protected $table = 'pesanan_kalender_kabupaten';

    protected $fillable = [
        'pesanan_kalender_id',
        'nama_kabupaten',
        'contact_person',
        'telepon_contact_person',
        'urutan',
    ];

    /**
     * Kabupaten milik satu periode pesanan kalender.
     */
    public function pesananKalender()
    {
        return $this->belongsTo(
            PesananKalender::class,
            'pesanan_kalender_id'
        );
    }

    /**
     * Kabupaten memiliki banyak unit.
     */
    public function units()
    {
        return $this->hasMany(
            PesananKalenderUnit::class,
            'pesanan_kalender_kabupaten_id'
        );
    }
}