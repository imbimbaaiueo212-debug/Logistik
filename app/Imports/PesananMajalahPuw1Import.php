<?php

namespace App\Imports;

use App\Models\PesananMajalahPuw1;
use App\Models\PesananMajalahUnitPuw1;
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

                // Skip baris kosong
                if (
                    empty($row) ||
                    count(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '')) === 0
                ) {
                    continue;
                }

                // Gabungkan semua cell
                $cells = array_map(fn($v) => trim((string) ($v ?? '')), $row);

                $fullText = strtoupper(trim(implode(' ', array_filter($cells, fn($v) => $v !== ''))));
                $rawFullText = trim(implode(' ', array_filter($cells, fn($v) => $v !== '')));

                // =====================================================
                // 1. JUDUL
                // =====================================================
                if ($judul === null && str_contains($fullText, 'PESANAN MAJALAH')) {
                    $judul = $rawFullText;
                    continue;
                }

                // =====================================================
                // 2. PERIODE
                // =====================================================
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

                // =====================================================
                // 3. CONTACT PERSON
                // =====================================================
                if (str_contains($fullText, 'CONTACT PERSON')) {
                    if (preg_match('/contact\s*person\s*[:\-]?\s*(.*)$/i', $rawFullText, $m)) {
                        $sisa = trim($m[1]);
                        if ($sisa !== '') {
                            $cpLines[] = $sisa;
                        }
                    }
                    continue;
                }

                // Baris lanjutan contact person
                // Contoh: "1. Ibu Syafa (0818 0617 5805), 2. Ibu Ayu (0896 2027 4935)"
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

                // =====================================================
                // 4. HEADER KOLOM
                // =====================================================
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

                // =====================================================
                // 5. TOTAL
                // =====================================================
                if (
                    str_contains($fullText, 'TOTAL') ||
                    $namaUnitLower === 'total'
                ) {
                    $this->barisDilewati++;
                    continue;
                }

                // =====================================================
                // 6. DATA UNIT
                // =====================================================
                $no        = $this->clean($row[0] ?? null);
                $noCabang  = $this->clean($row[2] ?? null);
                $kabupaten = $this->clean($row[3] ?? null);
                $jumlah    = $row[4] ?? null;
                $alamat    = $this->clean($row[5] ?? null);
                $telepon   = $this->clean($row[6] ?? null);

                // Nama unit wajib + kolom No harus angka
                if (empty($namaUnit) || !is_numeric($no)) {
                    $this->barisDilewati++;
                    continue;
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

            // =====================================================
            // PROSES CONTACT PERSON
            // =====================================================
            $contactPerson = null;
            $teleponContactPerson = null;

            if (!empty($cpLines)) {
                $cpText = implode(' ', $cpLines);
                [$contactPerson, $teleponContactPerson] = $this->parseContactPerson($cpText);
            }

            // =====================================================
            // UPDATE HEADER
            // =====================================================
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
     * Parse Contact Person
     * Contoh input:
     * "1. Ibu Syafa (0818 0617 5805), 2. Ibu Ayu (0896 2027 4935)"
     *
     * Hasil:
     * contact_person         = "Ibu Syafa, Ibu Ayu"
     * telepon_contact_person = "081806175805, 089620274935"
     */
    private function parseContactPerson(string $text): array
    {
        $text = trim($text);

        // Hapus prefix "Contact Person :"
        $text = preg_replace('/^contact\s*person\s*[:\-]?\s*/i', '', $text);
        $text = trim($text);

        $names  = [];
        $phones = [];

        // Pola utama: (angka.) Nama (telepon)
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

        // Fallback: kalau regex utama gagal, coba pisah dengan koma
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
                    // Tanpa telepon
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

    /**
     * Parse periode dari teks
     */
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

    /**
     * Normalisasi angka (support format Indonesia)
     * 134,4 → 134.4
     * 1.234,56 → 1234.56
     */
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
            // Format Indonesia: 1.234,56
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            // Hanya koma → desimal
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}