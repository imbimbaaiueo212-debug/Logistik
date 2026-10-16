<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasif_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20);                 // M159
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();       // Juni 2026
            $table->string('bulan')->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('no_ps')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pasif_pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasif_periode_id')->constrained('pasif_periodes')->cascadeOnDelete();
            $table->string('nama_unit');
            $table->string('no_cab')->nullable();
            $table->integer('qty')->default(0);          // kolom MAJALAH
            $table->integer('bacaan_unit')->default(1);
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasif_pesanans');
        Schema::dropIfExists('pasif_periodes');
    }
};