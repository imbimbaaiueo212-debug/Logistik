<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Majalah\Models\PesananMajalah;

class UnitNamaMismatch extends Model
{
    protected $table = 'unit_nama_mismatches';

    protected $fillable = [
        'no_cab',
        'nama_excel',
        'nama_master',
        'sumber',
        'pesanan_majalah_id',
        'periode',
        'is_resolved',
        'catatan',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function pesananMajalah()
    {
        return $this->belongsTo(PesananMajalah::class);
    }
}