<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockOpnameItem extends Model
{
    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_qty',
        'physical_qty',
        'selisih',
        'note',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'physical_qty' => 'integer',
        'selisih' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT (CORE LOGIC)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /*
        |--------------------------------------------------
        | AUTO HITUNG SELISIH (WAJIB)
        |--------------------------------------------------
        */
        static::saving(function ($item) {

            // default jika null
            $item->physical_qty = $item->physical_qty ?? 0;
            $item->system_qty = $item->system_qty ?? 0;

            $item->selisih = $item->physical_qty - $item->system_qty;
        });

        /*
        |--------------------------------------------------
        | PROTECT UPDATE (ANTI EDIT SETELAH SUBMIT)
        |--------------------------------------------------
        */
        static::updating(function ($item) {

            $opname = $item->opname()->lockForUpdate()->first();

            if (!$opname) {
                throw new \Exception('Data Stock Opname tidak ditemukan');
            }

            if ($opname->status !== StockOpname::STATUS_DRAFT) {
                throw new \Exception('Tidak bisa edit, Stock Opname sudah diproses');
            }
        });

        /*
        |--------------------------------------------------
        | PROTECT DELETE (JANGAN SAMPAI HAPUS DATA)
        |--------------------------------------------------
        */
        static::deleting(function ($item) {

            if ($item->opname && $item->opname->status !== StockOpname::STATUS_DRAFT) {
                throw new \Exception('Tidak bisa hapus item, SO sudah diproses');
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER (DIGUNAKAN DI SELURUH SISTEM)
    |--------------------------------------------------------------------------
    */

    public function isSelisih()
    {
        return $this->selisih !== 0;
    }

    public function isMinus()
    {
        return $this->selisih < 0;
    }

    public function isPlus()
    {
        return $this->selisih > 0;
    }

    public function adjustmentType()
    {
        return $this->selisih > 0 ? 'in' : 'out';
    }

    public function adjustmentQty()
    {
        return abs($this->selisih);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE (BIAR QUERY BERSIH)
    |--------------------------------------------------------------------------
    */

    public function scopeSelisih($query)
    {
        return $query->where('selisih', '!=', 0);
    }

    public function scopeMinus($query)
    {
        return $query->where('selisih', '<', 0);
    }

    public function scopePlus($query)
    {
        return $query->where('selisih', '>', 0);
    }
}