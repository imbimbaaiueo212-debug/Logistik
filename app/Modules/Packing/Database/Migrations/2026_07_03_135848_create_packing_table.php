<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packing', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Picking & QC
            $table->foreignId('picking_id')->unique()->constrained('pickings')->cascadeOnDelete();
            $table->foreignId('qc_outgoing_id')->nullable()->constrained('qc_outgoings')->nullOnDelete();

            // Data dasar dari sebelumnya (sama seperti QC)
            $table->string('no_pl')->nullable();
            $table->date('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('pengiriman')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->nullable();
            $table->date('tgl_estimasi')->nullable();

            // Field Packing Baru
            $table->decimal('berat', 10, 2)->nullable();           // Berat estimasi / rencana
            $table->decimal('berat_aktual', 10, 2)->nullable();    // Berat setelah packing
            $table->date('tgl_packing')->nullable();
            
            $table->string('pic_packing')->nullable();
            $table->string('status_packing')->default('Pending'); // Pending, Sedang Packing, Selesai, Batal

            $table->text('keterangan_packing')->nullable();

            $table->foreignId('packing_by')->nullable()->constrained('users');
            $table->timestamp('packing_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packing');
    }
};