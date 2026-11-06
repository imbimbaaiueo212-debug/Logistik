<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris per edisi kalender (KLD27 = kalender 2027, KLD28 = kalender 2028, dst.)
        Schema::create('kalender_puw_periode', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 10)->unique();          // KLD27
            $table->unsignedSmallInteger('tahun');           // 2027
            $table->unsignedTinyInteger('qty')->default(2);  // 1 / 2 / 3, berlaku untuk semua unit
            $table->string('jenis_unit')->nullable();        // filter sumber: kolom status unit_kemitraan (dipisah koma)
            $table->string('no_induk_mitra')->nullable();    // filter sumber: mis. PUW001
            $table->boolean('hanya_aktif')->default(true);
            $table->timestamps();
        });

        // Salinan data unit saat edisi dibuat (tidak ikut berubah bila Database Unit berubah di tahun berikutnya)
        Schema::create('kalender_puw_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('kalender_puw_periode')->cascadeOnDelete();
            $table->unsignedInteger('no')->default(0);
            $table->string('id_record')->nullable();
            $table->string('no_cab', 50)->nullable();
            $table->string('nama_unit');
            $table->string('status', 100)->nullable();
            $table->string('ops', 100)->nullable();
            $table->string('no_telp_unit', 100)->nullable();
            $table->text('alamat_unit')->nullable();
            $table->string('rt', 20)->nullable();
            $table->string('rw', 20)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kab_kota', 150)->nullable();
            $table->string('kecamatan', 150)->nullable();
            $table->string('kel_desa', 150)->nullable();
            $table->string('kode_pos', 20)->nullable();
            $table->unsignedTinyInteger('qty')->default(2);
            $table->timestamps();

            $table->index(['periode_id', 'no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_puw_unit');
        Schema::dropIfExists('kalender_puw_periode');
    }
};
