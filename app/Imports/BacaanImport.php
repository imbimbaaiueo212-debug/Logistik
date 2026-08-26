<?php

namespace App\Imports;

use App\Models\BacaanPesanan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BacaanImport implements ToCollection
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index < 4) continue; // skip header

            $no       = $row[1] ?? null;
            $cabang   = trim($row[2] ?? '');
            $namaUnit = trim($row[3] ?? '');
            $bacaan   = (int) ($row[5] ?? 0);   // kolom Bacaan Unit
            $telepon  = trim($row[6] ?? '');
            $alamat   = trim($row[7] ?? '');

            if ($namaUnit === '' || $bacaan <= 0 || !is_numeric($no)) {
                continue;
            }

            BacaanPesanan::create([
                'bacaan_periode_id' => $this->periodeId,
                'nama_unit'         => $namaUnit,
                'no_cab'            => $cabang ?: null,
                'bacaan_unit'       => $bacaan,
                'telepon'           => $telepon ?: null,
                'alamat'            => $alamat ?: null,
            ]);
        }
    }
}