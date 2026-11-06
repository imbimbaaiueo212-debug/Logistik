<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderPuwUnit extends Model
{
    protected $table = 'kalender_puw_unit';

    protected $fillable = [
        'periode_id', 'no', 'id_record', 'no_cab', 'nama_unit', 'status', 'ops', 'no_telp_unit',
        'alamat_unit', 'rt', 'rw', 'provinsi', 'kab_kota', 'kecamatan', 'kel_desa', 'kode_pos', 'qty',
    ];

    public function periode()
    {
        return $this->belongsTo(KalenderPuwPeriode::class, 'periode_id');
    }
}
