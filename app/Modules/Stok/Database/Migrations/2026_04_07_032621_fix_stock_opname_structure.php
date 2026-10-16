<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('stock_opname_items', function (Blueprint $table) {

        // ❗ ubah physical_qty jadi nullable
        $table->integer('physical_qty')->nullable()->change();

        // ❗ selisih juga nullable
        $table->integer('selisih')->nullable()->change();
    });

    Schema::table('stock_opnames', function (Blueprint $table) {

        // 🔥 tambahkan snapshot time
        $table->timestamp('snapshot_at')->nullable()->after('warehouse_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('stock_opname_items', function (Blueprint $table) {
        $table->integer('physical_qty')->default(0)->change();
        $table->integer('selisih')->default(0)->change();
    });

    Schema::table('stock_opnames', function (Blueprint $table) {
        $table->dropColumn('snapshot_at');
    });
}
};
