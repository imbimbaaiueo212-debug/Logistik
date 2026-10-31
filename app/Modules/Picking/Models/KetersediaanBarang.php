<?php

namespace App\Modules\Picking\Models;

use Illuminate\Database\Eloquent\Model;

class KetersediaanBarang extends Model
{
    protected $table = 'ketersediaan_barang';

    protected $fillable = ['jenis', 'kode', 'status', 'tgl_datang', 'diubah_oleh'];

    protected $casts = ['tgl_datang' => 'date'];
}
