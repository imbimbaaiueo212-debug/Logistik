<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('kode_supplier')
                  ->unique()
                  ->nullable()
                  ->after('id');                    // SKU / Kode Supplier

            $table->string('nama_supplier')
                  ->after('kode_supplier');         // Nama Supplier yang lebih jelas

            // Ubah kolom lama agar konsisten (opsional tapi direkomendasikan)
            $table->renameColumn('name', 'old_name'); // backup dulu nama lama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['kode_supplier', 'nama_supplier']);
            $table->renameColumn('old_name', 'name'); // kembalikan nama lama
        });
    }
};