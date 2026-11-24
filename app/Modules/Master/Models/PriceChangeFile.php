<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;

class PriceChangeFile extends Model
{
    protected $table = 'price_change_request_files';

    protected $fillable = ['request_id', 'jenis', 'path', 'nama', 'ukuran'];

    public function isImage(): bool
    {
        return in_array(strtolower(pathinfo($this->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }
}
