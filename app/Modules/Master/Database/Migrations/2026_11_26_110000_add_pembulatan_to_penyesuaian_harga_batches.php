<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'pembulatan')) {
                // Kelipatan pembulatan harga jual (Rp). Kosong = bawaan 1.000
                $table->unsignedInteger('pembulatan')->nullable()->after('pengali_lain');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (Schema::hasColumn('penyesuaian_harga_batches', 'pembulatan')) {
                $table->dropColumn('pembulatan');
            }
        });
    }
};
