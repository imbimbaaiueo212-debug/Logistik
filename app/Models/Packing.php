<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    protected $table = 'packing';

    protected $fillable = [
        'picking_id',
        'qc_outgoing_id',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'pengiriman',
        'nama_barang',
        'tgl_bayar',
        'jumlah_bayar',
        'tgl_estimasi',
        'berat',
        'berat_aktual',
        'tgl_packing',
        'nama_packer',
        'koli',
        'status_packing',
        'keterangan_packing',
        'packing_by',
        'packing_at'
    ];

    protected $casts = [
        'tgl_turun_pl' => 'date',
        'tgl_bayar'    => 'date',
        'tgl_estimasi' => 'date',
        'tgl_packing'  => 'date',
        'berat'        => 'decimal:2',
        'berat_aktual' => 'decimal:2',
        'packing_at'   => 'datetime'
    ];

    // Relasi
    public function picking()
    {
        return $this->belongsTo(Picking::class);
    }

    public function qcOutgoing()
    {
        return $this->belongsTo(QcOutgoing::class);
    }
    public function distributionOrder()
{
    return $this->hasOne(DistributionOrder::class, 'packing_id');
}
}