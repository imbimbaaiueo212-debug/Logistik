<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'label',
        'satuan',
        'berat_satuan',
        'berat_paket',
        'isi',
        'harga_beli',
        'harga_jual',
        'status',
        'role',
        'tanggal_rilis',
        'jenis',     // ← baru
        'hal',       // ← baru
        'lembar',    // ← baru
        'kertas',    // ← baru
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

    // ================== ACCESSORS (RUMUS OTOMATIS) ==================

    public function getBeratPaketAttribute()
    {
        if ($this->berat_satuan && $this->isi) {
            return round($this->berat_satuan * $this->isi, 3);
        }

        return $this->attributes['berat_paket'] ?? null;
    }

    public function getHargaBeliPerSatuanAttribute()
    {
        if ($this->harga_beli && $this->isi && $this->isi > 0) {
            return round($this->harga_beli / $this->isi, 2);
        }
        return null;
    }

    public function getHargaJualPerSatuanAttribute()
    {
        if ($this->harga_jual && $this->isi && $this->isi > 0) {
            return round($this->harga_jual / $this->isi, 2);
        }
        return null;
    }
}