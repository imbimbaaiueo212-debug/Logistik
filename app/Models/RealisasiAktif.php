<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealisasiAktif extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'realisasi_aktif';

    protected $fillable = [
        'no_pl', 'tgl_turun_pl', 'nama_unit', 'pengiriman', 'nama_barang',
        'tgl_bayar', 'jumlah_bayar', 'nama_stokis', 'tgl_estimasi',
        'estimasi_hari', 'penyebut', 'pengambil', 'ket', 'jakarta_aktif_id'
    ];
}