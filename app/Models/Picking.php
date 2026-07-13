<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picking extends Model
{
    use HasFactory;

    protected $table = 'pickings';

    protected $fillable = [
        'realisasi_aktif_id',
        'jakarta_aktif_id',
        'kategori_order',
        'no_pl',
        'tgl_order',
        'tgl_picking',
        'payment_date',
        'waktu_estimasi_persiapan',
        'jam_picking',
        'id_pesan',
        'vendor',
        'nama_unit',
        'billing_last_name',
        'billing_company',
        'kirim',
        'no_telpon',
        'alamat_kirim',
        'kab_kota_provinsi',
        'ekspedisi',
        'service_pengiriman',
        'pesanan',
        'total',
        'berat',
        'total_item',
        'total_qty',
        'status',
        'printed_at',
        'created_by',
        'catatan',

        // Field legacy (jika masih ada di tabel)
        'tracking_number',
        'berat_bimbashop',
        'berat_aktual',
        'dipicking_oleh',
        'pic_qc',
        'qc_at',
        'pic_packing',
        'packing_at',
        'pic_finishing',
        'finishing_at',
        'status_persiapan',
        'jenis_bank',
        'status_pembayaran',
        'ongkir',
        'fee_payment',
        'harga',
        'diskon',
        'pic',
    ];

    protected $casts = [
        'tgl_order'                => 'date',
        'tgl_picking'              => 'date',
        'payment_date'             => 'datetime',
        'waktu_estimasi_persiapan' => 'date',
        'qc_at'                    => 'datetime',
        'packing_at'               => 'datetime',
        'finishing_at'             => 'datetime',
        'printed_at'               => 'datetime',
        'jam_picking'              => 'string',           // ← Ditambahkan
        'total'                    => 'decimal:2',
        'berat'                    => 'decimal:2',
        'berat_bimbashop'          => 'decimal:2',
        'berat_aktual'             => 'decimal:2',
        'harga'                    => 'decimal:2',
        'diskon'                   => 'decimal:2',
        'ongkir'                   => 'decimal:2',
        'fee_payment'              => 'decimal:2',
        'total_item'               => 'integer',
        'total_qty'                => 'integer',
        
    ];

    // Relasi
    public function realisasi()
    {
        return $this->belongsTo(RealisasiAktif::class, 'realisasi_aktif_id');
    }

    public function pickingItems()
    {
        return $this->hasMany(PickingItem::class, 'picking_id');
    }

    public function jakartaAktif()
    {
        return $this->belongsTo(JakartaAktif::class, 'jakarta_aktif_id');
    }

    // Scope (jika masih digunakan di controller lain)
    public function scopeBelumDipicking($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSudahDipicking($query)
    {
        return $query->whereIn('status', ['completed', 'printed']);
    }
}