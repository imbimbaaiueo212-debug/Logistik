<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasifPeriode extends Model
{
    protected $fillable = [
        'edisi', 'judul', 'periode', 'bulan', 'tahun',
        'no_ps', 'status', 'created_by', 'grup',
    ];

    public function pesanan()
    {
        return $this->hasMany(PasifPesanan::class);
    }
}