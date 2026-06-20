<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unit_kemitraan', function (Blueprint $table) {
            $table->id('id_record');
            
            $table->string('no_cab', 100)->nullable();
            $table->string('bimba_aiueo_unit', 150)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('ops', 100)->nullable();
            $table->string('no_telp_unit', 50)->nullable();
            $table->string('email_unit', 150)->nullable();
            $table->text('alamat_unit')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kab_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kel_desa', 100)->nullable();
            $table->string('kode_pos', 20)->nullable();
            $table->string('titik_koordinat', 100)->nullable();
            $table->decimal('koordinat_s', 15, 8)->nullable();
            $table->decimal('koordinat_e', 15, 8)->nullable();

            $table->string('no_induk_mitra', 100)->nullable();
            $table->string('nama_mitra', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 50)->nullable();
            $table->string('foto', 255)->nullable();

            $table->string('bank', 100)->nullable();
            $table->string('no_rekening', 100)->nullable();
            $table->string('atas_nama', 150)->nullable();

            $table->string('no_akta', 100)->nullable();
            $table->date('tgl_akta')->nullable();
            $table->decimal('nilai_lisensi', 20, 2)->nullable();
            $table->decimal('persen_mitra', 8, 2)->nullable();
            $table->decimal('persen_ypai', 8, 2)->nullable();

            $table->date('awal')->nullable();
            $table->date('akhir')->nullable();
            $table->date('perpanjang')->nullable();
            $table->date('tutup')->nullable();

            $table->string('jmp', 100)->nullable();
            $table->string('lpm', 100)->nullable();
            $table->string('pengembalian', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('va_bca', 100)->nullable();
            $table->string('va_mandiri_royalti', 100)->nullable();
            $table->string('va_mandiri_lisensi', 100)->nullable();
            $table->string('marketing', 100)->nullable();
            $table->string('koorwil_kpk_sos', 100)->nullable();

            $table->text('detail')->nullable();
            $table->text('note')->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->text('history')->nullable();
            $table->string('valid', 100)->nullable();
            $table->string('level_user', 100)->nullable();

            $table->decimal('sisa_3', 15, 2)->nullable();
            $table->decimal('sisa_1', 15, 2)->nullable();
            $table->decimal('sisa_2', 15, 2)->nullable();
            $table->decimal('sisa_4', 15, 2)->nullable();
            $table->decimal('sisa_f', 15, 2)->nullable();
            $table->integer('masa_kontrak')->nullable();
            $table->decimal('sisa', 15, 2)->nullable();
            $table->decimal('sisa_rr', 15, 2)->nullable();

            $table->string('no', 100)->nullable();
            $table->string('lokasi_', 100)->nullable();
            $table->string('kategori_perubahan', 100)->nullable();
            $table->string('awal_kontrak', 100)->nullable();
            $table->string('akhir_kontrak', 100)->nullable();

            $table->string('pdf', 255)->nullable();
            $table->string('update_pdf', 255)->nullable();
            $table->string('last_updated_', 100)->nullable();
            $table->string('version', 50)->nullable();
            $table->text('related_pengajuan_perubahans')->nullable();

            // Kolom dengan titik
            $table->string('awal_', 100)->nullable();
            $table->string('awal_kontrak_', 100)->nullable();
            $table->string('awal_tanda', 100)->nullable();
            $table->string('akhir_tanda', 100)->nullable();
            $table->string('perpanjangan_tanda', 100)->nullable();
            $table->string('tutup_tanda', 100)->nullable();

            $table->string('vendor_stokis_1', 150)->nullable();
            $table->string('vendor_stokis_2', 150)->nullable();
            $table->text('sisa_summary')->nullable();
            $table->text('notifikasi_sisa_kontrak_lisensi')->nullable();

            $table->text('alamat_saat_ini')->nullable();
            $table->text('related_pengajuan_perubahans_by_no_cab')->nullable();
            $table->text('alamat_mitra')->nullable();
            $table->string('nama_mitra_tanda', 150)->nullable();
            $table->string('no_hp_mitra', 50)->nullable();
            $table->string('email_mitra', 150)->nullable();

            $table->string('len_perubahan_unit', 100)->nullable();
            $table->string('no_cab_bimba_unit', 100)->nullable();
            $table->decimal('lampiran_jarak_stokis_1', 10, 2)->nullable();
            $table->decimal('lampiran_jarak_stokis_2', 10, 2)->nullable();
            $table->text('keterangan_stokis_1')->nullable();
            $table->text('keterangan_stokis_2')->nullable();
            $table->string('kirim_email_lisensi', 100)->nullable();

            $table->string('pdf_', 255)->nullable();
            $table->string('update_pdf_', 255)->nullable();
            $table->string('last_updated__', 100)->nullable();
            $table->string('version_', 50)->nullable();
            $table->date('awal_kontrak__')->nullable();
            $table->date('akhir_kontrak__')->nullable();
            $table->string('jakarta', 100)->nullable();
            $table->timestamp('tanggal_update')->nullable();
            $table->timestamp('tanggal_update__')->nullable();
            $table->string('masa_kontrak_____', 100)->nullable();
            $table->string('jika_maka', 100)->nullable();
            $table->string('related_perpanjang_kontraks', 255)->nullable();
            $table->string('cabang_unit_bimba', 150)->nullable();
            $table->string('status_ops_unit_bimba', 100)->nullable();
            $table->string('status_ops_vendor_1', 100)->nullable();
            $table->string('status_ops_vendor_2', 100)->nullable();

            $table->string('dokumen_tambahan_1', 255)->nullable();
            $table->string('dokumen_tambahan_2', 255)->nullable();
            $table->string('dokumen_tambahan_3', 255)->nullable();

            $table->string('akun_facebook', 255)->nullable();
            $table->string('akun_instagram', 255)->nullable();
            $table->string('akun_media_sosial_unit_bimba_aiueo', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unit_kemitraan');
    }
};