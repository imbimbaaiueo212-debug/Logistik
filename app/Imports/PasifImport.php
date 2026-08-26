<?php

namespace App\Imports;

use App\Models\PasifPesanan;
use App\Models\BacaanPesanan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PasifImport implements ToCollection
{
    protected $pasifPeriodeId;
    protected $bacaanPeriodeId;

    public function __construct($pasifPeriodeId, $bacaanPeriodeId = null)
    {
        $this->pasifPeriodeId  = $pasifPeriodeId;
        $this->bacaanPeriodeId = $bacaanPeriodeId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Skip header (baris 1–4 di Excel)
            if ($index < 4) {
                continue;
            }

            // Struktur kolom Excel (kolom A kosong):
            // 0 = kosong
            // 1 = NO
            // 2 = CABANG
            // 3 = biMBA-AIUEO UNIT
            // 4 = MAJALAH (qty)
            // 5 = Bacaan Unit
            // 6 = NO TELP
            // 7 = ALAMAT

            $no       = $row[1] ?? null;
            $cabang   = trim((string) ($row[2] ?? ''));
            $namaUnit = trim((string) ($row[3] ?? ''));
            $qty      = (int) ($row[4] ?? 0);
            $bacaan   = (int) ($row[5] ?? 0);
            $telepon  = trim((string) ($row[6] ?? ''));
            $alamat   = trim((string) ($row[7] ?? ''));

            // Skip baris kosong / total
            if ($namaUnit === '' || !is_numeric($no)) {
                continue;
            }

            // 1. Simpan ke Unit Pasif (Qty Majalah)
            if ($qty > 0) {
                PasifPesanan::create([
                    'pasif_periode_id' => $this->pasifPeriodeId,
                    'nama_unit'        => $namaUnit,
                    'no_cab'           => $cabang ?: null,
                    'qty'              => $qty,
                    'bacaan_unit'      => $bacaan,
                    'telepon'          => $telepon ?: null,
                    'alamat'           => $alamat ?: null,
                ]);
            }

            // 2. Simpan ke Bacaan Unit (tabel terpisah)
            if ($this->bacaanPeriodeId && $bacaan > 0) {
                BacaanPesanan::create([
                    'bacaan_periode_id' => $this->bacaanPeriodeId,
                    'nama_unit'         => $namaUnit,
                    'no_cab'            => $cabang ?: null,
                    'bacaan_unit'       => $bacaan,
                    'telepon'           => $telepon ?: null,
                    'alamat'            => $alamat ?: null,
                ]);
            }
        }
    }
}