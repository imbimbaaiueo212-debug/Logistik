<?php

namespace App\Modules\Order\Models;

use App\Modules\Master\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealisasiLogistikAktif extends Model
{
    use SoftDeletes;

    protected $table = 'realisasi_logistik_aktif';

    protected $fillable = [
        'rekap_number',
        'no_pl',
        'tgl_turun_pl',
        'nama_unit',
        'billing_last_name',
        'billing_company',
        'pengiriman',
        'service_pengiriman',
        'nama_barang',
        'kategori_order',
        'product_id',
        'product_ids',
        'tgl_bayar',
        'jumlah_bayar',
        'order_weight',
        'nama_stokis',
        'tgl_estimasi',
        'estimasi_hari',
        'penyebut',
        'pengambil',
        'ket',
        'logistik_aktif_id',
        'printed_at',
        'picking_printed_at',
        'tgl_terima',
        'tgl_picking',
        'status_persiapan',
        'pic',
    ];

    protected $casts = [
        'tgl_turun_pl' => 'date',
        'tgl_bayar'    => 'datetime',
        'tgl_estimasi' => 'datetime',
        'printed_at'   => 'datetime',
        'picking_printed_at' => 'datetime',
        'tgl_terima'   => 'datetime',
        'tgl_picking'  => 'date',
        'jumlah_bayar' => 'decimal:2',
        'order_weight' => 'decimal:2',
    ];

    public function logistikAktif()
    {
        return $this->belongsTo(LogistikAktif::class, 'logistik_aktif_id');
    }

    /**
     * Alias supaya view PDF Jakarta (jakarta-printed-pdf) bisa dipakai ulang.
     */
    public function jakartaAktif()
    {
        return $this->logistikAktif();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
