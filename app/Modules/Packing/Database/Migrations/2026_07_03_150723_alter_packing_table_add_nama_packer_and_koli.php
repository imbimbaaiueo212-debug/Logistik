<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packing', function (Blueprint $table) {
            // Ubah nama kolom pic_packing menjadi nama_packer
            $table->renameColumn('pic_packing', 'nama_packer');

            // Tambah kolom baru koli
            $table->integer('koli')->nullable()->after('nama_packer');
        });
    }

    public function down()
    {
        Schema::table('packing', function (Blueprint $table) {
            $table->renameColumn('nama_packer', 'pic_packing');
            $table->dropColumn('koli');
        });
    }
};