<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickings', function (Blueprint $table) {
            
            if (!Schema::hasColumn('pickings', 'kategori_order')) {
                $table->string('kategori_order', 50)
                      ->nullable()
                      ->after('no_pl');
            }

        });
    }

    public function down(): void
    {
        Schema::table('pickings', function (Blueprint $table) {
            if (Schema::hasColumn('pickings', 'kategori_order')) {
                $table->dropColumn('kategori_order');
            }
        });
    }
};