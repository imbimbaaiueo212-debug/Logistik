<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DlcPeriode extends Model
{
    protected $fillable = [
        'edisi',
        'judul',
        'periode',
        'bulan',
        'tahun',
        'status',
        'created_by',
    ];

    public function pesanan(): HasMany
    {
        return $this->hasMany(DlcPesanan::class, 'dlc_periode_id');
    }

    public function getTotalQtyAttribute(): int
    {
        return $this->pesanan()->sum('qty');
    }
}