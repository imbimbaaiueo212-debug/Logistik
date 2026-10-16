<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matching_user_export', function (Blueprint $table) {
            // Ubah tipe kolom dari unsignedBigInteger menjadi string
            $table->string('unit_kemitraan_id', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('matching_user_export', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_kemitraan_id')->change();
        });
    }
};