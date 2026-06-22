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
        'rekap_number',
        'no_pl', 
        'tgl_turun_pl', 
        'nama_unit', 
        'pengiriman', 
        'service_pengiriman',
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
        'is_processed',
        'processed_at',
        
        // Kolom baru untuk Picking List
        'picking_printed_at',
        
        // Kolom Berat (baru)
        'order_weight',     // atau 'berat' jika Anda lebih suka
        'berat',            // alternatif nama
        'billing_last_name',
        'billing_company',
    ];

    protected $casts = [
        'tgl_turun_pl'      => 'datetime',
        'tgl_bayar'         => 'datetime',
        'tgl_estimasi'      => 'datetime',
        'deleted_at'        => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'is_processed'      => 'boolean',
        'processed_at'      => 'datetime',
        'printed_at'        => 'datetime',
        'picking_printed_at'=> 'datetime',
        
        // Cast berat sebagai decimal / float
        'order_weight'      => 'decimal:2',
        'berat'             => 'decimal:2',
    ];

    public function jakartaAktif()
    {
        return $this->belongsTo(JakartaAktif::class, 'jakarta_aktif_id');
    }
}