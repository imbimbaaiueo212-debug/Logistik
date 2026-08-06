<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPacking extends Model
{
    protected $table = 'manual_packings';

    protected $fillable = [
        'manual_picking_id',
        'manual_qc_outgoing_id',
        'no_pl',
        'nama_unit',
        'grup',
        'kategori_order',
        'pic_picking',
        'berat',
        'berat_aktual',
        'tgl_packing',
        'nama_packer',
        'koli',
        'status_packing',
        'keterangan_packing',
        'packing_by',
        'packing_at',
        'created_by',
    ];

    protected $casts = [
        'tgl_packing'  => 'date',
        'berat'        => 'decimal:2',
        'berat_aktual' => 'decimal:2',
        'packing_at'   => 'datetime',
    ];

    // ===== Relasi =====
    public function manualPicking()
    {
        return $this->belongsTo(ManualPicking::class, 'manual_picking_id');
    }

    public function manualQcOutgoing()
    {
        return $this->belongsTo(ManualQcOutgoing::class, 'manual_qc_outgoing_id');
    }

    public function packedBy()
    {
        return $this->belongsTo(User::class, 'packing_by');
    }

    // Opsional: kalau nanti ada Distribution Manual
    // public function distributionOrder()
    // {
    //     return $this->hasOne(ManualDistributionOrder::class, 'manual_packing_id');
    // }
}