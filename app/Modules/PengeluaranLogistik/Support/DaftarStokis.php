<?php

namespace App\Modules\PengeluaranLogistik\Support;

/**
 * Daftar kode stokis (token SKU) dan nama stokis.
 * Disamakan dengan Report Angka Cetak Kalender (KalenderReportAngkaCetakController),
 * jadi stokis yang dikenali di kedua report sama (mis. DPK, BKS tanpa angka 1).
 */
final class DaftarStokis
{
    public const PASIF = [
        'SMG' => 'Semarang', 'SBY' => 'Surabaya', 'SGN' => 'Sragen', 'YYK' => 'Yogyakarta',
        'JKTP' => 'Jakarta Pasif',
    ];

    public const AKTIF = [
        'TNG1' => 'Tangerang 1', 'BKS' => 'Bekasi 1', 'SRG1' => 'Serang 1',
        'TNG2' => 'Tangerang 2', 'BGR1' => 'Bogor 1', 'BGR2' => 'Bogor 2',
        'DPK' => 'Depok 1', 'SKB1' => 'Sukabumi 1', 'KNG' => 'Kuningan 1',
        'IDM' => 'Indramayu 1', 'SKB2' => 'Sukabumi 2', 'SNG' => 'Subang 1',
        'KWG1' => 'Karawang 1', 'PWK' => 'Purwakarta 1', 'BDG1' => 'Bandung 1',
        'BDG2' => 'Bandung 2', 'CIL1' => 'Cilincing 1', 'SRG2' => 'Serang 2',
        'DPR1' => 'Denpasar 1', 'KWG2' => 'Karawang 2', 'BGR3' => 'Bogor 3',
        'YYK1' => 'Yogyakarta 1', 'CRB1' => 'Cirebon 1',
        'TNG3' => 'Tangerang 3', 'PKL1' => 'Pekalongan 1',
        'JKT' => 'Jakarta',
    ];

    public const PENGELOLA = [
        'PUA1' => 'Pengelola Unit Aktif Khusus 1',
        'PUA2' => 'Pengelola Unit Aktif Khusus 2',
        'PUA3' => 'Pengelola Unit Aktif Khusus 3',
    ];

    /** Stokis internal (nama billing "Stokis Inventaris biMBA AIUEO 372"). */
    public const INVENTARIS = [
        'INV' => 'Inventaris',
    ];

    /** Rumah Jakarta pasif lain: bukan stokis logistik. (JKT dan JKTP sekarang dihitung sebagai stokis.) */
    public const BUKAN_STOKIS = ['DLC', 'EBT', 'INV', 'BSS'];

    /** @return array<string,string> kode => nama, semua kelompok */
    public static function semua(): array
    {
        return self::PASIF + self::AKTIF + self::PENGELOLA + self::INVENTARIS;
    }

    public static function nama(string $kode): string
    {
        return self::semua()[$kode] ?? $kode;
    }

    public static function kelompok(string $kode): string
    {
        return match (true) {
            isset(self::PASIF[$kode])     => 'Stokis Pasif',
            isset(self::AKTIF[$kode])     => 'Stokis Aktif',
            isset(self::PENGELOLA[$kode]) => 'Pengelola Unit Aktif Khusus',
            isset(self::INVENTARIS[$kode]) => 'Inventaris',
            default                       => 'Lainnya',
        };
    }

    /** Urutan tampil kelompok. */
    public static function urutKelompok(string $kode): int
    {
        return match (self::kelompok($kode)) {
            'Stokis Aktif'                => 1,
            'Stokis Pasif'                => 2,
            'Pengelola Unit Aktif Khusus' => 3,
            'Inventaris'                  => 4,
            default                       => 9,
        };
    }

    /** @return string[] potongan SKU huruf besar */
    public static function token(?string $sku): array
    {
        return preg_split('/[^A-Z0-9]+/', strtoupper((string) $sku), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}