<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stokis_mitra', function (Blueprint $table) {
            $table->id();
            $table->string('no_cab', 20)->unique()->index();
            
            $table->string('nama_stokis_db_kemitraan')->nullable();
            $table->string('nama_stokis_db_bimbashop')->nullable();
            
            $table->string('no_induk_mitra')->nullable();
            $table->string('nama_mitra')->nullable();
            
            $table->string('email')->nullable();
            $table->string('no_hp', 30)->nullable();
            
            $table->text('related_form_pembukaan_unit_aktif')->nullable();
            $table->text('related_formulir_kerjasama_english')->nullable();
            
            $table->string('db_kemitraan_db_bimbashop')->nullable();
            
            $table->text('related_unit_bimba_aiueo')->nullable();
            $table->text('related_formulir_kerjasama_mk_mm')->nullable();
            $table->text('related_pengajuan_perubahan')->nullable();
            
            $table->json('item_sku')->nullable();           // pakai json
            $table->string('ops_stokist')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stokis_mitra');
    }
};