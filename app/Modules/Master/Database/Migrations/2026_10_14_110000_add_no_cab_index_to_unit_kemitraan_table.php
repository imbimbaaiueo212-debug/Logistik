<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'unit_kemitraan_no_cab_index';

    private function indexExists(): bool
    {
        return count(DB::select(
            'SHOW INDEX FROM unit_kemitraan WHERE Key_name = ?',
            [$this->indexName]
        )) > 0;
    }

    public function up(): void
    {
        // Aman dijalankan walau index sudah dibuat manual lewat phpMyAdmin
        if (! $this->indexExists()) {
            Schema::table('unit_kemitraan', function (Blueprint $table) {
                $table->index('no_cab', $this->indexName);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists()) {
            Schema::table('unit_kemitraan', function (Blueprint $table) {
                $table->dropIndex($this->indexName);
            });
        }
    }
};
