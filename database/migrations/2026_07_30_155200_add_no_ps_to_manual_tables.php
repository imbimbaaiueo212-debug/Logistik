<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // manual_orders
        if (Schema::hasTable('manual_orders') && !Schema::hasColumn('manual_orders', 'no_ps')) {
            Schema::table('manual_orders', function (Blueprint $table) {
                $table->string('no_ps', 50)->nullable()->after('order_id');
            });
        }

        // manual_realisasis
        if (Schema::hasTable('manual_realisasi') && !Schema::hasColumn('manual_realisasi', 'no_ps')) {
            Schema::table('manual_realisasi', function (Blueprint $table) {
                $table->string('no_ps', 50)->nullable()->after('no_pl');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('manual_orders') && Schema::hasColumn('manual_orders', 'no_ps')) {
            Schema::table('manual_orders', function (Blueprint $table) {
                $table->dropColumn('no_ps');
            });
        }

        if (Schema::hasTable('manual_realisasi') && Schema::hasColumn('manual_realisasi', 'no_ps')) {
            Schema::table('manual_realisasi', function (Blueprint $table) {
                $table->dropColumn('no_ps');
            });
        }
    }
};