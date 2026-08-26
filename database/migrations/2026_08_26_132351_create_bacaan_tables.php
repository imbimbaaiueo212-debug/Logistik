<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bacaan_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20);
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan')->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('no_ps')->nullable();          // ← No PS sendiri
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bacaan_pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bacaan_periode_id')->constrained('bacaan_periodes')->cascadeOnDelete();
            $table->string('nama_unit');
            $table->string('no_cab')->nullable();
            $table->integer('bacaan_unit')->default(0);   // qty bacaan
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bacaan_pesanans');
        Schema::dropIfExists('bacaan_periodes');
    }
};