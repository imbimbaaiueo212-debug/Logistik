<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Beri nol di depan No Cab angka murni sampai 5 digit.
     * 340 -> 00340, 4340 -> 04340. Yang sudah 5 digit atau lebih tidak diubah.
     * Baris yang sudah soft delete ikut diperbaiki.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE unit_kemitraan
            SET no_cab = LPAD(TRIM(no_cab), 5, '0')
            WHERE no_cab IS NOT NULL
              AND TRIM(no_cab) REGEXP '^[0-9]+$'
              AND CHAR_LENGTH(TRIM(no_cab)) < 5
        ");

        // Tabel matching menyimpan salinan No Cab unit, ikut diseragamkan
        if (Schema::hasTable('matching_user_export')) {
            DB::statement("
                UPDATE matching_user_export
                SET no_cab = LPAD(TRIM(no_cab), 5, '0')
                WHERE no_cab IS NOT NULL
                  AND TRIM(no_cab) REGEXP '^[0-9]+$'
                  AND CHAR_LENGTH(TRIM(no_cab)) < 5
            ");
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: nol di depan tidak bisa dibedakan dari data asli.
    }
};
