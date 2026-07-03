<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionOrder extends Model
{
    protected $table = 'distribution_orders';

    protected $fillable = [
        'packing_id',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'metode_pengiriman',
        'nama_barang',
        'tgl_bayar',
        'jumlah_bayar',
        'tgl_estimasi',

        // Distribusi
        'jenis_pengiriman',      // 'Diambil' atau 'Dikirim'
        'tgl_pengambilan',
        'pengambil',

        'tgl_pickup',
        'ekspedisi',
        'awb',
        'service',
        'estimasi_pengiriman',

        'tgl_diterima',
        'penerima',

        'status_pengiriman',     // Pending, In Transit, Delivered, dll
        'catatan',

        'distribution_at',
        'delivered_at',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_turun_pl'   => 'date',
        'tgl_bayar'      => 'date',
        'tgl_estimasi'   => 'date',
        'tgl_pengambilan'=> 'date',
        'tgl_pickup'     => 'date',
        'tgl_diterima'   => 'date',
        'distribution_at'=> 'datetime',
        'delivered_at'   => 'datetime',
    ];

    // ========================================
    // RELASI
    // ========================================

    public function packing(): BelongsTo
    {
        return $this->belongsTo(Packing::class);
    }

    public function realisasiAktif(): BelongsTo
    {
        return $this->belongsTo(RealisasiAktif::class, 'no_pl', 'no_pl');
    }

    public function jakartaAktif(): BelongsTo
    {
        return $this->belongsTo(JakartaAktif::class, 'no_pl', 'id_pesan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ========================================
    // SCOPE
    // ========================================

    public function scopeDiambil($query)
    {
        return $query->where('jenis_pengiriman', 'Diambil');
    }

    public function scopeDikirim($query)
    {
        return $query->where('jenis_pengiriman', 'Dikirim');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status_pengiriman', 'Delivered');
    }
}