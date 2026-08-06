<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualDistributionOrder extends Model
{
    protected $table = 'manual_distribution_orders';

    protected $fillable = [
        'manual_packing_id',
        'manual_picking_id',
        'no_pl',
        'no_ps',
        'nama_unit',
        'grup',
        'kategori_order',
        'ekspedisi',
        'service_pengiriman',
        'status_kirim',
        'berat',
        'berat_aktual',
        'koli',
        'status_distribusi',
        'no_resi',
        'tgl_kirim',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tgl_kirim'    => 'date',
        'berat'        => 'decimal:2',
        'berat_aktual' => 'decimal:2',
    ];

    public function manualPacking()
    {
        return $this->belongsTo(ManualPacking::class, 'manual_packing_id');
    }

    public function manualPicking()
    {
        return $this->belongsTo(ManualPicking::class, 'manual_picking_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}