<?php

namespace App\Imports;

use App\Models\PesananMajalah;
use App\Models\PesananMajalahKabupaten;
use App\Models\PesananMajalahUnit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PesananMajalahImport implements ToCollection, WithCalculatedFormulas
{
    protected PesananMajalah $pesananMajalah;
    protected ?PesananMajalahKabupaten $kabupatenAktif = null;
    protected int $urutanKabupaten = 0;

    public function __construct(PesananMajalah $pesananMajalah)
    {
        $this->pesananMajalah = $pesananMajalah;
    }

    public function collection(Collection $rows)
{
    // =========================================================
    // HAPUS DATA LAMA PERIODE INI (supaya re-import bersih)
    // =========================================================
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

    $this->kabupatenAktif = null;
    $this->urutanKabupaten = 0;

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

        // Skip baris kosong
        if (empty(array_filter($cells, fn($v) => $v !== ''))) {
            continue;
        }

        $fullText = strtoupper(trim(implode(' ', array_filter($cells, fn($v) => $v !== ''))));

        // Skip baris TOTAL
        if (str_contains($fullText, 'TOTAL')) {
            continue;
        }

        // =========================================================
        // BARIS JUDUL
        // =========================================================
        if ($judul === null && str_contains($fullText, 'PESANAN MAJALAH')) {
            $judul = trim(implode(' ', array_filter($cells, fn($v) => $v !== '')));
            continue;
        }

        // =========================================================
        // BARIS PERIODE
        // =========================================================
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

        // =========================================================
        // DETEKSI KABUPATEN
        // =========================================================
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

        // =========================================================
        // DETEKSI CONTACT PERSON
        // =========================================================
        if ($this->kabupatenAktif && (
            preg_match('/CONTACT\s*PERSON\s*[:\-]?\s*(.+)$/i', $fullText, $matches) ||
            preg_match('/\b(1\.\s*IBU.+|IBU\s+[A-Z]+.+\(.+\))/i', $fullText, $matches)
        )) {
            $cp = trim($matches[1] ?? $matches[0]);
            $this->kabupatenAktif->update(['contact_person' => $cp]);
            continue;
        }

        // =========================================================
        // DATA UNIT
        // =========================================================
        if ($this->kabupatenAktif && $this->isUnitRow($cells, $fullText)) {
            $this->importUnit($cells, $fullText);
            continue;
        }
    }

    // Update header
    $updateData = array_filter([
        'judul'   => $judul,
        'bulan'   => $bulan,
        'tahun'   => $tahun,
        'periode' => $periode,
    ], fn($v) => $v !== null);

    if (!empty($updateData)) {
        $this->pesananMajalah->update($updateData);
    }
}

    /**
     * Cek apakah baris adalah data unit
     */
    protected function isUnitRow(array $cells, string $fullText): bool
    {
        // Cara 1: kolom pertama angka
        $no = trim($cells[0] ?? '');
        if ($no !== '' && is_numeric($no)) {
            return true;
        }

        // Cara 2: fullText dimulai dengan angka
        if (preg_match('/^\d+\s*[A-Za-z]/', $fullText)) {
            return true;
        }

        return false;
    }

    /**
     * Import data unit (mendukung data yang menempel)
     */
    protected function importUnit(array $cells, string $fullText): void
{
    $no            = $this->cleanValue($cells[0] ?? null);
    $namaUnit      = $this->cleanValue($cells[1] ?? null);
    $noCabang      = $this->cleanValue($cells[2] ?? null);
    $jumlahPesanan = $this->parseJumlah($cells[3] ?? null);
    $alamat        = $this->cleanValue($cells[4] ?? null);
    $telepon       = $this->cleanValue($cells[5] ?? null);

    // Hanya pakai regex jika kolom rapi gagal (nama/no cabang kosong)
    // JANGAN trigger hanya karena jumlah = 0
    if (empty($namaUnit) || empty($noCabang)) {

        if (preg_match('/^(\d+)\s*([A-Za-z][A-Za-z\s\.]+?)\s*(\d{2,5})\s*(\d{1,4})\s*(.+?)\s*(0\d{2,4}[\s\-]?\d{3,4}[\s\-]?\d{3,5}.*)$/i', $fullText, $m)) {
            $no            = $m[1];
            $namaUnit      = trim($m[2]);
            $noCabang      = $m[3];
            $jumlahPesanan = (int) $m[4];
            $alamat        = trim($m[5]);
            $telepon       = trim($m[6]);
        }
        elseif (preg_match('/^(\d+)\s*([A-Za-z].+?)\s+(\d{2,5})\s+(\d{1,4})\s+(.+)$/i', $fullText, $m)) {
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

    PesananMajalahUnit::create([
        'pesanan_majalah_kabupaten_id' => $this->kabupatenAktif->id,
        'no'                           => is_numeric($no) ? (int) $no : null,
        'nama_unit'                    => $namaUnit,
        'no_cabang'                    => $noCabang,
        'jumlah_pesanan'               => $jumlahPesanan, // boleh 0
        'alamat_unit'                  => $alamat,
        'telepon'                      => $telepon,
    ]);
}

    /**
 * Parse jumlah pesanan agar pure sama dengan Excel + pembulatan benar
 * Contoh:
 * 44,8 → 45
 * 44,5 → 45
 * 44,4 → 44
 * 33,6 → 34
 */
/**
 * Parse jumlah pesanan - simpan nilai asli dari Excel (boleh desimal)
 */
protected function parseJumlah($value): float
{
    if ($value === null || $value === '') {
        return 0;
    }

    // Jika sudah angka murni (float/int dari Excel)
    if (is_numeric($value)) {
        return (float) $value;
    }

    $str = trim((string) $value);

    // Hapus karakter selain angka, koma, titik, dan minus
    $str = preg_replace('/[^\d,.-]/', '', $str);

    if ($str === '' || $str === '-' || $str === '.' || $str === ',') {
        return 0;
    }

    // Deteksi format Indonesia (koma sebagai desimal)
    if (str_contains($str, ',')) {
        // Hapus titik (pemisah ribuan)
        $str = str_replace('.', '', $str);
        // Ganti koma menjadi titik
        $str = str_replace(',', '.', $str);
    } else {
        // Tidak ada koma
        if (substr_count($str, '.') === 1) {
            $parts = explode('.', $str);
            // Jika setelah titik lebih dari 2 digit → anggap pemisah ribuan
            if (strlen($parts[1]) > 2) {
                $str = str_replace('.', '', $str);
            }
        } else {
            // Banyak titik → pemisah ribuan
            $str = str_replace('.', '', $str);
        }
    }

    if (is_numeric($str)) {
        return (float) $str;
    }

    return 0;
}

    /**
     * Parse periode
     */
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