<?php

namespace App\Modules\Kalender\Support;

final class KodeKalender
{
    /** 2027 / "kld27" / "KLD 27" => "KLD27". Selain itu dikembalikan apa adanya (huruf besar). */
    public static function normalize($v): string
    {
        $v = strtoupper(trim((string) $v));

        if (preg_match('/^20(\d{2})$/', $v, $m) || preg_match('/^KLD\s*(\d{2})$/', $v, $m)) {
            return 'KLD' . $m[1];
        }

        return $v;
    }

    public static function tahun($v): ?int
    {
        return preg_match('/^KLD(\d{2})$/', self::normalize($v), $m) ? 2000 + (int) $m[1] : null;
    }

    public static function dariTahun($tahun): ?string
    {
        return preg_match('/^20(\d{2})$/', (string) $tahun, $m) ? 'KLD' . $m[1] : null;
    }

    /** Untuk query: ['KLD27', '2027'] supaya data lama tetap terbaca. */
    public static function varian($v): array
    {
        $n = self::normalize($v);
        $t = self::tahun($n);

        return $t ? [$n, (string) $t] : [$n];
    }
}
