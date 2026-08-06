<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualQcOutgoing extends Model
{
    protected $table = 'manual_qc_outgoings';

    protected $fillable = [
        'manual_picking_id',
        'no_pl',
        'no_ps',                // ← TAMBAHKAN
        'nama_unit',
        'grup',
        'kategori_order',
        'status_qc',
        'pic_qc',
        'kode_qc',
        'keterangan',
        'tgl_qc',
        'created_by',
    ];

    protected $casts = [
        'tgl_qc' => 'datetime',
    ];

    public function manualPicking()
    {
        return $this->belongsTo(ManualPicking::class);
    }

    public function manualPacking()
    {
        return $this->hasOne(ManualPacking::class, 'manual_qc_outgoing_id');
    }
}