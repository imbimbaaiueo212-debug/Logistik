<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKemitraan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_kemitraan';

    /**
     * Primary key adalah id_record (contoh: MG2420, MG5114)
     */
    protected $primaryKey = 'id_record';

    /**
     * Bukan auto-increment
     */
    public $incrementing = false;

    /**
     * Tipe primary key string
     */
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi mass-assignment
     */
    protected $fillable = [
        // Identitas Unit
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

        // Data Mitra
        'no_induk_mitra',
        'nama_mitra',
        'email',
        'no_hp',
        'foto',

        // Rekening
        'bank',
        'no_rekening',
        'atas_nama',

        // Akta & Lisensi
        'no_akta',
        'tgl_akta',
        'nilai_lisensi',
        'persen_mitra',
        'persen_ypai',

        // Periode Kontrak
        'awal',
        'akhir',
        'perpanjang',
        'tutup',

        // Pembayaran & VA
        'jmp',
        'lpm',
        'pengembalian',
        'tanggal',
        'va_bca',
        'va_mandiri_royalti',
        'va_mandiri_lisensi',
        'marketing',
        'koorwil_kpk_sos',

        // Catatan & Update
        'detail',
        'note',
        'updated_by',
        'last_updated',
        'history',
        'valid',
        'level_user',

        // Sisa Kontrak
        'sisa_3',
        'sisa_1',
        'sisa_2',
        'sisa_4',
        'sisa_f',
        'masa_kontrak',
        'sisa',
        'sisa_rr',

        // Perubahan & Kontrak
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

        // Tanda / Alias
        'awal_',
        'awal_kontrak_',
        'awal_tanda',
        'akhir_tanda',
        'perpanjangan_tanda',
        'tutup_tanda',

        // Vendor / Stokis
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

        // Lampiran & Email
        'len_perubahan_unit',
        'no_cab_bimba_unit',
        'lampiran_jarak_stokis_1',
        'lampiran_jarak_stokis_2',
        'keterangan_stokis_1',
        'keterangan_stokis_2',
        'kirim_email_lisensi',

        // PDF & Version tambahan
        'pdf_',
        'update_pdf_',
        'last_updated__',
        'version_',
        'awal_kontrak__',
        'akhir_kontrak__',

        // Update Jakarta
        'jakarta',
        'tanggal_update',
        'tanggal_update__',
        'masa_kontrak_____',
        'jika_maka',
        'related_perpanjang_kontraks',

        // Status Ops
        'cabang_unit_bimba',
        'status_ops_unit_bimba',
        'status_ops_vendor_1',
        'status_ops_vendor_2',

        // Dokumen Tambahan
        'dokumen_tambahan_1',
        'dokumen_tambahan_2',
        'dokumen_tambahan_3',

        // Media Sosial
        'akun_facebook',
        'akun_instagram',
        'akun_media_sosial_unit_bimba_aiueo',

        // Pengelolaan (hasil mapping)
        'status_pengelolaan',
        'mitra_pengelolaan',

        // Memo
        'status_unit',
        'pdf_memo',
        'update_pdf_memo',
        'last_updated_memo',
        'version_memo',
        'kirim_email_memo',
        'tgl_kirim_email_memo',

        // Marketing & Alamat Mitra Detail
        'nama_marketing_',
        'no_rumah',
        'rt_mitra',
        'rw_mitra',
        'kel_mitra',
        'kec_mitra',
        'kota_mitra',
        'provinsi_mitra',
        'kode_pos_mitra',
        'email_marketing_',
    ];

    /**
     * Casting tipe data
     * Semua date diganti ke datetime agar lebih toleran terhadap format Y-m-d H:i:s
     */
    protected $casts = [
        // Tanggal / Waktu
        'tgl_akta'              => 'datetime',
        'awal'                  => 'datetime',
        'akhir'                 => 'datetime',
        'perpanjang'            => 'datetime',
        'tutup'                 => 'datetime',
        'tanggal'               => 'datetime',
        'awal_kontrak'          => 'datetime',
        'akhir_kontrak'         => 'datetime',
        'awal_'                 => 'datetime',
        'awal_kontrak_'         => 'datetime',
        'awal_tanda'            => 'datetime',
        'akhir_tanda'           => 'datetime',
        'perpanjangan_tanda'    => 'datetime',
        'tutup_tanda'           => 'datetime',
        'awal_kontrak__'        => 'datetime',
        'akhir_kontrak__'       => 'datetime',
        'last_updated'          => 'datetime',
        'last_updated_'         => 'datetime',
        'last_updated__'        => 'datetime',
        'last_updated_memo'     => 'datetime',
        'tanggal_update'        => 'datetime',
        'tanggal_update__'      => 'datetime',
        'tgl_kirim_email_memo'  => 'datetime',

        // Decimal
        'nilai_lisensi'             => 'decimal:2',
        'persen_mitra'              => 'decimal:2',
        'persen_ypai'               => 'decimal:2',
        'sisa_3'                    => 'decimal:2',
        'sisa_1'                    => 'decimal:2',
        'sisa_2'                    => 'decimal:2',
        'sisa_4'                    => 'decimal:2',
        'sisa_f'                    => 'decimal:2',
        'sisa_rr'                   => 'decimal:2',
        'lampiran_jarak_stokis_1'   => 'decimal:4',
        'lampiran_jarak_stokis_2'   => 'decimal:4',
    ];

    /**
     * Relasi ke MatchingUserExport
     */
    public function matchingUserExport()
    {
        return $this->hasOne(MatchingUserExport::class, 'unit_kemitraan_id', 'id_record');
    }

    /**
     * Scope contoh (opsional)
     */
    public function scopeAktif($query)
    {
        return $query->where('ops', 'Active')
                     ->orWhere('status_pengelolaan', 'Unit Aktif');
    }

    public function scopeClosed($query)
    {
        return $query->where('ops', 'Closed');
    }
}