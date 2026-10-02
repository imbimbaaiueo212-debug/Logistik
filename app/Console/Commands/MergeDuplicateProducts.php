<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Menggabungkan produk kembar (sub_kategori + label sama).
 *
 * Tanpa --apply : hanya LAPORAN, database tidak diubah.
 * Dengan --apply: pindahkan semua product_id ke produk yang dipertahankan
 *                 (id terkecil), lalu hapus produk kembarannya.
 *
 * Simpan di: app/Console/Commands/MergeDuplicateProducts.php
 * Jalankan : php artisan products:merge-duplicates
 *            php artisan products:merge-duplicates --apply
 */
class MergeDuplicateProducts extends Command
{
    protected $signature = 'products:merge-duplicates {--apply : Jalankan perubahan (tanpa ini hanya laporan)}';

    protected $description = 'Gabungkan produk kembar (sub_kategori + label sama) dan pindahkan semua referensinya';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        $this->info($apply
            ? '=== MODE APPLY: database AKAN diubah ==='
            : '=== MODE LAPORAN: database tidak diubah ===');

        // ---------------------------------------------------------
        // 1. Cari kelompok kembar
        // ---------------------------------------------------------
        $groups = DB::table('products')
            ->select(
                'sub_kategori',
                'label',
                DB::raw('COUNT(*) AS jml'),
                DB::raw('COUNT(DISTINCT name) AS jml_nama')
            )
            ->groupBy('sub_kategori', 'label')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->line('Kelompok kembar ditemukan: ' . $groups->count());

        $map      = [];   // dup_id => keep_id
        $manual   = [];   // kelompok yang tidak digabung otomatis
        $rowsShow = [];

        foreach ($groups as $g) {

            // label kosong: jangan digabung (bisa menggabungkan produk berbeda)
            if ($g->label === null || trim($g->label) === '') {
                $manual[] = [$g->sub_kategori, '(label kosong)', $g->jml, 'label kosong'];
                continue;
            }

            // nama berbeda: kemungkinan varian, bukan kembar
            if ((int) $g->jml_nama > 1) {
                $manual[] = [$g->sub_kategori, $g->label, $g->jml, 'nama produk berbeda'];
                continue;
            }

            $q = DB::table('products')->where('label', $g->label);
            $g->sub_kategori === null
                ? $q->whereNull('sub_kategori')
                : $q->where('sub_kategori', $g->sub_kategori);

            $ids  = $q->orderBy('id')->pluck('id')->all();
            $keep = array_shift($ids);

            foreach ($ids as $dup) {
                $map[$dup] = $keep;
            }

            $rowsShow[] = [$g->sub_kategori, $g->label, $keep, implode(',', $ids)];
        }

        $this->newLine();
        $this->info('Akan digabung otomatis: ' . count($rowsShow) . ' kelompok (' . count($map) . ' produk kembar akan dihapus)');
        if ($rowsShow) {
            $this->table(['sub_kategori', 'label', 'id dipertahankan', 'id kembar'], $rowsShow);
        }

        if ($manual) {
            $this->newLine();
            $this->warn('PERLU DITANGANI MANUAL (tidak disentuh): ' . count($manual) . ' kelompok');
            $this->table(['sub_kategori', 'label', 'jumlah', 'alasan'], $manual);
        }

        if (!$map) {
            $this->info('Tidak ada yang bisa digabung otomatis.');
            return self::SUCCESS;
        }

        $dupIds = array_keys($map);

        // ---------------------------------------------------------
        // 2. Temukan semua tabel yang punya kolom product_id
        // ---------------------------------------------------------
        $refs = DB::select("
            SELECT c.TABLE_NAME AS tbl, c.COLUMN_NAME AS col
            FROM information_schema.COLUMNS c
            JOIN information_schema.TABLES t
              ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
            WHERE c.TABLE_SCHEMA = ?
              AND c.COLUMN_NAME = 'product_id'
              AND t.TABLE_TYPE = 'BASE TABLE'
              AND c.TABLE_NAME <> 'products'
        ", [DB::getDatabaseName()]);

        $this->newLine();
        $this->info('Jumlah baris yang menunjuk ke produk kembar, per tabel:');

        $report = [];
        $counts = [];
        foreach ($refs as $r) {
            $n = DB::table($r->tbl)->whereIn($r->col, $dupIds)->count();
            $counts[$r->tbl] = $n;
            $report[] = [$r->tbl, $r->col, $n];
        }
        $this->table(['tabel', 'kolom', 'baris terpengaruh'], $report);

        // ---------------------------------------------------------
        // 3. Pengaman: stok & mutasi stok yang sudah ada -> berhenti
        // ---------------------------------------------------------
        $stokTerpakai = ($counts['stocks'] ?? 0) + ($counts['stock_movements'] ?? 0);
        if ($stokTerpakai > 0) {
            $this->error("Ada {$stokTerpakai} baris stok/mutasi stok yang memakai produk kembar.");
            $this->error('Digabung otomatis bisa merusak riwayat stok. Tangani manual dulu.');
            return self::FAILURE;
        }

        if (!$apply) {
            $this->newLine();
            $this->info('Ini hanya laporan. Jika sudah yakin dan sudah backup: tambahkan --apply');
            return self::SUCCESS;
        }

        if (!$this->confirm('Sudah backup database dan yakin melanjutkan?')) {
            $this->warn('Dibatalkan.');
            return self::SUCCESS;
        }

        // ---------------------------------------------------------
        // 4. Terapkan dalam satu transaksi
        // ---------------------------------------------------------
        try {
            DB::transaction(function () use ($refs, $map, $dupIds) {

                // 4a. Pindahkan product_id di semua tabel
                foreach ($refs as $r) {
                    foreach ($map as $dup => $keep) {
                        DB::update(
                            "UPDATE IGNORE `{$r->tbl}` SET `{$r->col}` = ? WHERE `{$r->col}` = ?",
                            [$keep, $dup]
                        );
                    }
                }

                // 4b. Sisa di product_supplier (bentrok pasangan yang sama): buang
                if (DB::getSchemaBuilder()->hasTable('product_supplier')) {
                    DB::table('product_supplier')->whereIn('product_id', $dupIds)->delete();
                }

                // 4c. realisasi_aktif.product_ids (JSON daftar id)
                if (DB::getSchemaBuilder()->hasColumn('realisasi_aktif', 'product_ids')) {
                    $rows = DB::table('realisasi_aktif')->whereNotNull('product_ids')->get(['id', 'product_ids']);
                    foreach ($rows as $row) {
                        $arr = json_decode($row->product_ids, true);
                        if (!is_array($arr)) {
                            continue;
                        }
                        $baru = array_values(array_unique(array_map(
                            fn ($id) => $map[(int) $id] ?? (int) $id,
                            $arr
                        )));
                        if ($baru !== $arr) {
                            DB::table('realisasi_aktif')->where('id', $row->id)
                                ->update(['product_ids' => json_encode($baru)]);
                        }
                    }
                }

                // 4d. Verifikasi: tidak boleh ada lagi yang menunjuk ke kembaran
                foreach ($refs as $r) {
                    $sisa = DB::table($r->tbl)->whereIn($r->col, $dupIds)->count();
                    if ($sisa > 0) {
                        throw new \RuntimeException("Masih ada {$sisa} baris di {$r->tbl}.{$r->col} yang menunjuk ke produk kembar. Dibatalkan.");
                    }
                }

                // 4e. Hapus produk kembar (sudah tidak dipakai siapa pun)
                DB::table('products')->whereIn('id', $dupIds)->delete();
            });
        } catch (\Throwable $e) {
            $this->error('GAGAL, semua perubahan dibatalkan: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Selesai. ' . count($dupIds) . ' produk kembar digabung.');
        $this->info('Cek ulang dengan: php artisan products:merge-duplicates');

        return self::SUCCESS;
    }
}
