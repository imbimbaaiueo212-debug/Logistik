<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_kalender_mismatches', function (Blueprint $table) {
            $table->id();
            $table->string('no_cab', 20)->index();
            $table->string('nama_excel')->nullable();
            $table->string('nama_master')->nullable();
            // 'import_kabupaten' | 'import_kotamadya' | 'import_puw1' | 'sync'
            $table->string('sumber')->nullable();
            $table->unsignedBigInteger('pesanan_kalender_id')->nullable()->index();
            $table->string('periode', 20)->nullable(); // 2026-11
            $table->boolean('is_resolved')->default(false)->index();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Cegah duplikat no_cab + periode + sumber
            $table->unique(['no_cab', 'periode', 'sumber'], 'unique_mismatch_kalender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_kalender_mismatches');
    }
};
