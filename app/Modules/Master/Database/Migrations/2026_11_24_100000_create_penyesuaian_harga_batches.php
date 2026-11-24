<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu kali import = satu batch: surat permohonan + data perubahan harga, menunggu ACC
        Schema::create('penyesuaian_harga_batches', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->date('berlaku_mulai')->nullable();
            $table->text('catatan')->nullable();                          // catatan pengimpor
            $table->string('status', 20)->default('menunggu')->index();   // menunggu | dipublikasi | ditolak
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_review')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Berkas asli yang diupload (surat permohonan + PDF data perubahan), disimpan privat
        Schema::create('penyesuaian_harga_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->index();
            $table->string('jenis', 20);                                   // surat | modul | seragam
            $table->string('path');
            $table->string('nama');
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->timestamps();
        });

        Schema::table('penyesuaian_harga', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->nullable()->after('id')->index(); // NULL = data bawaan lama
            $table->decimal('harga_jual_baru', 15, 2)->nullable();                    // snapshot harga jual saat dipublikasi
            $table->dropUnique(['grup', 'no']);
            $table->unique(['batch_id', 'grup', 'no']);
        });
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga', function (Blueprint $table) {
            $table->dropUnique(['batch_id', 'grup', 'no']);
            $table->dropColumn(['batch_id', 'harga_jual_baru']);
            $table->unique(['grup', 'no']);
        });
        Schema::dropIfExists('penyesuaian_harga_files');
        Schema::dropIfExists('penyesuaian_harga_batches');
    }
};
