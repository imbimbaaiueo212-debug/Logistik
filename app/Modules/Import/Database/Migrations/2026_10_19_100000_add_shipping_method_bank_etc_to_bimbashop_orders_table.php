<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kolom export biMBA Shop yang sebelumnya belum ikut tersimpan. */
    private array $kolom = [
        'shipping_state_code' => 'string',
        'shipping_method'     => 'string',
        'bank'                => 'string',
        'customer_roles'      => 'string',
        'dokan_vendor_name'   => 'string',
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
