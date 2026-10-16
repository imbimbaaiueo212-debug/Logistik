<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_kemitraan', function (Blueprint $table) {
            // Memo
            $table->string('status_unit')->nullable()->after('akun_media_sosial_unit_bimba_aiueo');
            $table->text('pdf_memo')->nullable();
            $table->text('update_pdf_memo')->nullable();
            $table->dateTime('last_updated_memo')->nullable();
            $table->string('version_memo')->nullable();
            $table->string('kirim_email_memo')->nullable();
            $table->dateTime('tgl_kirim_email_memo')->nullable();

            // Marketing
            $table->string('nama_marketing_')->nullable();

            // Alamat Mitra
            $table->string('no_rumah')->nullable();
            $table->string('rt_mitra')->nullable();
            $table->string('rw_mitra')->nullable();
            $table->string('kel_mitra')->nullable();
            $table->string('kec_mitra')->nullable();
            $table->string('kota_mitra')->nullable();
            $table->string('provinsi_mitra')->nullable();
            $table->string('kode_pos_mitra')->nullable();

            // Email Marketing
            $table->string('email_marketing_')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('unit_kemitraan', function (Blueprint $table) {
            $table->dropColumn([
                'status_unit',
                'pdf_memo',
                'update_pdf_memo',
                'last_updated_memo',
                'version_memo',
                'kirim_email_memo',
                'tgl_kirim_email_memo',
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
            ]);
        });
    }
};