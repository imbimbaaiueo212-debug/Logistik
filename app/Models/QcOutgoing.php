<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcOutgoing extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'keterangan',
        'picking_id',
        'pic_qc',       // WAJIB ADA
        'created_by'
    ];

    public function picking()
    {
        return $this->belongsTo(Picking::class);
    }
}