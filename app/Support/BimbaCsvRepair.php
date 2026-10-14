<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Memperbaiki CSV export biMBA Shop yang "rusak" akibat disimpan ulang di Excel:
 *
 *  - setiap baris data dibungkus tanda kutip dan diakhiri ";;"
 *    contoh:  "7364,7364,""30 Ilir"",...,ID";;
 *  - alamat yang berisi enter memecah satu baris menjadi beberapa baris.
 *
 * Hasilnya ditulis ke file CSV sementara yang rapi (pemisah koma, 50 kolom),
 * lalu file itu yang diimport. File asli tidak diubah.
 */
class BimbaCsvRepair
{
    /** Jumlah kolom yang diharapkan sesuai header export. */
    private const EXPECTED_COLUMNS = 50;

    /** Mulai kolom ke-30 (index 29) adalah data billing/shipping. */
    private const ADDRESS_START_INDEX = 29;

    /**
     * Apakah file ini berformat rusak (baris data dibungkus kutip)?
     */
    public static function isWrapped(string $path): bool
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        fgets($handle); // lewati header

        $line = false;
        while (($raw = fgets($handle)) !== false) {
            if (trim($raw) !== '') {
                $line = $raw;
                break;
            }
        }

        fclose($handle);

        return $line !== false && preg_match('/^"\d+,\d*,/', $line) === 1;
    }

    /**
     * Baca file rusak, tulis versi bersihnya ke file sementara, kembalikan path-nya.
     */
    public static function repairToTempFile(string $path): string
    {
        $in = fopen($path, 'rb');

        if ($in === false) {
            throw new \RuntimeException('File CSV tidak bisa dibaca.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'bimba_csv_');
        $out = fopen($tmp, 'wb');

        if ($out === false) {
            fclose($in);
            throw new \RuntimeException('File sementara tidak bisa dibuat.');
        }

        // Header ditulis apa adanya (tanpa BOM)
        $header = (string) fgets($in);
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        fwrite($out, rtrim($header, "\r\n") . "\r\n");

        $current = null; // isi baris yang sedang disusun
        $pieces  = 0;    // berapa baris fisik yang disambung
        $written = 0;

        while (($raw = fgets($in)) !== false) {
            $line = rtrim($raw, "\r\n");

            if ($line === '') {
                continue;
            }

            // Buang ";;" di ujung, lalu pembungkus kutip luar
            $t = rtrim($line, ';');
            $c = (strlen($t) >= 2 && $t[0] === '"' && substr($t, -1) === '"')
                ? substr($t, 1, -1)
                : $t;

            // Baris baru selalu diawali "ID,customer_id," (angka,angka/kosong,)
            $isNewRow = preg_match('/^\d+,\d*,/', $c) === 1;

            if ($isNewRow || $current === null) {
                if ($current !== null) {
                    self::writeRow($out, $current, $pieces);
                    $written++;
                }
                $current = $c;
                $pieces  = 1;
            } else {
                // Lanjutan dari baris sebelumnya (alamat berisi enter).
                // Jika diawali koma, tanda kutip penutup sel alamat hilang
                // di data sumber, jadi kita tutup dulu.
                $current .= ($c !== '' && $c[0] === ',') ? '"' . $c : ' ' . $c;
                $pieces++;
            }
        }

        if ($current !== null) {
            self::writeRow($out, $current, $pieces);
            $written++;
        }

        fclose($in);
        fclose($out);

        Log::info("BimbaCsvRepair: {$written} baris data dibersihkan.");

        return $tmp;
    }

    private static function writeRow($out, string $content, int $pieces): void
    {
        // Kutip yang digandakan ("") dikembalikan menjadi satu (")
        $content = str_replace('""', '"', $content);

        $row = str_getcsv($content, ',', '"', '');

        // Baris yang tadinya terpecah: bersihkan sisa tanda kutip di data alamat
        if ($pieces > 1) {
            foreach ($row as $i => $value) {
                if ($i >= self::ADDRESS_START_INDEX) {
                    $row[$i] = trim(trim(trim((string) $value), '"'));
                }
            }
        }

        if (count($row) !== self::EXPECTED_COLUMNS) {
            Log::warning('BimbaCsvRepair: jumlah kolom ' . count($row)
                . ' (seharusnya ' . self::EXPECTED_COLUMNS . ') pada ID ' . ($row[0] ?? '?'));
        }

        fputcsv($out, $row, ',', '"', '');
    }
}
