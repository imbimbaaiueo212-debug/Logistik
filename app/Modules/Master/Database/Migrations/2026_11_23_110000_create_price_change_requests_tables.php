<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header pengajuan + data surat permohonan
        Schema::create('price_change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();              // PH-2026-0001
            $table->text('alasan');
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->string('surat_path');                        // file bukti (disk local, privat)
            $table->string('surat_nama');
            $table->string('status', 20)->default('menunggu')->index(); // menunggu|diterapkan|ditolak|dibatalkan
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_review')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });

        // Rincian: produk apa, harga lama -> harga baru (NULL = tidak diubah)
        Schema::create('price_change_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->decimal('harga_beli_lama', 15, 2)->nullable();
            $table->decimal('harga_beli_baru', 15, 2)->nullable();
            $table->decimal('harga_jual_lama', 15, 2)->nullable();
            $table->decimal('harga_jual_baru', 15, 2)->nullable();
            $table->timestamps();
        });

        // Kronologi (timeline) setiap pengajuan
        Schema::create('price_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi', 30);                          // diajukan|disetujui|diterapkan|ditolak|dibatalkan
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Penanda di Master Product: "harga sudah update"
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('harga_diubah_pada')->nullable();
            $table->unsignedBigInteger('harga_request_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['harga_diubah_pada', 'harga_request_id']);
        });
        Schema::dropIfExists('price_change_logs');
        Schema::dropIfExists('price_change_request_items');
        Schema::dropIfExists('price_change_requests');
    }
};
