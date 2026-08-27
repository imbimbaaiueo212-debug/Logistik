<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // HEADER PERIODE PASIF MANUAL
        // =====================================================
        Schema::create('pasif_manual_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20)->index();          // contoh: 161 / M161
            $table->string('judul')->nullable();           // Majalah Edisi 161
            $table->string('periode')->nullable();         // 08-2026
            $table->string('bulan', 20)->nullable();       // Agustus
            $table->string('tahun', 10)->nullable();       // 2026
            $table->string('no_ps')->nullable();
            $table->string('grup', 5)->default('F');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // =====================================================
        // DETAIL TRANSAKSI PASIF MANUAL (sesuai Excel)
        // =====================================================
        Schema::create('pasif_manual_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasif_manual_periode_id')
                  ->constrained('pasif_manual_periodes')
                  ->onDelete('cascade');

            // Kolom sesuai Excel "Pemesanan Unit Pasif"
            $table->unsignedInteger('no')->nullable();                // No
            $table->string('id_pesan')->nullable()->index();          // 8639001
            $table->string('kode_pesan')->nullable()->index();        // PP863900
            $table->date('tgl_pesan')->nullable();                    // 15 Agustus 2026
            $table->string('minggu', 20)->nullable();                 // Ke-2
            $table->string('nama_unit')->index();                     // Nama Unit
            $table->string('label', 30)->nullable();                  // M161
            $table->integer('jumlah')->default(0);                    // Jumlah
            $table->string('pesanan')->nullable();                    // Majalah Edisi 161
            $table->text('note')->nullable();                         // Note
            $table->string('keterangan')->nullable();                 // Keterangan (no.memo)

            // Field tambahan
            $table->string('no_cab')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('status_kirim')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasif_manual_transaksis');
        Schema::dropIfExists('pasif_manual_periodes');
    }
};