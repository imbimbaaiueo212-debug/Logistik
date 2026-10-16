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
    Schema::table('manual_orders', function (Blueprint $table) {
        $table->decimal('price', 15, 2)->nullable()->change();
        $table->decimal('total', 15, 2)->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('manual_orders', function (Blueprint $table) {
        $table->decimal('price', 15, 2)->nullable(false)->change();
        $table->decimal('total', 15, 2)->nullable(false)->change();
    });
}
};
