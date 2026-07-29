<?php

namespace App\Imports;

use App\Models\PesananMajalah;
use App\Models\PesananMajalahKotamadya;
use App\Models\PesananMajalahUnitKotamadya;
use App\Models\UnitKemitraan;
use App\Models\UnitNamaMismatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PesananMajalahKotamadyaImport implements ToCollection, WithStartRow
{
    protected PesananMajalah $pesananMajalah;

    protected int $kotamadyaBaru   = 0;
    protected int $kotamadyaLama   = 0;
    protected int $unitBaru        = 0;
    protected int $unitDiupdate    = 0;
    protected int $barisDilewati   = 0;

    protected ?PesananMajalahKotamadya $currentKotamadya = null;
    protected array $urutanUnit = [];

    /** @var array daftar mismatch (untuk flash session) */
    public array $mismatchList = [];

    public function __construct(PesananMajalah $pesananMajalah)
    {
        $this->pesananMajalah = $pesananMajalah;
    }

    public function startRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {

            foreach ($rows as $row) {

                $cols = $row->values()
                    ->map(fn ($v) => $this->clean($v))
                    ->toArray();

                if ($this->isEmptyRow($cols)) {
                    $this->barisDilewati++;
                    continue;
                }

                $colA = $cols[0] ?? null;
                $colB = $cols[1] ?? null;
                $colC = $cols[2] ?? null;
                $colD = $cols[3] ?? null;
                $colE = $cols[4] ?? null;
                $colF = $cols[5] ?? null;

                // 1. Skip baris Judul
                if ($this->isJudulRow($colA, $colB)) {
                    continue;
                }

                // 2. Skip baris Periode
                if ($this->isPeriodeRow($colA, $colB)) {
                    continue;
                }

                // 3. Skip header kolom & baris TOTAL
                if ($this->isTitleOrHeaderRow($colA, $colB, $colC, $colD, $colE)) {
                    $this->barisDilewati++;
                    continue;
                }

                // 4. Deteksi Nama Kotamadya / Kabupaten
                if ($this->isKotamadyaRow($colA, $colB)) {
                    $nama = $this->extractKotamadyaName($colA, $colB);

                    if ($nama) {
                        $this->handleKotamadya($nama);
                    } else {
                        $this->barisDilewati++;
                    }
                    continue;
                }

                // 5. Contact Person
                if ($this->isContactPersonRow($colA, $colB)) {
                    if ($this->currentKotamadya) {
                        [$namaCp, $teleponCp] = $this->parseContactPerson($colA ?: $colB);

                        $update = array_filter([
                            'contact_person'         => $namaCp,
                            'telepon_contact_person' => $teleponCp,
                        ]);

                        if (!empty($update)) {
                            $this->currentKotamadya->update($update);
                        }
                    }
                    continue;
                }

                // 6. Data Unit
                if ($this->isUnitDataRow($colA, $colB)) {
                    if ($this->currentKotamadya) {
                        $this->handleUnit(
                            no:            $this->toInteger($colA),
                            namaUnit:      $colB,
                            noCabang:      $colC,
                            jumlahPesanan: $this->toDecimal($colD),
                            alamatUnit:    $colE,
                            telepon:       $colF
                        );
                    } else {
                        $this->barisDilewati++;
                    }
                    continue;
                }

                // Baris lain dilewati
                $this->barisDilewati++;
            }
        });
    }

    /**
     * ============================================================
     * HANDLE KOTAMADYA
     * ============================================================
     */
    private function handleKotamadya(string $namaKotamadya): void
    {
        $kotamadya = PesananMajalahKotamadya::where('pesanan_majalah_id', $this->pesananMajalah->id)
            ->where('nama_kotamadya', $namaKotamadya)
            ->first();

        if (!$kotamadya) {
            $kotamadya = PesananMajalahKotamadya::create([
                'pesanan_majalah_id'     => $this->pesananMajalah->id,
                'nama_kotamadya'         => $namaKotamadya,
                'contact_person'         => null,
                'telepon_contact_person' => null,
                'urutan'                 => 0,
            ]);
            $this->kotamadyaBaru++;
        } else {
            $this->kotamadyaLama++;
        }

        $this->currentKotamadya = $kotamadya;
        $this->urutanUnit[$kotamadya->id] = $this->urutanUnit[$kotamadya->id] ?? 0;
    }

    /**
     * ============================================================
     * HANDLE UNIT + CEK MISMATCH
     * ============================================================
     */
    private function handleUnit(
        int $no,
        ?string $namaUnit,
        ?string $noCabang,
        float $jumlahPesanan,
        ?string $alamatUnit,
        ?string $telepon = null
    ): void {
        if (!$namaUnit) {
            $this->barisDilewati++;
            return;
        }

        $this->urutanUnit[$this->currentKotamadya->id]++;

        if ($no <= 0) {
            $no = $this->urutanUnit[$this->currentKotamadya->id];
        }

        // =====================================================
        // CEK MISMATCH (sama seperti Kabupaten)
        // =====================================================
        $noCab = trim($noCabang ?? '');

        if ($noCab !== '') {
            $uk = UnitKemitraan::where('no_cab', $noCab)->first();

            if ($uk && !empty($uk->bimba_aiueo_unit)) {
                $namaMaster = trim($uk->bimba_aiueo_unit);

                if (!$this->isNamaUnitMirip($namaUnit, $namaMaster)) {
                    // Simpan ke list flash
                    $this->mismatchList[] = [
                        'no_cab'      => $noCab,
                        'nama_excel'  => $namaUnit,
                        'nama_master' => $namaMaster,
                    ];

                    // Simpan ke database mismatch
                    UnitNamaMismatch::updateOrCreate(
                        [
                            'no_cab'  => $noCab,
                            'periode' => $this->pesananMajalah->periode,
                            'sumber'  => 'import_kotamadya',
                        ],
                        [
                            'nama_excel'         => $namaUnit,
                            'nama_master'        => $namaMaster,
                            'pesanan_majalah_id' => $this->pesananMajalah->id,
                            'is_resolved'        => false,
                        ]
                    );

                    $this->barisDilewati++;
                    return; // TIDAK masuk pesanan_majalah_unit_kotamadya
                }
            }
        }

        // Nama match / no_cab tidak ada di master → lanjut simpan
        $query = PesananMajalahUnitKotamadya::where(
            'pesanan_majalah_kotamadya_id',
            $this->currentKotamadya->id
        );

        if ($noCabang) {
            $query->where('no_cabang', $noCabang);
        } else {
            $query->where('nama_unit', $namaUnit);
        }

        $unit = $query->first();

        $data = [
            'no'             => $no,
            'nama_unit'      => $namaUnit,
            'no_cabang'      => $noCabang,
            'jumlah_pesanan' => $jumlahPesanan,
            'alamat_unit'    => $alamatUnit,
            'telepon'        => $telepon,
        ];

        if ($unit) {
            $unit->update($data);
            $this->unitDiupdate++;
        } else {
            PesananMajalahUnitKotamadya::create(array_merge($data, [
                'pesanan_majalah_kotamadya_id' => $this->currentKotamadya->id,
            ]));
            $this->unitBaru++;
        }
    }

    /**
     * Nama dianggap MIRIP hanya jika:
     * - Sama persis setelah normalisasi
     * - Sama setelah spasi dihilangkan (compact)
     * - Similarity ≥ 95% ATAU Levenshtein ≤ 5%
     *
     * Extra angka/suffix seperti "04" → dianggap BERBEDA
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

        // 1. Exact match
        if ($a === $b) {
            return true;
        }

        // 2. Compact (tanpa spasi)
        $ca = preg_replace('/\s+/', '', $a);
        $cb = preg_replace('/\s+/', '', $b);

        if ($ca !== '' && $cb !== '' && $ca === $cb) {
            return true;
        }

        // 3. Similarity sangat tinggi
        similar_text($a, $b, $percent);
        if ($percent >= 95) {
            return true;
        }

        // 4. Levenshtein relatif sangat kecil
        $maxLen = max(mb_strlen($a), mb_strlen($b));
        if ($maxLen > 0) {
            $dist = levenshtein($a, $b);
            if (($dist / $maxLen) <= 0.05) {
                return true;
            }
        }

        return false;
    }

    /**
     * ============================================================
     * DETEKSI BARIS
     * ============================================================
     */
    private function isEmptyRow(array $cols): bool
    {
        return empty(array_filter($cols, fn ($v) => $v !== null && $v !== ''));
    }

    private function isJudulRow(?string $a, ?string $b): bool
    {
        $text = strtoupper(trim(($a ?? '') . ' ' . ($b ?? '')));
        return str_contains($text, 'PESANAN MAJALAH');
    }

    private function isPeriodeRow(?string $a, ?string $b): bool
    {
        $text = strtoupper(trim(($a ?? '') . ' ' . ($b ?? '')));
        return str_contains($text, 'PERIODE BULAN') ||
               (str_contains($text, 'BULAN') && str_contains($text, 'TAHUN'));
    }

    private function isTitleOrHeaderRow(?string $a, ?string $b, ?string $c, ?string $d, ?string $e): bool
    {
        $combined = strtoupper(implode(' ', array_filter([$a, $b, $c, $d, $e])));

        if (str_contains($combined, 'NAMA UNIT') || str_contains($combined, 'NO CABANG')) {
            return true;
        }
        if (str_contains($combined, 'JUMLAH PESANAN') || str_contains($combined, 'ALAMAT')) {
            return true;
        }

        if (
            str_contains($combined, 'TOTAL') ||
            str_contains($combined, 'JUMLAH') ||
            str_contains($combined, 'SUB TOTAL') ||
            str_contains($combined, 'GRAND TOTAL')
        ) {
            return true;
        }

        if (in_array(strtoupper(trim($a ?? '')), ['NO.', 'NO'])) {
            return true;
        }

        return false;
    }

    private function isKotamadyaRow(?string $a, ?string $b): bool
    {
        $text = strtoupper(trim(($a ?? '') . ' ' . ($b ?? '')));

        $keywords = [
            'KOTAMADYA', 'KOTA ', 'KABUPATEN', 'KAB. ',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($text, $kw) && !is_numeric($a) && !is_numeric($b)) {
                return true;
            }
        }

        return false;
    }

    private function isContactPersonRow(?string $a, ?string $b): bool
    {
        $text = strtoupper(trim(($a ?? '') . ' ' . ($b ?? '')));
        return str_contains($text, 'CONTACT PERSON');
    }

    private function isUnitDataRow(?string $a, ?string $b): bool
    {
        return is_numeric($a) && !empty($b) && !is_numeric($b);
    }

    /**
     * ============================================================
     * EXTRACTOR
     * ============================================================
     */
    private function extractKotamadyaName(?string $a, ?string $b): ?string
    {
        $name = trim($a ?: $b ?: '');
        if ($name === '') {
            return null;
        }
        return preg_replace('/\s+/', ' ', $name);
    }

    private function parseContactPerson(?string $text): array
    {
        if (!$text) {
            return [null, null];
        }

        $text = trim($text);

        if (preg_match('/contact\s*person\s*[:\-]?\s*(.+)$/i', $text, $m)) {
            $rest = trim($m[1]);
        } else {
            $rest = $text;
        }

        $nama    = null;
        $telepon = null;

        if (preg_match('/\(([^)]+)\)/', $rest, $m)) {
            $telepon = $this->cleanPhone($m[1]);
            $nama    = trim(preg_replace('/\([^)]+\)/', '', $rest));
        } elseif (preg_match('/(.+?)\s+([\d\s\-\+]+)$/', $rest, $m)) {
            $nama    = trim($m[1]);
            $telepon = $this->cleanPhone($m[2]);
        } else {
            $nama = $rest;
        }

        $nama = ltrim($nama ?? '', ': -');

        return [$nama ?: null, $telepon ?: null];
    }

    private function cleanPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        $phone = preg_replace('/\D/', '', $phone);
        return $phone !== '' ? $phone : null;
    }

    private function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function toInteger($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $value = str_replace(['.', ','], '', (string) $value);
        return is_numeric($value) ? (int) $value : 0;
    }

    private function toDecimal($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $value = trim((string) $value);
        $value = str_replace(' ', '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    // Getter
    public function getKotamadyaBaru(): int   { return $this->kotamadyaBaru; }
    public function getKotamadyaLama(): int   { return $this->kotamadyaLama; }
    public function getUnitBaru(): int        { return $this->unitBaru; }
    public function getUnitDiupdate(): int    { return $this->unitDiupdate; }
    public function getBarisDilewati(): int   { return $this->barisDilewati; }
}