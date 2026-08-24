<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcOutgoingPasif extends Model
{
    protected $table = 'qc_outgoing_pasif';

    protected $fillable = [
        'picking_pasif_id',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'pengiriman',
        'nama_barang',
        'tgl_bayar',
        'jumlah_bayar',
        'tgl_estimasi',
        'nama_stokis',
        'estimasi_hari',
        'kode_qc',
        'tgl_qc',
        'status_qc',
        'pic_qc',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tgl_turun_pl' => 'date',
        'tgl_bayar'    => 'date',
        'tgl_estimasi' => 'date',
        'tgl_qc'       => 'datetime',
        'jumlah_bayar' => 'decimal:2',
    ];

    public function pickingPasif(): BelongsTo
    {
        return $this->belongsTo(PickingPasif::class, 'picking_pasif_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}