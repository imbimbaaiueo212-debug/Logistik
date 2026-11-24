<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            $table->string('judul', 150)->nullable()->after('id');   // kosong = otomatis "Perubahan Harga <Bulan Tahun berlaku>"
        });
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            $table->dropColumn('judul');
        });
    }
};
