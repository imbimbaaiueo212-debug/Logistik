<?php

namespace App\Imports;

use App\Models\PesananMajalah;
use App\Models\PesananMajalahKotamadya;
use App\Models\PesananMajalahUnitKotamadya;
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
                            jumlahPesanan: $this->toDecimal($colD), // ← decimal
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
     * HANDLE UNIT
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

        // Header kolom
        if (str_contains($combined, 'NAMA UNIT') || str_contains($combined, 'NO CABANG')) {
            return true;
        }
        if (str_contains($combined, 'JUMLAH PESANAN') || str_contains($combined, 'ALAMAT')) {
            return true;
        }

        // Baris TOTAL / subtotal
        if (
            str_contains($combined, 'TOTAL') ||
            str_contains($combined, 'JUMLAH') ||
            str_contains($combined, 'SUB TOTAL') ||
            str_contains($combined, 'GRAND TOTAL')
        ) {
            return true;
        }

        // Kolom NO
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

        // Format: Nama (08xxxxxxxxxx)
        if (preg_match('/\(([^)]+)\)/', $rest, $m)) {
            $telepon = $this->cleanPhone($m[1]);
            $nama    = trim(preg_replace('/\([^)]+\)/', '', $rest));
        }
        // Format: Nama 08xxxxxxxxxx
        elseif (preg_match('/(.+?)\s+([\d\s\-\+]+)$/', $rest, $m)) {
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

    /**
     * ============================================================
     * PARSE ANGKA
     * ============================================================
     */
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

    /**
     * Parse decimal dengan support format Indonesia
     * Contoh yang didukung:
     * 55,2
     * 55.2
     * 1.234,56
     * 1,234.56
     * 1234,5
     * 1234.5
     */
    private function toDecimal($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        // Sudah numeric (Excel kadang langsung kasih float)
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $value = trim((string) $value);
        $value = str_replace(' ', '', $value); // hapus spasi

        // Kasus ada titik DAN koma
        if (str_contains($value, ',') && str_contains($value, '.')) {
            // Cek posisi terakhir → yang lebih belakang adalah desimal
            if (strrpos($value, ',') > strrpos($value, '.')) {
                // Format Indonesia: 1.234,56
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // Format internasional: 1,234.56
                $value = str_replace(',', '', $value);
            }
        }
        // Hanya ada koma → anggap desimal Indonesia
        elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }
        // Hanya ada titik → biarkan (sudah format internasional)

        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    // Getter
    public function getKotamadyaBaru(): int   { return $this->kotamadyaBaru; }
    public function getKotamadyaLama(): int   { return $this->kotamadyaLama; }
    public function getUnitBaru(): int        { return $this->unitBaru; }
    public function getUnitDiupdate(): int    { return $this->unitDiupdate; }
    public function getBarisDilewati(): int   { return $this->barisDilewati; }
}