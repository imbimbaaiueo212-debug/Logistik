<?php

namespace App\Modules\Order\Models;

use App\Modules\Master\Models\Product;
use Illuminate\Database\Eloquent\Model;

class LogistikPasifItem extends Model
{
    protected $table = 'logistik_pasif_items';

    protected $fillable = [
        'logistik_pasif_id',
        'product_id',
        'sku',
        'label',
        'nama_produk',
        'qty',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'qty'      => 'integer',
        'harga'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function logistikPasif()
    {
        return $this->belongsTo(LogistikPasif::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
