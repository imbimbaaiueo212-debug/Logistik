<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('manual_orders', function (Blueprint $table) {

        // Billing
        $table->string('billing_first_name')->nullable();
        $table->string('billing_last_name')->nullable();

        // Shipping
        $table->string('shipping_first_name')->nullable();
        $table->string('shipping_last_name')->nullable();

        $table->text('shipping_address_1')->nullable();
        $table->text('shipping_address_2')->nullable();

        $table->string('shipping_city')->nullable();
        $table->string('shipping_state')->nullable();
        $table->string('shipping_postcode')->nullable();
        $table->string('shipping_country')->nullable();

        // Source
        $table->enum('source', ['manual', 'bimbashop'])
              ->default('manual');
    });
}

    public function down(): void
{
    Schema::table('manual_orders', function (Blueprint $table) {

        $table->dropColumn([
            'billing_first_name',
            'billing_last_name',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
            'shipping_country',
            'source',
        ]);

    });
}
};