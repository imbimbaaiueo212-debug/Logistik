<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('berat_paket', 15, 4)->nullable()->change();
            $table->decimal('berat_satuan', 12, 4)->nullable()->change();
            $table->decimal('harga_beli', 18, 2)->nullable()->change();
            $table->decimal('harga_jual', 18, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('berat_paket')->nullable()->change();
            $table->integer('berat_satuan')->nullable()->change();
            $table->integer('harga_beli')->nullable()->change();
            $table->integer('harga_jual')->nullable()->change();
        });
    }
};