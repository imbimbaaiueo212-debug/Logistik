<?php

namespace App\Modules\Order\Support;

use App\Modules\Import\Models\BimbashopOrder;

/**
 * Membaca "Shipping Method" dari biMBA Shop (mis. "JNE REG") lalu memecahnya
 * menjadi Jasa Kurir (JNE) dan Service (REG) untuk modal Edit & Proses.
 */
class ShippingMethod
{
    /** Nama kurir yang dikenali => pola awal teks. */
    private const KURIR = [
        'Lion Parcel'    => '/^lion\s*parcel\b/i',
        'J&T'            => '/^j\s*&\s*t\b|^jnt\b|^j\s*and\s*t\b/i',
        'JNE'            => '/^jne\b/i',
        'TIKI'           => '/^tiki\b/i',
        'SiCepat'        => '/^si\s*cepat\b/i',
        'AnterAja'       => '/^anter\s*aja\b/i',
        'Ninja Xpress'   => '/^ninja(\s*xpress)?\b/i',
        'POS Indonesia'  => '/^pos(\s*indonesia)?\b/i',
        'Wahana'         => '/^wahana\b/i',
        'SAP'            => '/^sap\b/i',
        'ID Express'     => '/^id\s*express\b/i',
        'Sentral Cargo'  => '/^sentral\s*cargo\b/i',
    ];

    /** @var array<string, string|null> cache per invoice agar tidak query berulang */
    private static array $cache = [];

    /** "JNE REG" => ['JNE', 'REG']; tidak dikenali / bukan kurir => [null, null]. */
    public static function pecah(?string $teks): array
    {
        $teks = trim((string) preg_replace('/\s+/', ' ', (string) $teks));
        if ($teks === '') {
            return [null, null];
        }

        // Ambil di tempat
        if (preg_match('/ambil|pick\s*up|pickup|di\s*ambil/i', $teks)) {
            return ['Diambil Sendiri', null];
        }

        // Bukan jasa kurir sungguhan
        if (preg_match('/^(flat\s*rate|free\s*shipping|gratis|tidak ada|-)$/i', $teks)) {
            return [null, null];
        }

        foreach (self::KURIR as $nama => $pola) {
            if (preg_match($pola, $teks, $m)) {
                return [$nama, self::bersihkanService(substr($teks, strlen($m[0])))];
            }
        }

        // Kurir lain: kata pertama = kurir, sisanya = service
        $bagian = explode(' ', $teks, 2);

        return [$bagian[0], self::bersihkanService($bagian[1] ?? '')];
    }

    /**
     * Cari Shipping Method di biMBA Shop berdasarkan nomor invoice (id_pesan) dan pecah.
     * Kembali [kurir, service]; [null, null] bila tidak ada.
     */
    public static function dariInvoice(?string $idPesan): array
    {
        $teks = self::teksUntuk($idPesan);

        return self::pecah($teks);
    }

    private static function teksUntuk(?string $idPesan): ?string
    {
        $idPesan = trim((string) $idPesan);
        if ($idPesan === '' || $idPesan === '-') {
            return null;
        }
        if (array_key_exists($idPesan, self::$cache)) {
            return self::$cache[$idPesan];
        }

        // id_pesan kadang berisi lebih dari satu order ("873844, 873845")
        $kandidat = array_values(array_filter(preg_split('/[\s,;+\/]+/', $idPesan)));

        $teks = BimbashopOrder::query()
            ->whereIn('order_id', $kandidat)
            ->whereNotNull('shipping_method')
            ->where('shipping_method', '!=', '')
            ->value('shipping_method');

        return self::$cache[$idPesan] = $teks;
    }

    private static function bersihkanService(string $sisa): ?string
    {
        $sisa = trim($sisa, " \t-:()[]");

        if ($sisa === '') {
            return null;
        }

        // service kurir pendek (reg, yes, ctc) ditulis kapital seperti biasa
        return mb_strlen($sisa) <= 10 ? mb_strtoupper($sisa) : $sisa;
    }
}
