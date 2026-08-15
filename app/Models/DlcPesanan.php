<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DlcPesanan extends Model
{
    protected $table = 'dlc_pesanan';

    protected $fillable = [
        'dlc_periode_id',
        'nama_unit',
        'qty',
        'no_cab',
        'alamat',
        'telepon',
        'keterangan',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(DlcPeriode::class, 'dlc_periode_id');
    }
}