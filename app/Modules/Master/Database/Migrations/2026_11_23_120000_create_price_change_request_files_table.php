<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_change_request_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->index();
            $table->string('jenis', 20)->default('surat'); // surat | pendukung
            $table->string('path');
            $table->string('nama');
            $table->unsignedInteger('ukuran')->nullable();
            $table->timestamps();
        });

        // Pindahkan surat lama (1 file per pengajuan) ke tabel baru
        $lama = DB::table('price_change_requests')->whereNotNull('surat_path')->get(['id', 'surat_path', 'surat_nama']);
        foreach ($lama as $r) {
            DB::table('price_change_request_files')->insert([
                'request_id' => $r->id, 'jenis' => 'surat', 'path' => $r->surat_path, 'nama' => $r->surat_nama,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_change_request_files');
    }
};
