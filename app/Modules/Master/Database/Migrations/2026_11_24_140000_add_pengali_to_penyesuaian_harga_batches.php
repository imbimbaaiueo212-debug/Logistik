<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'pengali_modul')) {
                $table->decimal('pengali_modul', 6, 3)->nullable()->after('catatan');
            }
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'pengali_lain')) {
                $table->decimal('pengali_lain', 6, 3)->nullable()->after('pengali_modul');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            foreach (['pengali_modul', 'pengali_lain'] as $kolom) {
                if (Schema::hasColumn('penyesuaian_harga_batches', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
