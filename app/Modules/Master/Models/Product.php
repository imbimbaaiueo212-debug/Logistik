<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\Master\Models\Category;
use App\Modules\Order\Models\JakartaAktifItem;

class Product extends Model
{
    /** Harga jual otomatis = harga beli x pengali, dibulatkan ke ribuan terdekat (sisa 500 ke atas naik, di bawah 500 turun). */
    public const PENGALI_MODUL   = 1.49;  // jenis mengandung kata "modul"
    public const PENGALI_LAINNYA = 1.20;  // selain modul

    /** Produk termasuk Modul? (dari Jenis; kalau Jenis kosong boleh dianggap Modul lewat $anggapModul). */
    public static function adalahModul(?string $jenis, bool $anggapModul = false): bool
    {
        return Str::contains(strtolower(trim((string) $jenis)), 'modul')
            || ($anggapModul && trim((string) $jenis) === '');
    }

    /** Pengali harga jual menurut jenis produk (bisa diganti manual lewat $pengaliModul / $pengaliLain). */
    public static function pengaliJual(?string $jenis, bool $anggapModul = false, ?float $pengaliModul = null, ?float $pengaliLain = null): float
    {
        return self::adalahModul($jenis, $anggapModul)
            ? ($pengaliModul ?? self::PENGALI_MODUL)
            : ($pengaliLain ?? self::PENGALI_LAINNYA);
    }

    /** Harga jual satuan sebelum dibulatkan (harga asli hasil kali pengali). */
    public static function hargaJualAsli(float $hargaBeli, ?string $jenis, bool $anggapModul = false, ?float $pengaliModul = null, ?float $pengaliLain = null): float
    {
        return $hargaBeli * self::pengaliJual($jenis, $anggapModul, $pengaliModul, $pengaliLain);
    }

    /**
     * Harga jual otomatis dari harga beli.
     * $anggapModul: dipakai bila Jenis produk kosong dan barangnya masuk kelompok Modul.
     */
    public static function hitungHargaJual(float $hargaBeli, ?string $jenis, int $isi = 1, bool $anggapModul = false, ?float $pengaliModul = null, ?float $pengaliLain = null): float
    {
        // Pembulatan per SATUAN ke ribuan terdekat: 3.350 -> 3.000, 3.499 -> 3.000, 3.500 -> 4.000
        $satuan = round(self::hargaJualAsli($hargaBeli, $jenis, $anggapModul, $pengaliModul, $pengaliLain) / 1000, 0, PHP_ROUND_HALF_UP) * 1000;

        // Jaga-jaga: harga beli ada tetapi hasil bulat jadi 0 -> minimal Rp 1.000
        if ($hargaBeli > 0 && $satuan < 1000) {
            $satuan = 1000;
        }

        // Harga paket = harga satuan x isi
        return $satuan * max(1, $isi);
    }

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

    protected $casts = [
    'harga_diubah_pada' => 'datetime',
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

            $product->harga_jual = self::hitungHargaJual(
                (float) $product->harga_beli,
                $product->jenis,
                (int) $product->isi
            );

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

public function penyesuaianRows()
{
    return $this->hasMany(\App\Modules\Master\Models\PenyesuaianHarga::class, 'product_id')
        ->whereNotNull('diterapkan_pada');
}

// baris penyesuaian dari batch terakhir yang mengubah harga produk ini (menyimpan harga sebelum & sesudah)
public function barisHargaTerakhir()
{
    if (! $this->harga_batch_id) {
        return null;
    }
    return $this->penyesuaianRows->firstWhere('batch_id', $this->harga_batch_id);
}

public function hargaBatch()
{
    return $this->belongsTo(\App\Modules\Master\Models\PenyesuaianHargaBatch::class, 'harga_batch_id');
}

}