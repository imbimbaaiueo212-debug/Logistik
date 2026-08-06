<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_distribution_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manual_packing_id')
                  ->constrained('manual_packings')
                  ->cascadeOnDelete();

            $table->foreignId('manual_picking_id')
                  ->nullable()
                  ->constrained('manual_pickings')
                  ->nullOnDelete();

            $table->string('no_pl')->nullable();
            $table->string('no_ps')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('grup')->nullable();
            $table->string('kategori_order')->nullable();

            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();
            $table->string('status_kirim')->nullable(); // Dikirim / Diambil

            $table->decimal('berat', 10, 2)->nullable();
            $table->decimal('berat_aktual', 10, 2)->nullable();
            $table->integer('koli')->nullable();

            $table->string('status_distribusi')->default('Pending'); // Pending / Proses / Selesai
            $table->string('no_resi')->nullable();
            $table->date('tgl_kirim')->nullable();
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_distribution_orders');
    }
};