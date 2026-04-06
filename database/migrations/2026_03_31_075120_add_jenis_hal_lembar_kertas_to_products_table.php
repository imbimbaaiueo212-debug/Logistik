<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('name');     // Jenis produk (Buku, Buku Tulis, dll)
            $table->integer('hal')->nullable()->after('jenis');     // Jumlah halaman
            $table->integer('lembar')->nullable()->after('hal');    // Jumlah lembar
            $table->string('kertas')->nullable()->after('lembar');  // Jenis kertas (HVS, Art Paper, dll)
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'hal', 'lembar', 'kertas']);
        });
    }
};