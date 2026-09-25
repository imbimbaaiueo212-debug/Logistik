<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['key' => 'user',       'name' => 'User'],
            ['key' => 'produk',     'name' => 'Produk'],
            ['key' => 'suplier',    'name' => 'Suplier'],
            ['key' => 'stokis',     'name' => 'Stokis'],
            ['key' => 'pemesanan',  'name' => 'Pemesanan'],
            ['key' => 'penjualan',  'name' => 'Penjualan'],
            ['key' => 'persiapan',  'name' => 'Persiapan'],
            ['key' => 'qc',         'name' => 'QC'],
            ['key' => 'distribusi', 'name' => 'Distribusi'],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(['key' => $m['key']], $m);
        }
    }
}