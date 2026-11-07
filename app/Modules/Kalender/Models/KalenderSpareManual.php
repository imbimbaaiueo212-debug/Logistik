<?php

namespace App\Modules\Kalender\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderSpareManual extends Model
{
    protected $table = 'kalender_spare_manual';

    protected $fillable = ['order_id', 'id_manual', 'tgl_order', 'nama', 'sku', 'edisi', 'qty', 'tahap', 'created_by'];

    protected $casts = ['tgl_order' => 'date', 'qty' => 'integer', 'tahap' => 'integer'];
}
