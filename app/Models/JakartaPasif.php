<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JakartaPasif extends Model
{
    protected $table = 'jakarta_pasif';

    protected $guarded = [];

    protected $casts = [
        'tgl_pesan'           => 'datetime',
        'payment_date'        => 'datetime',
        'estimasi_print_pl'   => 'datetime',
        'estimasi_persiapan'  => 'datetime',
        'processed_at'        => 'datetime',
        'is_processed'        => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JakartaPasifItem::class, 'jakarta_pasif_id');
    }

    public function casdana()
    {
        return $this->belongsTo(CasdanaTransaction::class, 'id_pesan', 'invoice_number');
    }

    public function realisasi()
    {
        return $this->hasMany(RealisasiPasif::class, 'jakarta_pasif_id');
    }
}