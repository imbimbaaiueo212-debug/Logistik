<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Hapus kolom old_name
            if (Schema::hasColumn('suppliers', 'old_name')) {
                $table->dropColumn('old_name');
            }

            // Hanya ubah nullable menjadi required (tidak perlu tambah unique lagi)
            $table->string('kode_supplier')->nullable(false)->change();
            $table->string('nama_supplier')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('old_name')->nullable(); // untuk rollback

            $table->string('kode_supplier')->unique()->nullable()->change();
            $table->string('nama_supplier')->nullable()->change();
        });
    }
};