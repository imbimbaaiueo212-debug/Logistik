<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockOpname extends Model
{
    protected $table = 'stock_opnames';

    protected $fillable = [
        'code',
        'warehouse_id',
        'status',
        'note',
        'created_by',
        'approved_by',
        'approved_at',
        'snapshot_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'snapshot_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    protected static function booted()
    {
        static::updating(function ($so) {
            if ($so->getOriginal('status') === self::STATUS_APPROVED) {
                throw new \Exception('Stock Opname sudah di-approve, tidak bisa diubah');
            }
        });

        static::deleting(function ($so) {
            if ($so->status !== self::STATUS_DRAFT) {
                throw new \Exception('Hanya draft yang boleh dihapus');
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted()
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC
    |--------------------------------------------------------------------------
    */

    public function submit()
    {
        if (!$this->isDraft()) {
            throw new \Exception('Hanya draft yang bisa disubmit');
        }

        $this->load('items');

        if ($this->items->count() === 0) {
            throw new \Exception('Item kosong');
        }

        foreach ($this->items as $item) {
            if (is_null($item->physical_qty)) {
                throw new \Exception('Masih ada item belum dihitung');
            }
        }

        $this->update([
            'status' => self::STATUS_SUBMITTED
        ]);
    }

    public function approve()
    {
        return DB::transaction(function () {

            $so = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$so) throw new \Exception('Data tidak ditemukan');
            if ($so->isApproved()) throw new \Exception('Sudah di approve');
            if (!$so->isSubmitted()) throw new \Exception('Harus submit dulu');

            $so->load('items');

            foreach ($so->items as $item) {
                if (is_null($item->physical_qty)) {
                    throw new \Exception('Data belum lengkap');
                }
            }

            $warehouse = $so->warehouse()->lockForUpdate()->first();

            foreach ($so->items as $item) {

                if (!$item->isSelisih()) continue;

                app(\App\Services\StockService::class)->adjustment([
                    'warehouse_id' => $so->warehouse_id,
                    'product_id' => $item->product_id,
                    'qty' => $item->adjustmentQty(),
                    'type' => $item->adjustmentType(),
                    'reference_type' => 'stock_opname',
                    'reference_id' => $so->id,
                    'note' => 'Stock Opname Adjustment',
                ]);
            }

            $so->update([
                'status' => self::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 🔓 UNFREEZE
            $warehouse->update(['is_freeze' => false]);

            return $so;
        });
    }

    public function cancel()
    {
        return DB::transaction(function () {

            if (!$this->isDraft()) {
                throw new \Exception('Hanya draft yang bisa dibatalkan');
            }

            $warehouse = $this->warehouse()->lockForUpdate()->first();

            $this->update([
                'status' => self::STATUS_CANCELLED
            ]);

            $warehouse->update(['is_freeze' => false]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($q) { return $q->where('status', self::STATUS_DRAFT); }
    public function scopeSubmitted($q) { return $q->where('status', self::STATUS_SUBMITTED); }
    public function scopeApproved($q) { return $q->where('status', self::STATUS_APPROVED); }
}