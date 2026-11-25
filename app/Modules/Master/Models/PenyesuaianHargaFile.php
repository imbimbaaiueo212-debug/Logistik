<?php

namespace App\Modules\Master\Models;

use Illuminate\Database\Eloquent\Model;

class PenyesuaianHargaFile extends Model
{
    protected $table = 'penyesuaian_harga_files';

    protected $fillable = ['batch_id', 'jenis', 'path', 'nama', 'ukuran'];

    public function labelJenis(): string
    {
        return match ($this->jenis) {
            'surat'   => 'Surat permohonan / bukti',
            'data'    => 'Data perubahan harga',
            'lampiran' => 'Lampiran / bukti',
            'modul'   => 'Data perubahan: Modul & Raport',
            'seragam' => 'Data perubahan: Seragam, Tas & KPK',
            default   => $this->jenis,
        };
    }
}
