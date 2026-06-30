<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKemitraan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_kemitraan';

    protected $primaryKey = 'id_record';

    public $incrementing = false;

    protected $keyType = 'string';
    
    // Jika primary key bukan integer/auto-increment
    // public $incrementing = true;
    // protected $keyType = 'int';

    protected $fillable = [
        'id_record',
        'no_cab',
        'bimba_aiueo_unit',
        'status',
        'ops',
        'no_telp_unit',
        'email_unit',
        'alamat_unit',
        'rt',
        'rw',
        'provinsi',
        'kab_kota',
        'kecamatan',
        'kel_desa',
        'kode_pos',
        'titik_koordinat',
        'koordinat_s',
        'koordinat_e',

        'no_induk_mitra',
        'nama_mitra',
        'email',
        'no_hp',
        'foto',

        'bank',
        'no_rekening',
        'atas_nama',

        'no_akta',
        'tgl_akta',
        'nilai_lisensi',
        'persen_mitra',
        'persen_ypai',

        'awal',
        'akhir',
        'perpanjang',
        'tutup',

        'jmp',
        'lpm',
        'pengembalian',
        'tanggal',
        'va_bca',
        'va_mandiri_royalti',
        'va_mandiri_lisensi',
        'marketing',
        'koorwil_kpk_sos',

        'detail',
        'note',
        'updated_by',
        'last_updated',
        'history',
        'valid',
        'level_user',

        'sisa_3',
        'sisa_1',
        'sisa_2',
        'sisa_4',
        'sisa_f',
        'masa_kontrak',
        'sisa',
        'sisa_rr',

        'no',
        'lokasi_',
        'kategori_perubahan',
        'awal_kontrak',
        'akhir_kontrak',

        'pdf',
        'update_pdf',
        'last_updated_',
        'version',
        'related_pengajuan_perubahans',

        'awal_',
        'awal_kontrak_',
        'awal_tanda',
        'akhir_tanda',
        'perpanjangan_tanda',
        'tutup_tanda',

        'vendor_stokis_1',
        'vendor_stokis_2',
        'sisa_summary',
        'notifikasi_sisa_kontrak_lisensi',

        'alamat_saat_ini',
        'related_pengajuan_perubahans_by_no_cab',
        'alamat_mitra',
        'nama_mitra_tanda',
        'no_hp_mitra',
        'email_mitra',

        'len_perubahan_unit',
        'no_cab_bimba_unit',
        'lampiran_jarak_stokis_1',
        'lampiran_jarak_stokis_2',
        'keterangan_stokis_1',
        'keterangan_stokis_2',
        'kirim_email_lisensi',

        'pdf_',
        'update_pdf_',
        'last_updated__',
        'version_',
        'awal_kontrak__',
        'akhir_kontrak__',
        'jakarta',
        'tanggal_update',
        'tanggal_update__',
        'masa_kontrak_____',
        'jika_maka',
        'related_perpanjang_kontraks',
        'cabang_unit_bimba',
        'status_ops_unit_bimba',
        'status_ops_vendor_1',
        'status_ops_vendor_2',

        'dokumen_tambahan_1',
        'dokumen_tambahan_2',
        'dokumen_tambahan_3',

        'akun_facebook',
        'akun_instagram',
        'akun_media_sosial_unit_bimba_aiueo',
    ];

    protected $casts = [
        'tgl_akta' => 'date',
        'awal' => 'date',
        'akhir' => 'date',
        'perpanjang' => 'date',
        'tutup' => 'date',
        'tanggal' => 'date',
        'awal_kontrak' => 'date',           // meski tipe string di DB
        'akhir_kontrak' => 'date',
        'awal_kontrak__' => 'date',
        'akhir_kontrak__' => 'date',
        'tanggal_update' => 'datetime',
        'tanggal_update__' => 'datetime',
        'last_updated' => 'datetime',
        'koordinat_s' => 'decimal:8',
        'koordinat_e' => 'decimal:8',
        'nilai_lisensi' => 'decimal:2',
        'persen_mitra' => 'decimal:2',
        'persen_ypai' => 'decimal:2',
        'sisa_3' => 'decimal:2',
        'sisa_1' => 'decimal:2',
        'sisa_2' => 'decimal:2',
        'sisa_4' => 'decimal:2',
        'sisa_f' => 'decimal:2',
        'sisa' => 'string',
        'sisa_rr' => 'decimal:2',
        'lampiran_jarak_stokis_1' => 'decimal:2',
        'lampiran_jarak_stokis_2' => 'decimal:2',
    ];

    // Opsional: Jika ingin mengatur kolom yang tidak boleh di-mass assignment
    // protected $guarded = ['id_record'];
}