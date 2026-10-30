<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DLC + Unit Pasif (Unit Pasif, Bacaan Unit, Spare 3%, Pasif Manual) untuk modul Kalender.
 * Semua tabel berawalan "kalender_" agar terpisah dari data Majalah.
 * Kolom "edisi" dipakai sebagai kunci periode (contoh: 2027).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- DLC
        Schema::create('kalender_dlc_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20);
            $table->string('judul', 100)->nullable();
            $table->string('periode', 50);
            $table->string('bulan', 20)->nullable();
            $table->year('tahun')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->string('no_ps')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kalender_dlc_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dlc_periode_id')->constrained('kalender_dlc_periodes')->cascadeOnDelete();
            $table->string('nama_unit', 150);
            $table->unsignedInteger('qty')->default(0);
            $table->string('no_cab', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['dlc_periode_id', 'nama_unit']);
        });

        // ---------- Unit Pasif
        Schema::create('kalender_pasif_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20);
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan')->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('no_ps')->nullable();
            $table->string('grup', 5)->default('F');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kalender_pasif_pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasif_periode_id')->constrained('kalender_pasif_periodes')->cascadeOnDelete();
            $table->string('nama_unit');
            $table->string('no_cab')->nullable();
            $table->integer('qty')->default(0);
            $table->integer('bacaan_unit')->default(1);
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // ---------- Bacaan Unit
        Schema::create('kalender_bacaan_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20);
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan')->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('no_ps')->nullable();
            $table->string('grup', 5)->default('F');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('kalender_bacaan_pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bacaan_periode_id')->constrained('kalender_bacaan_periodes')->cascadeOnDelete();
            $table->string('nama_unit');
            $table->string('no_cab')->nullable();
            $table->integer('bacaan_unit')->default(0);
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // ---------- Spare Pasif 3%
        Schema::create('kalender_spare_pasifs', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20)->index();
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan', 20)->nullable();
            $table->string('tahun', 10)->nullable();
            $table->unsignedInteger('dlc_total')->default(0);
            $table->unsignedInteger('pasif_total')->default(0);
            $table->unsignedInteger('bacaan_total')->default(0);
            $table->unsignedInteger('grand_total')->default(0);
            $table->decimal('spare_raw', 12, 3)->default(0);
            $table->unsignedInteger('spare')->default(0);
            $table->unsignedInteger('lembar')->default(0);
            $table->string('grup', 5)->default('A')->index();
            $table->string('status', 20)->default('aktif')->index();
            $table->string('no_ps')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['edisi', 'status']);
        });

        // ---------- Pasif Manual (Create Manual)
        Schema::create('kalender_pasif_manual_periodes', function (Blueprint $table) {
            $table->id();
            $table->string('edisi', 20)->index();
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan', 20)->nullable();
            $table->string('tahun', 10)->nullable();
            $table->string('no_ps')->nullable();
            $table->string('grup', 5)->default('F');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('kalender_pasif_manual_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasif_manual_periode_id')
                ->constrained('kalender_pasif_manual_periodes')
                ->cascadeOnDelete();

            $table->unsignedInteger('no')->nullable();
            $table->string('id_pesan')->nullable()->index();
            $table->string('kode_pesan')->nullable()->index();
            $table->date('tgl_pesan')->nullable();
            $table->string('minggu', 20)->nullable();
            $table->string('nama_unit')->index();
            $table->string('label', 30)->nullable();
            $table->integer('jumlah')->default(0);
            $table->string('pesanan')->nullable();
            $table->text('note')->nullable();
            $table->string('keterangan')->nullable();

            $table->string('no_cab')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('status_kirim')->nullable();
            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_pasif_manual_transaksis');
        Schema::dropIfExists('kalender_pasif_manual_periodes');
        Schema::dropIfExists('kalender_spare_pasifs');
        Schema::dropIfExists('kalender_bacaan_pesanans');
        Schema::dropIfExists('kalender_bacaan_periodes');
        Schema::dropIfExists('kalender_pasif_pesanans');
        Schema::dropIfExists('kalender_pasif_periodes');
        Schema::dropIfExists('kalender_dlc_pesanan');
        Schema::dropIfExists('kalender_dlc_periodes');
    }
};
