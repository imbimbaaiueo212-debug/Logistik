<?php

namespace App\Imports;

use App\Models\StokisMitra;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StokisMitraImport implements ToModel, WithStartRow, WithChunkReading
{
    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function model(array $row)
    {
        if (empty(trim($row[0] ?? ''))) {
            return null;
        }

        // Proses item_sku
        $itemSkuRaw = $row[13] ?? '';
        $itemSku = null;

        if (is_string($itemSkuRaw) && !empty($itemSkuRaw)) {
            $arr = array_filter(array_map('trim', explode(',', $itemSkuRaw)));
            $itemSku = !empty($arr) ? json_encode($arr) : null;
        }

        $data = [
            'no_cab'                            => trim($row[0]),
            'nama_stokis_db_kemitraan'          => $row[1] ?? null,
            'nama_stokis_db_bimbashop'          => $row[2] ?? null,
            'no_induk_mitra'                    => $row[3] ?? null,
            'nama_mitra'                        => $row[4] ?? null,
            'email'                             => $row[5] ?? null,
            'no_hp'                             => $row[6] ?? null,
            'related_form_pembukaan_unit_aktif' => $row[7] ?? null,
            'related_formulir_kerjasama_english'=> $row[8] ?? null,
            'db_kemitraan_db_bimbashop'         => $row[9] ?? null,
            'related_unit_bimba_aiueo'          => $row[10] ?? null,
            'related_formulir_kerjasama_mk_mm'  => $row[11] ?? null,
            'related_pengajuan_perubahan'       => $row[12] ?? null,
            'item_sku'                          => $itemSku,
            'ops_stokist'                       => $row[14] ?? null,
        ];

        // === CREATE OR UPDATE ===
        return StokisMitra::updateOrCreate(
            ['no_cab' => $data['no_cab']],  // Kunci utama untuk cek duplikat
            $data                            // Data yang akan diisi/update
        );
    }
}