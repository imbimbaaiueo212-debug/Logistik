<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {

            // pastikan tipe sama
            $table->unsignedBigInteger('kategori_id')->nullable()->change();

            // tambahkan foreign key yang benar
            $table->foreign('kategori_id')
                  ->references('id')
                  ->on('categories') // ✅ BENAR
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
        });
    }
};