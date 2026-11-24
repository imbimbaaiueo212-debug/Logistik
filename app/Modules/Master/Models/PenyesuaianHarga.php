<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;

class PenyesuaianHarga extends Model
{
    protected $table = 'penyesuaian_harga';

    protected $fillable = [
        'batch_id', 'grup', 'no', 'nama', 'harga_2024', 'harga_2026',
        'product_id', 'harga_beli_sebelum', 'harga_jual_sebelum', 'harga_jual_baru', 'diterapkan_pada',
    ];

    protected $casts = [
        'diterapkan_pada' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(PenyesuaianHargaBatch::class, 'batch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /** Status harga produk di Master Product dibanding PDF. */
    public function statusHarga(): string
    {
        if (! $this->product) {
            return 'belum_cocok';
        }

        $beli = round((float) $this->product->harga_beli);

        if ($beli === round((float) $this->harga_2026)) {
            return 'sudah_2026';
        }
        if ($beli === round((float) $this->harga_2024)) {
            return 'masih_2024';
        }

        return 'beda';
    }
}
