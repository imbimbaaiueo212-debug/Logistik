<?php

namespace App\Imports;

use App\Models\PesananMajalahPuw1;
use App\Models\PesananMajalahUnitPuw1;
use App\Models\UnitKemitraan;
use App\Models\UnitNamaMismatch;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PesananMajalahPuw1Import implements ToArray, WithChunkReading, WithCalculatedFormulas
{
    protected PesananMajalahPuw1 $pesananMajalahPuw1;

    public int $unitBaru = 0;
    public int $unitDiperbarui = 0;
    public int $barisDilewati = 0;

    /** @var array daftar mismatch (untuk flash session) */
    public array $mismatchList = [];

    public function __construct(PesananMajalahPuw1 $pesananMajalahPuw1)
    {
        $this->pesananMajalahPuw1 = $pesananMajalahPuw1;
    }

    public function array(array $rows)
    {
        DB::transaction(function () use ($rows) {

            $judul   = null;
            $bulan   = null;
            $tahun   = null;
            $periode = null;

            $cpLines = [];

            foreach ($rows as $index => $row) {

                if (
                    empty($row) ||
                    count(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '')) === 0
                ) {
                    continue;
                }

                $cells = array_map(fn($v) => trim((string) ($v ?? '')), $row);

                $fullText = strtoupper(trim(implode(' ', array_filter($cells, fn($v) => $v !== ''))));
                $rawFullText = trim(implode(' ', array_filter($cells, fn($v) => $v !== '')));

                // 1. JUDUL
                if ($judul === null && str_contains($fullText, 'PESANAN MAJALAH')) {
                    $judul = $rawFullText;
                    continue;
                }

                // 2. PERIODE
                if (
                    str_contains($fullText, 'PERIODE BULAN') ||
                    preg_match('/\b(JANUARI|FEBRUARI|MARET|APRIL|MEI|JUNI|JULI|AGUSTUS|SEPTEMBER|OKTOBER|NOVEMBER|DESEMBER)\b.*\d{4}/i', $fullText)
                ) {
                    $parsed  = $this->parsePeriode($fullText);
                    $bulan   = $parsed['bulan'] ?? $bulan;
                    $tahun   = $parsed['tahun'] ?? $tahun;
                    $periode = $parsed['periode'] ?? $periode;
                    continue;
                }

                // 3. CONTACT PERSON
                if (str_contains($fullText, 'CONTACT PERSON')) {
                    if (preg_match('/contact\s*person\s*[:\-]?\s*(.*)$/i', $rawFullText, $m)) {
                        $sisa = trim($m[1]);
                        if ($sisa !== '') {
                            $cpLines[] = $sisa;
                        }
                    }
                    continue;
                }

                if (
                    !is_numeric($this->clean($row[0] ?? null)) &&
                    !str_contains($fullText, 'NAMA UNIT') &&
                    !str_contains($fullText, 'NO CAB') &&
                    !str_contains($fullText, 'TOTAL') &&
                    (
                        preg_match('/\b(IBU|BAPAK|SDRI|SDR)\b/i', $rawFullText) ||
                        preg_match('/\d+\.\s*[A-Za-z].+\(/', $rawFullText)
                    )
                ) {
                    $cpLines[] = $rawFullText;
                    continue;
                }

                // 4. HEADER KOLOM
                $namaUnit = $this->clean($row[1] ?? null);
                $namaUnitLower = strtolower((string) $namaUnit);

                if (
                    $namaUnitLower === 'nama unit' ||
                    str_contains($fullText, 'NAMA UNIT') ||
                    str_contains($fullText, 'NO CAB') ||
                    (str_contains($fullText, 'KABUPATEN') && str_contains($fullText, 'JUMLAH'))
                ) {
                    $this->barisDilewati++;
                    continue;
                }

                // 5. TOTAL
                if (
                    str_contains($fullText, 'TOTAL') ||
                    $namaUnitLower === 'total'
                ) {
                    $this->barisDilewati++;
                    continue;
                }

                // 6. DATA UNIT
                $no        = $this->clean($row[0] ?? null);
                $noCabang  = $this->clean($row[2] ?? null);
                $kabupaten = $this->clean($row[3] ?? null);
                $jumlah    = $row[4] ?? null;
                $alamat    = $this->clean($row[5] ?? null);
                $telepon   = $this->clean($row[6] ?? null);

                if (empty($namaUnit) || !is_numeric($no)) {
                    $this->barisDilewati++;
                    continue;
                }

                // =====================================================
                // CEK MISMATCH
                // =====================================================
                $noCab = trim($noCabang ?? '');

                if ($noCab !== '') {
                    $uk = UnitKemitraan::where('no_cab', $noCab)->first();

                    if ($uk && !empty($uk->bimba_aiueo_unit)) {
                        $namaMaster = trim($uk->bimba_aiueo_unit);

                        if (!$this->isNamaUnitMirip($namaUnit, $namaMaster)) {
                            $this->mismatchList[] = [
                                'no_cab'      => $noCab,
                                'nama_excel'  => $namaUnit,
                                'nama_master' => $namaMaster,
                            ];

                            UnitNamaMismatch::updateOrCreate(
                                [
                                    'no_cab'  => $noCab,
                                    'periode' => $this->pesananMajalahPuw1->periode,
                                    'sumber'  => 'import_puw1',
                                ],
                                [
                                    'nama_excel'         => $namaUnit,
                                    'nama_master'        => $namaMaster,
                                    'pesanan_majalah_id' => null, // PUW1 beda tabel
                                    'is_resolved'        => false,
                                ]
                            );

                            $this->barisDilewati++;
                            continue; // TIDAK masuk pesanan_majalah_unit_puw1
                        }
                    }
                }

                $jumlahPesanan = $this->normalizeNumber($jumlah);

                $unit = PesananMajalahUnitPuw1::updateOrCreate(
                    [
                        'pesanan_majalah_puw1_id' => $this->pesananMajalahPuw1->id,
                        'no_cabang'               => $noCabang,
                    ],
                    [
                        'no'             => (int) $no,
                        'nama_unit'      => $namaUnit,
                        'kabupaten_kota' => $kabupaten,
                        'jumlah_pesanan' => $jumlahPesanan,
                        'alamat_unit'    => $alamat,
                        'telepon'        => $telepon,
                    ]
                );

                if ($unit->wasRecentlyCreated) {
                    $this->unitBaru++;
                } else {
                    $this->unitDiperbarui++;
                }
            }

            // CONTACT PERSON
            $contactPerson = null;
            $teleponContactPerson = null;

            if (!empty($cpLines)) {
                $cpText = implode(' ', $cpLines);
                [$contactPerson, $teleponContactPerson] = $this->parseContactPerson($cpText);
            }

            $updateData = array_filter([
                'judul'                  => $judul,
                'bulan'                  => $bulan,
                'tahun'                  => $tahun,
                'periode'                => $periode,
                'contact_person'         => $contactPerson,
                'telepon_contact_person' => $teleponContactPerson,
            ], fn($v) => $v !== null && $v !== '');

            if (!empty($updateData)) {
                $this->pesananMajalahPuw1->update($updateData);
            }
        });
    }

    /**
     * Nama dianggap MIRIP hanya jika exact / compact / similarity ≥ 95% / Levenshtein ≤ 5%
     * Extra angka seperti "04" → dianggap BERBEDA
     */
    protected function isNamaUnitMirip(string $namaA, string $namaB): bool
    {
        $norm = function (string $s): string {
            $s = strtolower(trim($s));
            $s = preg_replace('/[()\[\].,\-_\/\\\\]+/', ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            return trim($s);
        };

        $a = $norm($namaA);
        $b = $norm($namaB);

        if ($a === '' || $b === '' || $a === '-' || $b === '-') {
            return true;
        }

        if ($a === $b) {
            return true;
        }

        $ca = preg_replace('/\s+/', '', $a);
        $cb = preg_replace('/\s+/', '', $b);

        if ($ca !== '' && $cb !== '' && $ca === $cb) {
            return true;
        }

        similar_text($a, $b, $percent);
        if ($percent >= 95) {
            return true;
        }

        $maxLen = max(mb_strlen($a), mb_strlen($b));
        if ($maxLen > 0) {
            $dist = levenshtein($a, $b);
            if (($dist / $maxLen) <= 0.05) {
                return true;
            }
        }

        return false;
    }

    private function parseContactPerson(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^contact\s*person\s*[:\-]?\s*/i', '', $text);
        $text = trim($text);

        $names  = [];
        $phones = [];

        preg_match_all(
            '/(?:\d+\.\s*)?([A-Za-z][A-Za-z\s\.]*?)\s*\(([^)]+)\)/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $nama = trim($m[1] ?? '');
            $tel  = preg_replace('/\D/', '', $m[2] ?? '');

            if ($nama !== '' && !is_numeric($nama)) {
                $names[] = $nama;
            }
            if ($tel !== '') {
                $phones[] = $tel;
            }
        }

        if (empty($names) && $text !== '') {
            $parts = preg_split('/\s*,\s*/', $text);

            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }

                if (preg_match('/^(?:\d+\.\s*)?(.+?)\s*\(([^)]+)\)\s*$/u', $part, $m)) {
                    $nama = trim($m[1]);
                    $tel  = preg_replace('/\D/', '', $m[2]);

                    if ($nama !== '') {
                        $names[] = $nama;
                    }
                    if ($tel !== '') {
                        $phones[] = $tel;
                    }
                } else {
                    $nama = preg_replace('/^\d+\.\s*/', '', $part);
                    $nama = trim($nama);
                    if ($nama !== '' && !is_numeric($nama)) {
                        $names[] = $nama;
                    }
                }
            }
        }

        return [
            !empty($names)  ? implode(', ', $names)  : null,
            !empty($phones) ? implode(', ', $phones) : null,
        ];
    }

    private function parsePeriode(string $text): array
    {
        $bulan = $tahun = $periode = null;

        if (preg_match('/TAHUN\s+(\d{4})/i', $text, $m)) {
            $tahun = (int) $m[1];
        } elseif (preg_match('/\b(20\d{2})\b/', $text, $m)) {
            $tahun = (int) $m[1];
        }

        if (preg_match('/PERIODE\s+BULAN\s+(.+?)\s+TAHUN/i', $text, $m)) {
            $bulan = trim($m[1]);
        } elseif (preg_match('/\b(JANUARI|FEBRUARI|MARET|APRIL|MEI|JUNI|JULI|AGUSTUS|SEPTEMBER|OKTOBER|NOVEMBER|DESEMBER)\s*(M\d+)?/i', $text, $m)) {
            $bulan = trim($m[0]);
        }

        $bulanMap = [
            'JANUARI' => '01', 'FEBRUARI' => '02', 'MARET' => '03', 'APRIL' => '04',
            'MEI' => '05', 'JUNI' => '06', 'JULI' => '07', 'AGUSTUS' => '08',
            'SEPTEMBER' => '09', 'OKTOBER' => '10', 'NOVEMBER' => '11', 'DESEMBER' => '12',
        ];

        if ($tahun && $bulan) {
            foreach ($bulanMap as $nama => $angka) {
                if (str_contains(strtoupper($bulan), $nama)) {
                    $periode = $tahun . '-' . $angka;
                    break;
                }
            }
        }

        return compact('bulan', 'tahun', 'periode');
    }

    private function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function normalizeNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = str_replace(' ', '', $value);

        if (str_contains($value, '.') && str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}