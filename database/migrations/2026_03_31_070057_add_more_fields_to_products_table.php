<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->change();           // pastikan nullable
            $table->string('label')->nullable()->after('sku');     // SKU / Label

            $table->string('satuan')->nullable();
            $table->decimal('berat_satuan', 10, 3)->nullable();    // berat per satuan (kg)
            $table->decimal('berat_paket', 10, 3)->nullable();     // berat paket (kg)
            $table->integer('isi')->nullable();                    // isi per paket

            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();

            $table->string('status')->nullable();                  // aktif / nonaktif dll
            $table->enum('role', ['jual', 'tidak_dijual', 'stock'])->nullable();

            $table->date('tanggal_rilis')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'label', 'satuan', 'berat_satuan', 'berat_paket', 'isi',
                'harga_beli', 'harga_jual', 'status', 'role', 'tanggal_rilis'
            ]);
        });
    }
};