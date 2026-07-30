<?php

namespace App\Imports;

use App\Models\PesananMajalah;
use App\Models\PesananMajalahKabupaten;
use App\Models\PesananMajalahUnit;
use App\Models\UnitKemitraan;
use App\Models\UnitNamaMismatch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PesananMajalahImport implements ToCollection, WithCalculatedFormulas
{
    protected PesananMajalah $pesananMajalah;
    protected ?PesananMajalahKabupaten $kabupatenAktif = null;
    protected int $urutanKabupaten = 0;

    /** @var array daftar mismatch (untuk flash session) */
    public array $mismatchList = [];

    public function __construct(PesananMajalah $pesananMajalah)
    {
        $this->pesananMajalah = $pesananMajalah;
    }

    public function collection(Collection $rows)
    {
        // Hapus data lama periode ini
        $kabupatenIds = PesananMajalahKabupaten::where(
            'pesanan_majalah_id',
            $this->pesananMajalah->id
        )->pluck('id');

        if ($kabupatenIds->isNotEmpty()) {
            PesananMajalahUnit::whereIn(
                'pesanan_majalah_kabupaten_id',
                $kabupatenIds
            )->delete();

            PesananMajalahKabupaten::where(
                'pesanan_majalah_id',
                $this->pesananMajalah->id
            )->delete();
        }

        $this->kabupatenAktif  = null;
        $this->urutanKabupaten = 0;
        $this->mismatchList    = [];

        $judul   = null;
        $bulan   = null;
        $tahun   = null;
        $periode = null;

        foreach ($rows as $index => $row) {
            $cells = $row->toArray();

            $cells = array_map(function ($value) {
                if ($value === null) return '';
                return trim(preg_replace('/\s+/', ' ', (string) $value));
            }, $cells);

            if (empty(array_filter($cells, fn ($v) => $v !== ''))) {
                continue;
            }

            $fullText = strtoupper(trim(implode(' ', array_filter($cells, fn ($v) => $v !== ''))));

            if (str_contains($fullText, 'TOTAL')) {
                continue;
            }

            // Judul
            if ($judul === null && str_contains($fullText, 'PESANAN MAJALAH')) {
                $judul = trim(implode(' ', array_filter($cells, fn ($v) => $v !== '')));
                continue;
            }

            // Periode
            if (
                str_contains($fullText, 'PERIODE BULAN') ||
                str_contains($fullText, 'JULI M') ||
                preg_match('/\b(JANUARI|FEBRUARI|MARET|APRIL|MEI|JUNI|JULI|AGUSTUS|SEPTEMBER|OKTOBER|NOVEMBER|DESEMBER)\b.*\d{4}/i', $fullText)
            ) {
                $parsed  = $this->parsePeriode($fullText);
                $bulan   = $parsed['bulan'] ?? $bulan;
                $tahun   = $parsed['tahun'] ?? $tahun;
                $periode = $parsed['periode'] ?? $periode;
                continue;
            }

            // Kabupaten
            if (
                preg_match('/^KABUPATEN\s+(.+)$/i', $fullText, $matches) ||
                preg_match('/\bKAB(?:UPATEN)?\.?\s+([A-Z\s]+)$/i', $fullText, $matches)
            ) {
                $namaKabupaten = trim($matches[1]);
                $namaKabupaten = preg_replace('/\s+(CONTACT|1\.|IBU|BAPAK).*$/i', '', $namaKabupaten);
                $namaKabupaten = trim($namaKabupaten);

                if ($namaKabupaten !== '') {
                    $this->urutanKabupaten++;

                    $this->kabupatenAktif = PesananMajalahKabupaten::create([
                        'pesanan_majalah_id' => $this->pesananMajalah->id,
                        'nama_kabupaten'     => $namaKabupaten,
                        'contact_person'     => null,
                        'urutan'             => $this->urutanKabupaten,
                    ]);
                }
                continue;
            }

            // Contact Person
            if ($this->kabupatenAktif && (
                preg_match('/CONTACT\s*PERSON\s*[:\-]?\s*(.+)$/i', $fullText, $matches) ||
                preg_match('/\b(1\.\s*IBU.+|IBU\s+[A-Z]+.+\(.+\))/i', $fullText, $matches)
            )) {
                $cp = trim($matches[1] ?? $matches[0]);
                $this->kabupatenAktif->update(['contact_person' => $cp]);
                continue;
            }

            // Data Unit
            if ($this->kabupatenAktif && $this->isUnitRow($cells, $fullText)) {
                $this->importUnit($cells, $fullText);
                continue;
            }
        }

        $updateData = array_filter([
            'judul'   => $judul,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'periode' => $periode,
        ], fn ($v) => $v !== null);

        if (!empty($updateData)) {
            $this->pesananMajalah->update($updateData);
        }
    }

    protected function isUnitRow(array $cells, string $fullText): bool
    {
        $no = trim($cells[0] ?? '');
        if ($no !== '' && is_numeric($no)) {
            return true;
        }

        if (preg_match('/^\d+\s*[A-Za-z]/', $fullText)) {
            return true;
        }

        return false;
    }

    /**
     * Jika nama beda → HANYA simpan mismatch, TIDAK masuk majalah
     */
    protected function importUnit(array $cells, string $fullText): void
    {
        $no            = $this->cleanValue($cells[0] ?? null);
        $namaUnit      = $this->cleanValue($cells[1] ?? null);
        $noCabang      = $this->cleanValue($cells[2] ?? null);
        $jumlahPesanan = $this->parseJumlah($cells[3] ?? null);
        $alamat        = $this->cleanValue($cells[4] ?? null);
        $telepon       = $this->cleanValue($cells[5] ?? null);

        if (empty($namaUnit) || empty($noCabang)) {
            if (preg_match('/^(\d+)\s*([A-Za-z][A-Za-z\s\.]+?)\s*(\d{2,5})\s*(\d{1,4})\s*(.+?)\s*(0\d{2,4}[\s\-]?\d{3,4}[\s\-]?\d{3,5}.*)$/i', $fullText, $m)) {
                $no            = $m[1];
                $namaUnit      = trim($m[2]);
                $noCabang      = $m[3];
                $jumlahPesanan = (int) $m[4];
                $alamat        = trim($m[5]);
                $telepon       = trim($m[6]);
            } elseif (preg_match('/^(\d+)\s*([A-Za-z].+?)\s+(\d{2,5})\s+(\d{1,4})\s+(.+)$/i', $fullText, $m)) {
                $no            = $m[1];
                $namaUnit      = trim($m[2]);
                $noCabang      = $m[3];
                $jumlahPesanan = (int) $m[4];
                $sisa          = trim($m[5]);

                if (preg_match('/(0\d{2,4}[\s\-]?\d{3,4}[\s\-]?\d{3,5}.*)$/', $sisa, $tel)) {
                    $telepon = trim($tel[1]);
                    $alamat  = trim(str_replace($telepon, '', $sisa));
                } else {
                    $alamat = $sisa;
                }
            }
        }

        if (empty($namaUnit)) {
            return;
        }

        $noCab = trim($noCabang ?? '');
        $namaFinal = $namaUnit; // default: pakai nama dari Excel

        // =====================================================
        // CEK MISMATCH → TETAP SIMPAN UNIT + CATAT MISMATCH
        // =====================================================
        if ($noCab !== '') {
            $uk = UnitKemitraan::where('no_cab', $noCab)->first();

            if ($uk && !empty($uk->bimba_aiueo_unit)) {
                $namaMaster = trim($uk->bimba_aiueo_unit);

                if (!$this->isNamaUnitMirip($namaUnit, $namaMaster)) {
                    // 1. Catat mismatch
                    $this->mismatchList[] = [
                        'no_cab'      => $noCab,
                        'nama_excel'  => $namaUnit,
                        'nama_master' => $namaMaster,
                    ];

                    UnitNamaMismatch::updateOrCreate(
                        [
                            'no_cab'  => $noCab,
                            'periode' => $this->pesananMajalah->periode,
                            'sumber'  => 'import_kabupaten',
                        ],
                        [
                            'nama_excel'         => $namaUnit,
                            'nama_master'        => $namaMaster,
                            'pesanan_majalah_id' => $this->pesananMajalah->id,
                            'is_resolved'        => false,
                        ]
                    );

                    // 2. Tetap pakai nama master (atau ganti ke $namaUnit kalau mau tetap Excel)
                    $namaFinal = $namaMaster;
                } else {
                    // Nama mirip → pakai nama master
                    $namaFinal = $namaMaster;
                }
            }
        }

        // =====================================================
        // SELALU SIMPAN UNIT (match maupun tidak match)
        // =====================================================
        PesananMajalahUnit::create([
            'pesanan_majalah_kabupaten_id' => $this->kabupatenAktif->id,
            'no'                           => is_numeric($no) ? (int) $no : null,
            'nama_unit'                    => $namaFinal,
            'no_cabang'                    => $noCabang,
            'jumlah_pesanan'               => $jumlahPesanan,
            'alamat_unit'                  => $alamat,
            'telepon'                      => $telepon,
        ]);
    }

    /**
 * Nama dianggap MIRIP hanya jika:
 * - Sama persis setelah normalisasi
 * - Sama setelah spasi dihilangkan (compact)
 * - Sangat mirip secara similarity (≥ 95%) ATAU jarak Levenshtein sangat kecil (≤ 5%)
 *
 * Extra angka/suffix seperti "04", "01", dll akan membuatnya BERBEDA.
 */
protected function isNamaUnitMirip(string $namaA, string $namaB): bool
{
    $norm = function (string $s): string {
        $s = strtolower(trim($s));
        // Hapus tanda baca, ganti jadi spasi
        $s = preg_replace('/[()\[\].,\-_\/\\\\]+/', ' ', $s);
        // Collapse spasi
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    };

    $a = $norm($namaA);
    $b = $norm($namaB);

    // Kosong / strip dianggap match (biar tidak memblokir data aneh)
    if ($a === '' || $b === '' || $a === '-' || $b === '-') {
        return true;
    }

    // 1. Exact match setelah normalisasi
    if ($a === $b) {
        return true;
    }

    // 2. Compact (tanpa spasi) exact
    $ca = preg_replace('/\s+/', '', $a);
    $cb = preg_replace('/\s+/', '', $b);

    if ($ca !== '' && $cb !== '' && $ca === $cb) {
        return true;
    }

    // 3. Similarity sangat tinggi (≥ 95%)
    similar_text($a, $b, $percent);
    if ($percent >= 95) {
        return true;
    }

    // 4. Levenshtein relatif sangat kecil (≤ 5% dari panjang terpanjang)
    $maxLen = max(mb_strlen($a), mb_strlen($b));
    if ($maxLen > 0) {
        $dist = levenshtein($a, $b);
        if (($dist / $maxLen) <= 0.05) {
            return true;
        }
    }

    // Selain itu → dianggap BERBEDA (mismatch)
    return false;
}

    protected function parseJumlah($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $str = trim((string) $value);
        $str = preg_replace('/[^\d,.-]/', '', $str);

        if ($str === '' || $str === '-' || $str === '.' || $str === ',') {
            return 0;
        }

        if (str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } else {
            if (substr_count($str, '.') === 1) {
                $parts = explode('.', $str);
                if (strlen($parts[1]) > 2) {
                    $str = str_replace('.', '', $str);
                }
            } else {
                $str = str_replace('.', '', $str);
            }
        }

        return is_numeric($str) ? (float) $str : 0;
    }

    protected function parsePeriode(string $text): array
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

    protected function cleanValue($value): ?string
    {
        if ($value === null || $value === '') return null;
        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }
}