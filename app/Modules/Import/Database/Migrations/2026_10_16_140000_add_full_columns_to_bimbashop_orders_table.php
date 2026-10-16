<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kolom tambahan agar sama dengan file export biMBA Shop (versi lengkap). */
    private array $kolom = [
        'billing_address_1' => 'text',
        'billing_address_2' => 'text',
        'billing_city'      => 'string',
        'billing_state'     => 'string',
        'billing_postcode'  => 'string',
        'billing_country'   => 'string',
        'billing_phone'     => 'string',
        'billing_email'     => 'string',
        'shipping_company'  => 'string',
        'shipping_state'    => 'string',
        'shipping_postcode' => 'string',
        'shipping_country'  => 'string',
        'shipping_phone'    => 'string',
    ];

    public function up(): void
    {
        Schema::table('bimbashop_orders', function (Blueprint $table) {
            foreach ($this->kolom as $nama => $tipe) {
                if (!Schema::hasColumn('bimbashop_orders', $nama)) {
                    $table->{$tipe}($nama)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('bimbashop_orders', function (Blueprint $table) {
            $ada = array_filter(array_keys($this->kolom), fn ($k) => Schema::hasColumn('bimbashop_orders', $k));
            if ($ada) {
                $table->dropColumn(array_values($ada));
            }
        });
    }
};
