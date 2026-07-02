<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Picking extends Model
{
    use HasFactory;

    protected $table = 'pickings';

    protected $fillable = [
        'jakarta_aktif_id',
        'no_pl',
        'tgl_order',
        'tgl_picking',
        'jam_picking',
        'id_pesan',
        'cabang',
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
        'tracking_number',
        'pesanan',
        'jenis_bank',
        'status_pembayaran',
        'harga',
        'diskon',
        'ongkir',
        'fee_payment',
        'total',
        'berat',
        'berat_bimbashop',
        'berat_aktual',
        'total_item',
        'total_qty',
        'dipicking_oleh',
        'pic_qc',
        'qc_at',
        'pic_packing',
        'packing_at',
        'pic_finishing',
        'finishing_at',
        'printed_at',
        'status',
        'waktu_estimasi_persiapan',
        'payment_date',
        'pic',
        'status_persiapan',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tgl_order'       => 'date',
        'tgl_picking'     => 'date',
        'jam_picking'     => 'string',
        'qc_at'           => 'datetime',
        'packing_at'      => 'datetime',
        'finishing_at'    => 'datetime',
        'printed_at'      => 'datetime',
        'harga'           => 'decimal:2',
        'diskon'          => 'decimal:2',
        'ongkir'          => 'decimal:2',
        'fee_payment'     => 'decimal:2',
        'total'           => 'decimal:2',
        'berat'           => 'decimal:2',
        'berat_bimbashop' => 'decimal:2',
        'berat_aktual'    => 'decimal:2',
        'total_item'      => 'integer',
        'total_qty'       => 'integer',
    ];

    // Relasi
    public function items()
    {
        return $this->hasMany(PickingItem::class);
    }

    public function jakartaAktif()
    {
        return $this->belongsTo(JakartaAktif::class, 'jakarta_aktif_id');
    }

    // Event otomatis saat dihapus
   protected static function booted()
    {
        static::deleting(function ($picking) {

            JakartaAktif::where('id', $picking->jakarta_aktif_id)
                ->update([
                    'picking_generated' => false,
                ]);
        });
    }

    // Scope
    public function scopeBelumDipicking($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSudahDipicking($query)
    {
        return $query->whereIn('status', ['completed', 'printed']);
    }
        public function realisasiAktif()
    {
        return $this->hasOne(RealisasiAktif::class, 'picking_id');
    }
}