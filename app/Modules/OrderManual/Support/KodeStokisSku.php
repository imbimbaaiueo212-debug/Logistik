<?php

namespace App\Modules\OrderManual\Support;

/**
 * Baca kode stokis & edisi dari SKU produk (mis. KLD27-JKT, KLD27-TNG1, KLD27-SMG).
 * Aturan sama dengan Report Angka Cetak Kalender: kode dibaca dari potongan SKU.
 *
 *  JKT         -> Stokis Jakarta (Jakarta)
 *  SMG/SBY/..  -> Stokis Pasif   (Logistik)
 *  TNG1/BKS1.. -> Stokis Aktif   (Logistik)
 *  PUA1..PUA3  -> Pengelola Unit Aktif Khusus (Logistik)
 */
final class KodeStokisSku
{
    public const PASIF = ['SMG', 'SBY', 'SGN', 'YYK'];

    public const AKTIF = [
        'TNG1', 'BKS1', 'SRG1', 'TNG2', 'BGR1', 'BGRT', 'DPK1', 'SKB1', 'KNG', 'IDM', 'SKB2', 'SNG',
        'KWG1', 'PWK', 'BDG1', 'BDG2', 'CIL1', 'SRG2', 'DPR1', 'KWG2', 'BGR3', 'YYK1', 'CRB1', 'TNG3', 'PKL1',
    ];

    public const PENGELOLA = ['PUA1', 'PUA2', 'PUA3'];

    /** @return string[] potongan SKU huruf besar */
    public static function token(?string $sku): array
    {
        return preg_split('/[^A-Z0-9]+/', strtoupper((string) $sku), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** Kode stokis dari SKU: kode logistik bila ada, 'JKT' bila Jakarta, selain itu null. */
    public static function kode(?string $sku): ?string
    {
        $token = self::token($sku);

        foreach (array_merge(self::PASIF, self::AKTIF, self::PENGELOLA) as $kode) {
            if (in_array($kode, $token, true)) {
                return $kode;
            }
        }

        return in_array('JKT', $token, true) ? 'JKT' : null;
    }

    /** true bila kode termasuk logistik (bukan Jakarta). */
    public static function logistik(?string $kode): bool
    {
        return $kode !== null && $kode !== 'JKT';
    }

    /** Edisi = potongan pertama SKU (KLD27-JKT -> KLD27). */
    public static function edisi(?string $sku): ?string
    {
        return self::token($sku)[0] ?? null;
    }
}
