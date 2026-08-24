<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionPasif extends Model
{
    protected $table = 'distribution_pasif';

    protected $fillable = [
        'packing_pasif_id',
        'picking_pasif_id',
        'qc_outgoing_pasif_id',
        'no_pl',
        'no_ps',
        'tgl_turun_pl',
        'tgl_pickup',
        'awb', 
        'tgl_diterima', 
        'penerima',
        'nama_unit',
        'nama_barang',
        'tgl_bayar',
        'jumlah_bayar',
        'tgl_estimasi',
        'jenis_pengiriman',
        'ekspedisi',
        'service',
        'pengiriman',
        'berat',
        'berat_aktual',
        'koli',
        'status_distribusi',
        'status_pengiriman',
        'no_resi',
        'distribution_at',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_turun_pl'    => 'date',
        'tgl_bayar'       => 'date',
        'tgl_estimasi'    => 'date',
        'distribution_at' => 'datetime',
        'jumlah_bayar'    => 'decimal:2',
        'berat'           => 'decimal:2',
        'berat_aktual'    => 'decimal:2',
        'tgl_pickup'   => 'date',
        'tgl_diterima' => 'date',
    ];

    public function packingPasif(): BelongsTo
    {
        return $this->belongsTo(PackingPasif::class, 'packing_pasif_id');
    }

    public function pickingPasif(): BelongsTo
    {
        return $this->belongsTo(PickingPasif::class, 'picking_pasif_id');
    }

    public function qcOutgoingPasif(): BelongsTo
    {
        return $this->belongsTo(QcOutgoingPasif::class, 'qc_outgoing_pasif_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}