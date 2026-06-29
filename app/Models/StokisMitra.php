<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokisMitra extends Model
{
    use HasFactory;

    protected $table = 'stokis_mitra';

    protected $fillable = [
        'no_cab',
        'nama_stokis_db_kemitraan',
        'nama_stokis_db_bimbashop',
        'no_induk_mitra',
        'nama_mitra',
        'email',
        'no_hp',
        'related_form_pembukaan_unit_aktif',
        'related_formulir_kerjasama_english',
        'db_kemitraan_db_bimbashop',
        'related_unit_bimba_aiueo',
        'related_formulir_kerjasama_mk_mm',
        'related_pengajuan_perubahan',
        'item_sku',
        'ops_stokist',
    ];

    // Hapus casts dulu
    protected $casts = [];

    protected static function boot()
{
    parent::boot();
    
    // Cegah event yang tidak perlu saat import
    static::creating(function ($model) {
        // kosongkan jika ada logic yang mengganggu
    });
}
}