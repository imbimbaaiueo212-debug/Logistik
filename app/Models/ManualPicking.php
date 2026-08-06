<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPicking extends Model
{
    protected $table = 'manual_pickings';

    protected $fillable = [
        'manual_realisasi_id',
        'manual_order_id',
        'no_pl',
        'no_ps',
        'kategori_order',
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
        'grup',
    ];

    protected $casts = [
        'tgl_order'                 => 'date',
        'tgl_picking'               => 'date',
        'payment_date'              => 'datetime',
        'waktu_estimasi_persiapan'  => 'date',
        'printed_at'                => 'datetime',
        'total'                     => 'decimal:2',
        'berat'                     => 'decimal:2',
    ];

    public function realisasi()
    {
        return $this->belongsTo(ManualRealisasi::class, 'manual_realisasi_id');
    }

    public function pickingItems()
    {
        return $this->hasMany(ManualPickingItem::class, 'manual_picking_id');
    }

    public function manualOrder()
    {
        return $this->belongsTo(ManualOrder::class);
    }
    public function manualRealisasi()
    {
        return $this->belongsTo(
            ManualRealisasi::class,
            'manual_realisasi_id'
        );
    }
    public function manualQcOutgoing()
{
    return $this->hasOne(ManualQcOutgoing::class);
}
public function product()
{
    return $this->belongsTo(\App\Models\Product::class, 'product_id'); 
    // sesuaikan foreign key jika nama kolomnya berbeda (misal: barang_id, item_id, dll)
}
}