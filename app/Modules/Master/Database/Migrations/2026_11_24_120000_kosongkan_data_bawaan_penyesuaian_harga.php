<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Data bawaan (104 item tanpa import) dikosongkan; tabel terisi hanya dari import PDF. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('penyesuaian_harga')->whereNull('batch_id')->delete();
    }

    public function down(): void
    {
        // data bawaan tidak dikembalikan
    }
};
