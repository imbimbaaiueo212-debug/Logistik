<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_export_bimba_shop', function (Blueprint $table) {
            $table->id('ID');                    // Auto increment primary key
            
            $table->unsignedBigInteger('customer_id')->nullable();
            
            $table->string('user_login')->nullable();
            $table->string('user_pass')->nullable();
            $table->string('user_nicename')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_url')->nullable();
            
            $table->timestamp('user_registered')->nullable();
            $table->string('display_name')->nullable();
            
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            
            $table->integer('user_status')->default(0);
            $table->string('roles')->nullable();
            $table->string('nickname')->nullable();
            $table->text('description')->nullable();
            
            $table->string('rich_editing')->nullable()->default('true');
            $table->string('syntax_highlighting')->nullable()->default('true');
            $table->string('admin_color')->nullable()->default('fresh');
            
            $table->boolean('use_ssl')->nullable()->default(false);
            $table->boolean('show_admin_bar_front')->nullable()->default(true);
            $table->string('locale')->nullable()->default('');
            
            $table->integer('wp_user_level')->default(0);
            $table->text('dismissed_wp_pointers')->nullable();
            $table->boolean('show_welcome_panel')->nullable()->default(1);
            
            $table->text('session_tokens')->nullable();
            $table->timestamp('last_update')->nullable();
            
            // WooCommerce Fields
            $table->integer('orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('aov', 15, 2)->default(0); // Average Order Value
            
            // Billing
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();
            
            // Shipping
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->nullable();

            $table->timestamps();   // created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_export');
    }
};