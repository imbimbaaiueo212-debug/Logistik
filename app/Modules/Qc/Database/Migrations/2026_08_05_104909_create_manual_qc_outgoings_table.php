<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_qc_outgoings', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('manual_picking_id')
                  ->constrained('manual_pickings')
                  ->onDelete('cascade');

            $table->string('no_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('grup')->nullable();
            $table->string('kategori_order')->nullable();
            
            $table->enum('status_qc', ['Pending', 'Lolos', 'Reject', 'Revisi'])->default('Pending');
            $table->string('pic_qc')->nullable();
            $table->string('kode_qc')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('tgl_qc')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_qc_outgoings');
    }
};