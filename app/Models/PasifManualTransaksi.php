<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasifManualTransaksi extends Model
{
    protected $fillable = [
        'pasif_manual_periode_id',
        'no',
        'id_pesan',
        'kode_pesan',
        'tgl_pesan',
        'minggu',
        'nama_unit',
        'label',
        'jumlah',
        'pesanan',
        'note',
        'keterangan',
        'no_cab',
        'alamat',
        'telepon',
        'status_kirim',
        'catatan',
    ];

    protected $casts = [
        'tgl_pesan' => 'date',
    ];

    public function periode()
    {
        return $this->belongsTo(PasifManualPeriode::class, 'pasif_manual_periode_id');
    }
}