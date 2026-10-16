<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_pasif', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->unsignedBigInteger('packing_pasif_id')->nullable()->index();
            $table->unsignedBigInteger('picking_pasif_id')->nullable()->index();
            $table->unsignedBigInteger('qc_outgoing_pasif_id')->nullable()->index();

            // Data utama
            $table->string('no_pl')->nullable()->index();
            $table->string('no_ps')->nullable();
            $table->date('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->nullable()->default(0);
            $table->date('tgl_estimasi')->nullable();

            // Pengiriman
            $table->string('jenis_pengiriman')->nullable(); // diambil_sendiri / ekspedisi
            $table->string('ekspedisi')->nullable();
            $table->string('service')->nullable();
            $table->string('pengiriman')->nullable();

            // Berat & koli
            $table->decimal('berat', 10, 2)->nullable();
            $table->decimal('berat_aktual', 10, 2)->nullable();
            $table->string('koli')->nullable();

            // Status distribusi
            $table->string('status_distribusi')->default('Pending'); // Pending / Proses / Selesai
            $table->string('status_pengiriman')->nullable(); // belum_pickup / sudah_pickup / terkirim
            $table->string('no_resi')->nullable();
            $table->timestamp('distribution_at')->nullable();

            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_pasif');
    }
};