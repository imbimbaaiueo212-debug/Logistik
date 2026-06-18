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
        'no_pl', 
        'tgl_turun_pl', 
        'nama_unit', 
        'pengiriman', 
        'nama_barang',
        'tgl_bayar', 
        'jumlah_bayar', 
        'nama_stokis', 
        'tgl_estimasi',
        'estimasi_hari', 
        'penyebut', 
        'pengambil', 
        'ket', 
        'jakarta_aktif_id',
        'is_processed',     // ← tambahkan
        'processed_at',
        'printed_at',           // ← TAMBAHKAN INI      // ← tambahkan
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'tgl_turun_pl' => 'datetime',
        'tgl_bayar'    => 'datetime',     // ← INI YANG PENTING
        'tgl_estimasi' => 'datetime',
        'deleted_at'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
        'printed_at'   => 'datetime',
    ];
}