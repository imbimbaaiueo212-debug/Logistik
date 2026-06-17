<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('realisasi_aktif', function (Blueprint $table) {
            $table->id();
            $table->string('no_pl')->nullable();
            $table->date('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('pengiriman')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->string('nama_stokis')->nullable();
            $table->date('tgl_estimasi')->nullable();
            $table->integer('estimasi_hari')->nullable();
            $table->string('penyebut')->nullable();
            $table->string('pengambil')->nullable();
            $table->text('ket')->nullable();

            // Relasi ke data asal
            $table->foreignId('jakarta_aktif_id')->nullable()->constrained('jakarta_aktif')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('realisasi_aktif');
    }
};