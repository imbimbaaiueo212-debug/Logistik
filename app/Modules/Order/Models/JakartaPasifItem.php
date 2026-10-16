<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Master\Models\Product;

class JakartaPasifItem extends Model
{
    protected $table = 'jakarta_pasif_items';

    protected $guarded = [];

    public function jakartaPasif(): BelongsTo
    {
        return $this->belongsTo(JakartaPasif::class, 'jakarta_pasif_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}