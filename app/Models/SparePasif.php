<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePasif extends Model
{
    protected $fillable = [
        'edisi',
        'judul',
        'periode',
        'bulan',
        'tahun',
        'dlc_total',
        'pasif_total',
        'bacaan_total',
        'grand_total',
        'spare_raw',
        'spare',
        'lembar',
        'grup',
        'status',
        'no_ps',
        'created_by',
    ];

    protected $casts = [
        'dlc_total'     => 'integer',
        'pasif_total'   => 'integer',
        'bacaan_total'  => 'integer',
        'grand_total'   => 'integer',
        'spare_raw'     => 'float',
        'spare'         => 'integer',
        'lembar'        => 'integer',
    ];
}