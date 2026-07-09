<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JakartaAktifItem extends Model
{
    protected $table = 'jakarta_aktif_items';

    protected $fillable = [

        'jakarta_aktif_id',

        'product_id',

        'sku',

        'label',

        'nama_produk',

        'qty',

        'harga',

        'subtotal',

    ];

    protected $casts = [

        'qty' => 'integer',

        'harga' => 'decimal:2',

        'subtotal' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELASI
    |--------------------------------------------------------------------------
    */

    public function jakartaAktif()
    {
        return $this->belongsTo(JakartaAktif::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}