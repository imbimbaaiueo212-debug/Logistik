<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan_majalah_kotamadya', function (Blueprint $table) {

            // Relasi ke pesanan majalah utama (opsional)
            $table->unsignedBigInteger('pesanan_majalah_id')
                  ->nullable()
                  ->after('id');

            // Nama Kotamadya / Kabupaten
            $table->string('nama_kotamadya')
                  ->nullable()
                  ->after('pesanan_majalah_id');

            // Contact Person
            $table->string('contact_person')
                  ->nullable()
                  ->after('nama_kotamadya');

            // Telepon Contact Person
            $table->string('telepon_contact_person')
                  ->nullable()
                  ->after('contact_person');

            // Urutan tampil
            $table->unsignedInteger('urutan')
                  ->default(0)
                  ->after('telepon_contact_person');

            // Index untuk pencarian
            $table->index('nama_kotamadya');
            $table->index('pesanan_majalah_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_majalah_kotamadya', function (Blueprint $table) {
            $table->dropIndex(['nama_kotamadya']);
            $table->dropIndex(['pesanan_majalah_id']);

            $table->dropColumn([
                'pesanan_majalah_id',
                'nama_kotamadya',
                'contact_person',
                'telepon_contact_person',
                'urutan',
            ]);
        });
    }
};