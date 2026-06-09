<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bimbashop_orders', function (Blueprint $table) {
            $table->id();
            
            // Order Identitas
            $table->string('order_id')->unique();           // kolom "ID"
            $table->timestamp('order_date')->nullable();
            $table->string('status')->nullable();
            $table->decimal('order_total', 15, 2)->default(0);
            $table->decimal('ship_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('refunded_total', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->decimal('order_weight', 10, 2)->nullable();

            // Item
            $table->string('item_sku')->nullable();
            $table->text('item_name');
            $table->decimal('item_price', 15, 2);
            $table->integer('item_qty')->default(1);

            // Billing
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();

            // Shipping
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->text('shipping_address_1')->nullable();
            $table->text('shipping_address_2')->nullable();
            $table->string('shipping_city')->nullable();

            $table->string('partial_pay_wallet_id')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bimbashop_orders');
    }
};