<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Category;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'label',
        'satuan',
        'berat_satuan',
        'isi',
        'harga_beli',
        'harga_jual',
        'berat_paket',
        'status',
        'role',
        'tanggal_rilis',
        'jenis',
        'hal',
        'lembar',
        'kertas',
        'kode',
        'kategori_id',
        'sub_kategori',
        'kategori',
        'harga_jual_penyesuaian'

    ];

    protected $appends = [
        'harga_beli_per_satuan',
        'harga_jual_per_satuan',
    ];

    public function suppliers()
    {
        return $this->belongsToMany(
            Supplier::class,
            'product_supplier',
            'product_id',
            'supplier_id'
        )->withPivot('price');
    }

    // ================= ACCESSORS =================

    public function getHargaBeliPerSatuanAttribute()
    {
        if ($this->harga_beli !== null && $this->isi > 0) {
            return round($this->harga_beli / $this->isi, 2);
        }
        return null;
    }

    public function getHargaJualPerSatuanAttribute()
    {
        if ($this->harga_jual !== null && $this->isi > 0) {
            return round($this->harga_jual / $this->isi, 2);
        }
        return null;
    }

   protected static function booted()
{
    static::saving(function ($product) {

        // ================= BERAT =================

        if (
            empty($product->berat_paket)
            && $product->berat_satuan
            && $product->isi
        ) {

            $product->berat_paket =
                round($product->berat_satuan * $product->isi, 3);

        }

        // ================= HARGA =================

        if (
            empty($product->harga_jual)
            && $product->harga_beli !== null
            && $product->isi > 0
        ) {

            $jenis = strtolower(trim($product->jenis ?? ''));

            $multiplier = Str::contains($jenis, 'modul')
                ? 1.49
                : 1.20;

            $hargaDasar = $product->harga_beli * $multiplier;

            $hargaJual = $hargaDasar * $product->isi;

            $product->harga_jual =
                ceil($hargaJual / 50) * 50;

        }

    });
}
       public function category()
{
    return $this->belongsTo(Category::class, 'kategori_id');
}
public function jakartaAktifItems()
{
    return $this->hasMany(JakartaAktifItem::class);
}
}