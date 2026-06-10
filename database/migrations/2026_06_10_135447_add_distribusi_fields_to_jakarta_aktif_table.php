<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            
            // Cek dulu sebelum menambah kolom
            if (!Schema::hasColumn('jakarta_aktif', 'service_pengiriman')) {
                $table->string('service_pengiriman')->nullable();
            }
            
            if (!Schema::hasColumn('jakarta_aktif', 'tracking_number')) {
                $table->string('tracking_number')->nullable();
            }
            
            if (!Schema::hasColumn('jakarta_aktif', 'distribusi_manual')) {
                $table->enum('distribusi_manual', ['Kirim', 'Ambil sendiri'])->default('Kirim');
            }
            
            if (!Schema::hasColumn('jakarta_aktif', 'nama_distributor')) {
                $table->string('nama_distributor')->nullable();
            }
            
            if (!Schema::hasColumn('jakarta_aktif', 'tgl_distribusi')) {
                $table->date('tgl_distribusi')->nullable();
            }
            
            if (!Schema::hasColumn('jakarta_aktif', 'status_distribusi')) {
                $table->string('status_distribusi')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            $table->dropColumn([
                'service_pengiriman',
                'tracking_number',
                'distribusi_manual',
                'nama_distributor',
                'tgl_distribusi',
                'status_distribusi'
            ]);
        });
    }
};