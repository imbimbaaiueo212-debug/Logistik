<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeBimbaText extends Command
{
    protected $signature = 'data:normalize-bimba';

    protected $description = 'Mengubah semua tulisan Bimba menjadi biMBA pada data database';

    public function handle()
    {
        $this->info('Memulai normalisasi tulisan Bimba menjadi biMBA...');

        // ==========================================
        // PRODUCTS
        // ==========================================
        $productColumns = [
            'name',
            'nama',
            'kategori',
            'sub_kategori',
        ];

        foreach ($productColumns as $column) {

            if (!\Schema::hasColumn('products', $column)) {
                continue;
            }

            $updated = DB::table('products')
                ->where($column, 'LIKE', '%bimba%')
                ->update([
                    $column => DB::raw(
                        "REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE($column, 'BIMBA', 'biMBA'),
                                    'Bimba', 'biMBA'
                                ),
                                'bimba', 'biMBA'
                            ),
                            'BiMBA', 'biMBA'
                        )"
                    ),
                ]);

            $this->info(
                "products.$column : $updated data diperbarui"
            );
        }


        // ==========================================
        // REALISASI AKTIF
        // ==========================================
        $realisasiColumns = [
            'nama_barang',
            'nama_unit',
            'penyebut',
        ];

        foreach ($realisasiColumns as $column) {

            if (!\Schema::hasColumn('realisasi_aktif', $column)) {
                continue;
            }

            $updated = DB::table('realisasi_aktif')
                ->where($column, 'LIKE', '%bimba%')
                ->update([
                    $column => DB::raw(
                        "REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE($column, 'BIMBA', 'biMBA'),
                                    'Bimba', 'biMBA'
                                ),
                                'bimba', 'biMBA'
                            ),
                            'BiMBA', 'biMBA'
                        )"
                    ),
                ]);

            $this->info(
                "realisasi_aktif.$column : $updated data diperbarui"
            );
        }


        // ==========================================
        // JAKARTA AKTIF
        // ==========================================
        $jakartaColumns = [
            'nama_unit',
            'billing_company',
            'billing_last_name',
            'catatan',
        ];

        foreach ($jakartaColumns as $column) {

            if (!\Schema::hasColumn('jakarta_aktif', $column)) {
                continue;
            }

            $updated = DB::table('jakarta_aktif')
                ->where($column, 'LIKE', '%bimba%')
                ->update([
                    $column => DB::raw(
                        "REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE($column, 'BIMBA', 'biMBA'),
                                    'Bimba', 'biMBA'
                                ),
                                'bimba', 'biMBA'
                            ),
                            'BiMBA', 'biMBA'
                        )"
                    ),
                ]);

            $this->info(
                "jakarta_aktif.$column : $updated data diperbarui"
            );
        }


        $this->newLine();

        $this->info(
            'Selesai! Semua data Bimba berhasil dinormalisasi menjadi biMBA.'
        );

        return Command::SUCCESS;
    }
}