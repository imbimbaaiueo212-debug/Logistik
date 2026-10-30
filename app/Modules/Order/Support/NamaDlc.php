<?php

namespace App\Modules\Order\Support;

use App\Modules\Import\Models\DlcPesanan;

/**
 * Merapikan Billing Name biMBA Shop untuk order DLC.
 *
 *   Shop   : "Raihana Anaya Fauzia 2079 Helena Helen Anggrek 5"
 *             <nama murid> <NIM 4 digit> <nama guru> <unit>
 *   Hasil  : "Raihana Anaya Fauzia 2079 Anggrek 5"   (nama guru dibuang)
 *
 * Unit dikenali dari daftar unit DLC (dlc_pesanan.nama_unit) dan dicari di UJUNG nama.
 * NIM selalu 4 digit. Kalau unit tidak dikenali, nama dikembalikan apa adanya
 * dan unit = null, supaya order bisa ditampilkan di daftar "Tidak dikenali".
 */
final class NamaDlc
{
    /** @var array<int,string>|null */
    private static ?array $unitCache = null;

    /** Daftar unit DLC yang dikenal (nama unit unik dari semua periode). */
    public static function unitDikenal(): array
    {
        return self::$unitCache ??= DlcPesanan::query()
            ->whereNotNull('nama_unit')
            ->where('nama_unit', '!=', '')
            ->distinct()
            ->pluck('nama_unit')
            ->map(fn ($u) => self::rapikan($u))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function lupakanCache(): void
    {
        self::$unitCache = null;
    }

    /**
     * Rapikan teks: semua jenis spasi (termasuk NBSP dan karakter lebar-nol dari
     * hasil export Excel / WooCommerce) menjadi satu spasi biasa.
     */
    public static function rapikan(?string $teks): string
    {
        $teks = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $teks) ?? '';
        $teks = preg_replace('/[\s\p{Z}]+/u', ' ', $teks) ?? '';

        return trim($teks);
    }

    /** Kunci banding unit: huruf kecil, spasi tunggal. */
    public static function kunciUnit(?string $unit): string
    {
        return mb_strtolower(self::rapikan($unit));
    }

    /**
     * @param  array<int,string>|null $units  null = pakai daftar dari database
     * @return array{unit:?string, nama:string, guru:string}
     */
    public static function pisah(?string $billingName, ?array $units = null): array
    {
        $teks = self::rapikan($billingName);
        $units = array_map(fn ($u) => self::rapikan($u), $units ?? self::unitDikenal());

        if ($teks === '') {
            return ['unit' => null, 'nama' => '', 'guru' => ''];
        }

        // Unit terpanjang dulu, supaya "Anggrek 12" tidak kalah oleh "Anggrek 1".
        usort($units, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $unit = null;
        $sisa = $teks;
        foreach ($units as $u) {
            if ($u === '') {
                continue;
            }
            $panjang = mb_strlen($u);
            if (mb_strtolower(mb_substr($teks, -$panjang)) === mb_strtolower($u)) {
                // pastikan batas kata: sebelum unit harus spasi atau awal teks
                $sebelum = mb_substr($teks, 0, mb_strlen($teks) - $panjang);
                if ($sebelum === '' || str_ends_with($sebelum, ' ')) {
                    $unit = $u;
                    $sisa = trim($sebelum);
                    break;
                }
            }
        }

        if ($unit === null) {
            return ['unit' => null, 'nama' => $teks, 'guru' => ''];
        }

        // NIM = angka tepat 4 digit pertama; murid = sampai NIM, sisanya guru.
        if (preg_match('/^(.*?(?<!\d)\d{4}(?!\d))(.*)$/us', $sisa, $m)) {
            $murid = trim($m[1]);
            $guru  = trim($m[2]);

            return ['unit' => $unit, 'nama' => trim($murid . ' ' . $unit), 'guru' => $guru];
        }

        // Tanpa NIM 4 digit: jangan menebak, pertahankan nama apa adanya.
        return ['unit' => $unit, 'nama' => $teks, 'guru' => ''];
    }

    /** Nama unit DLC siap pakai (tanpa nama guru). */
    public static function bersih(?string $billingName, ?array $units = null): string
    {
        return self::pisah($billingName, $units)['nama'];
    }
}
