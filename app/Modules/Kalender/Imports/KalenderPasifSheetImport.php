<?php

namespace App\Modules\Kalender\Imports;

use App\Modules\Kalender\Models\KalenderPasifPesanan;
use App\Modules\Kalender\Models\KalenderBacaanPesanan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import SATU sheet rekap pemesanan kalender Unit Pasif (dipanggil dari KalenderPasifImport).
 *
 * Format baru (kolom A kosong):
 *   1 NO | 2 CABANG | 3 biMBA-AIUEO UNIT | 4 MURID | 5 RELAWAN | 6 RELAWAN @2 exp (rumus)
 *   7 HUMAS 10 + Pajangan Unit | 8 TOTAL (rumus) | 9 NO TELP | 10 ALAMAT
 *
 * Format lama (tanpa kolom Humas), dikenali dari header yang tidak memuat "HUMAS":
 *   1 NO | 2 CABANG | 3 UNIT | 4 qty | 5 Bacaan Unit | 6 NO TELP | 7 ALAMAT
 *
 * Kolom rumus tidak dibaca: @2 exp dan TOTAL dihitung ulang dari MURID, RELAWAN, HUMAS.
 */
class KalenderPasifSheetImport implements ToCollection
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
        $formatBaru = $this->formatBaru($rows);

        foreach ($rows as $index => $row) {
            // Baris 1-4 Excel = judul & header
            if ($index < 4) {
                continue;
            }

            $no       = $row[1] ?? null;
            $cabang   = trim((string) ($row[2] ?? ''));
            $namaUnit = trim((string) ($row[3] ?? ''));

            // Lewati baris kosong / baris TOTAL
            if ($namaUnit === '' || !is_numeric($no)) {
                continue;
            }

            $murid   = $this->angka($row[4] ?? 0);
            $relawan = $this->angka($row[5] ?? 0);

            if ($formatBaru) {
                $humas   = $this->angka($row[7] ?? 0);
                $telepon = trim((string) ($row[9] ?? ''));
                $alamat  = trim((string) ($row[10] ?? ''));
            } else {
                $humas   = 0;
                $telepon = trim((string) ($row[6] ?? ''));
                $alamat  = trim((string) ($row[7] ?? ''));
            }

            // 1. Unit Pasif
            if ($murid > 0 || $relawan > 0 || $humas > 0) {
                KalenderPasifPesanan::create([
                    'pasif_periode_id' => $this->pasifPeriodeId,
                    'nama_unit'        => $namaUnit,
                    'no_cab'           => $cabang ?: null,
                    'qty'              => $murid,
                    'bacaan_unit'      => $relawan,
                    'humas'            => $humas,
                    'telepon'          => $telepon ?: null,
                    'alamat'           => $alamat ?: null,
                ]);
            }

            // 2. Bacaan Unit (tabel terpisah)
            if ($this->bacaanPeriodeId && $relawan > 0) {
                KalenderBacaanPesanan::create([
                    'bacaan_periode_id' => $this->bacaanPeriodeId,
                    'nama_unit'         => $namaUnit,
                    'no_cab'            => $cabang ?: null,
                    'bacaan_unit'       => $relawan,
                    'telepon'           => $telepon ?: null,
                    'alamat'            => $alamat ?: null,
                ]);
            }
        }
    }

    private function formatBaru(Collection $rows): bool
    {
        foreach ($rows->take(5) as $row) {
            foreach ($row as $cell) {
                if (stripos((string) $cell, 'HUMAS') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function angka($v): int
    {
        return is_numeric($v) ? (int) round((float) $v) : 0;
    }
}
