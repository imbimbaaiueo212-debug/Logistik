<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_sertifikat_realisasis', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('manual_sertifikat_order_id')->nullable()->index();
            $table->string('no_pl', 40)->nullable()->index()
                  ->comment('Contoh: PL-MS-250902-0001');
            $table->date('tgl_turun_pl')->nullable()->index();

            $table->string('nama_unit', 150)->nullable();
            $table->string('billing_last_name', 50)->nullable();
            $table->string('billing_company', 150)->nullable();

            $table->string('kategori_order', 50)->nullable()
                  ->comment('Sertifikat');
            $table->string('nama_barang', 255)->nullable();

            $table->string('rekap_number', 40)->nullable()->index()
                  ->comment('Contoh: RAMS-250902-0001');

            $table->dateTime('picking_printed_at')->nullable();
            $table->dateTime('printed_at')->nullable();

            $table->timestamps();

            // Foreign key (opsional, bisa diaktifkan jika mau strict)
            // $table->foreign('manual_sertifikat_order_id')
            //       ->references('id')
            //       ->on('manual_sertifikat_orders')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_sertifikat_realisasis');
    }
};