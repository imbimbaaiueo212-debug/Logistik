<?php

namespace App\Modules\Master\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PriceChangeLog extends Model
{
    protected $table = 'price_change_logs';

    protected $fillable = ['request_id', 'user_id', 'aksi', 'catatan'];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}
