<?php

namespace App\Modules\Order\Support;

use App\Modules\Import\Services\ShopMatcher;

/**
 * Kategori order untuk Rekap Aktual / Picking: Modul, Majalah, Kalender, Sertifikat.
 *
 * Kalender TIDAK digabung ke Modul. Status HOLD "barang belum datang" hanya berlaku
 * untuk Majalah dan Kalender; Modul, ATK, Sertifikat dan lainnya langsung PROSES.
 */
final class KategoriOrder
{
    public const MODUL      = 'Modul';
    public const MAJALAH    = 'Majalah';
    public const KALENDER   = 'Kalender';
    public const SERTIFIKAT = 'Sertifikat';

    /** Urutan kelompok saat Rekap Aktual dibuat. */
    public const SEMUA = [self::MODUL, self::MAJALAH, self::KALENDER, self::SERTIFIKAT];

    /** Kategori yang boleh berstatus HOLD (barang belum datang). */
    public const BISA_HOLD = [self::MAJALAH, self::KALENDER];

    /**
     * Tentukan kategori satu item order (JakartaXxxItem).
     * Urutan: kategori produk di Master -> deteksi Kalender lewat SKU / nama -> Modul.
     */
    public static function dari($item): string
    {
        $kategori = strtolower(trim((string) ($item->product?->kategori ?? '')));

        if (str_contains($kategori, 'kalender')) {
            return self::KALENDER;
        }
        if (str_contains($kategori, 'majalah')) {
            return self::MAJALAH;
        }
        if (str_contains($kategori, 'sertifikat')) {
            return self::SERTIFIKAT;
        }

        // Kategori produk belum terisi / "Modul": cek apakah sebenarnya Kalender.
        if (self::itemKalender($item->sku ?? null, $item->nama_produk ?? null, $item->label ?? null)) {
            return self::KALENDER;
        }

        return self::MODUL;
    }

    public static function bisaHold(?string $kategori): bool
    {
        return in_array((string) $kategori, self::BISA_HOLD, true);
    }

    private static function itemKalender(?string $sku, ?string $nama, ?string $label): bool
    {
        if (str_contains(strtolower((string) $nama . ' ' . (string) $label), 'kalender')) {
            return true;
        }

        $sku = strtoupper(trim((string) $sku));
        foreach (ShopMatcher::KALENDER_SKU_PREFIX as $prefix) {
            if ($sku !== '' && str_starts_with($sku, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
