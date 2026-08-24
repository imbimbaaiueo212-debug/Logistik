<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickingPasif extends Model
{
    protected $table = 'picking_pasif';

    protected $fillable = [
        'realisasi_pasif_id',
        'jakarta_pasif_id',
        'no_pl',
        'id_pesan',
        'kategori_order',
        'tgl_order',
        'tgl_picking',
        'tgl_terima',                 // ← ditambahkan
        'payment_date',
        'waktu_estimasi_persiapan',
        'jam_picking',
        'vendor',
        'nama_unit',
        'billing_last_name',
        'billing_company',
        'kirim',
        'no_telpon',
        'alamat_kirim',
        'kab_kota_provinsi',
        'ekspedisi',
        'service_pengiriman',
        'pesanan',
        'total',
        'berat',
        'total_item',
        'total_qty',
        'status',
        'status_persiapan',           // ← ditambahkan
        'pic',                        // ← ditambahkan
        'printed_at',
        'created_by',
        'catatan',
    ];

    protected $casts = [
        'tgl_order'                => 'date',
        'tgl_picking'              => 'date',
        'tgl_terima'               => 'datetime',   // ← ditambahkan
        'payment_date'             => 'date',
        'waktu_estimasi_persiapan' => 'date',
        'printed_at'               => 'datetime',
        'total'                    => 'decimal:2',
        'berat'                    => 'decimal:2',
        'total_item'               => 'integer',
        'total_qty'                => 'integer',
    ];

    // ====================== RELATIONS ======================

    public function realisasiPasif(): BelongsTo
    {
        return $this->belongsTo(RealisasiPasif::class, 'realisasi_pasif_id');
    }

    public function jakartaPasif(): BelongsTo
    {
        return $this->belongsTo(JakartaPasif::class, 'jakarta_pasif_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PickingPasifItem::class, 'picking_pasif_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}