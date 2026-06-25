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
        
        // Kolom Print Status
        'printed_at',
        'picking_printed_at',
        'qc_printed_at',
        'ra_picking_printed_at',
        'packing_printed_at',
        'distribusi_printed_at',

        // Kolom Berat
        'order_weight',
        'berat',
        'billing_last_name',
        'billing_company',
    ];

    protected $casts = [
        'tgl_turun_pl'       => 'datetime',
        'tgl_bayar'          => 'datetime',
        'tgl_estimasi'       => 'datetime',
        'deleted_at'         => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'is_processed'       => 'boolean',
        'processed_at'       => 'datetime',
        
        // Print timestamps
        'printed_at'         => 'datetime',
        'picking_printed_at' => 'datetime',
        'qc_printed_at'      => 'datetime',
        'ra_picking_printed_at' => 'datetime',
        'packing_printed_at' => 'datetime',
        'distribusi_printed_at' => 'datetime',

        // Berat
        'order_weight'       => 'decimal:2',
        'berat'              => 'decimal:2',
    ];

    public function jakartaAktif()
    {
        return $this->belongsTo(JakartaAktif::class, 'jakarta_aktif_id');
    }
    public function bimbashopOrders()
{
    return $this->hasMany(BimbashopOrder::class, 'order_id', 'no_pl');
}
public function picking()
    {
        return $this->belongsTo(Picking::class, 'picking_id');
    }
}