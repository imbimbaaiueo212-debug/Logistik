<?php

namespace App\Modules\PengeluaranLogistik\Models;

use Illuminate\Database\Eloquent\Model;

class TransferGudangManual extends Model
{
    protected $table = 'transfer_gudang_manual';

    protected $fillable = [
        'tanggal', 'dari', 'tujuan', 'product_id', 'nama_barang', 'sku',
        'kategori', 'qty', 'catatan', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty'     => 'integer',
    ];
}
