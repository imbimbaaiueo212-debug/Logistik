<?php

namespace App\Modules\Import\Support;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\OrderManual\Services\CetakShopMatcher;
use App\Modules\OrderManual\Support\KodeStokisSku;
use App\Modules\PengeluaranLogistik\Support\DaftarStokis;

/**
 * Aturan hitung order logistik biMBA Shop untuk Report Angka Cetak Majalah (A3 & A4).
 * Satu-satunya tempat aturan ini ditulis: dipakai oleh ReportAngkaCetakController
 * (angka) dan RumahAngkaCetakController (rincian per order), jadi keduanya pasti sama.
 *
 * Grup hasil: stokis (A3), pengelola (A4), puw (di A1, tidak dihitung di sini),
 *             lg_bukan_stokis (SKU LG tapi nama bukan stokis, tidak dihitung), batal (status batal).
 */
final class AngkaCetakLogistik
{
    /** Nama stokis pasif (dikeluarkan dari A3). "YOGYAKARTA" tanpa angka 1 = pasif. */
    private const KATA_PASIF = ['SRAGEN', 'SEMARANG', 'SURABAYA', 'JAKARTA PASIF'];

    /** Status Casdana yang dianggap sudah dibayar. */
    private const KASDANA_LUNAS = ['SUCCESS', 'SETTLED'];

    /** Kode SKU yang dibaca: LG (A3 & A4) + PUA1..3 (A4). SKU kode stokis = order unit ke stokis, bukan A3. */
    public static function kodeSku(): array
    {
        return array_merge(['LG'], KodeStokisSku::PENGELOLA);
    }

    /** Order biMBA Shop satu edisi untuk SKU logistik (belum diklasifikasi). */
    public static function query(string $edisi)
    {
        $kodeSku = self::kodeSku();

        return BimbashopOrder::query()->where(function ($q) use ($edisi, $kodeSku) {
            foreach ($kodeSku as $k) {
                $q->orWhere('item_sku', $edisi . '-' . $k);
            }
        });
    }

    /** order_id => status Casdana terbaru (invoice "ID877596" == order_id "877596"). */
    public static function kasdanaMap(iterable $orderIds): array
    {
        $ids = collect($orderIds)->map(fn ($i) => trim((string) $i))->filter()->unique()->values()->all();
        $map = [];

        foreach (array_chunk($ids, 500) as $chunk) {
            $invoice = array_merge($chunk, array_map(fn ($i) => 'ID' . $i, $chunk));

            CasdanaTransaction::whereIn('invoice_number', $invoice)
                ->orderBy('id')
                ->get(['invoice_number', 'status'])
                ->each(function ($c) use (&$map) {
                    $oid = preg_replace('/^ID/i', '', strtoupper(trim((string) $c->invoice_number)));
                    $map[$oid] = strtoupper(trim((string) $c->status));
                });
        }

        return $map;
    }

    private static function stokisPasif(string $nama): bool
    {
        foreach (self::KATA_PASIF as $k) {
            if (str_contains($nama, $k)) {
                return true;
            }
        }

        // Yogyakarta tanpa angka 1 = stokis pasif (SKU YYK)
        return (bool) preg_match('/YOGYAKARTA(?!\s*1)/', $nama);
    }

    /**
     * Klasifikasi satu baris order.
     * A3: semua order SKU {EDISI}-LG (kecuali nama billing mengandung Pengelola), dan sudah dibayar
     *     (ada di Casdana berstatus SUCCESS/SETTLED, atau bayar di luar Casdana: wallet/COD/other).
     *
     * @param array $kasdana order_id => status Casdana (dari kasdanaMap)
     */
    public static function klasifikasi($o, array $kasdana = []): array
    {
        $status = preg_replace('/^wc-/', '', strtolower(trim((string) $o->status)));
        $metode = strtolower((string) $o->payment_method);
        $qty    = max((int) $o->item_qty, 0);
        $nama   = strtoupper(preg_replace('/\s+/', ' ', trim(implode(' ', array_filter([
            $o->billing_first_name, $o->billing_last_name, $o->billing_company,
        ])))));
        $sku    = strtoupper(trim((string) $o->item_sku));
        $isLg   = str_ends_with($sku, '-LG');
        $kode   = substr($sku, (int) strrpos($sku, '-') + 1);
        $oid    = trim((string) $o->order_id);

        $base = ['qty' => $qty, 'status' => $status, 'nama' => $nama, 'kode' => $kode, 'lewat_casdana_bukan' => false];

        if (preg_match('/cancel|fail|refund|trash/', $status)) {
            return $base + ['grup' => 'batal', 'dihitung' => false, 'alasan' => 'Status ' . $status . ' (dikecualikan)'];
        }

        $luarCasdana = CetakShopMatcher::bayarTanpaCasdana($o->payment_method) || str_contains($metode, 'deposit');

        // ---- A4: Pengelola Unit Aktif Khusus (aturan lama, tidak diubah) ----
        if (preg_match('/PENGELOLA\s+UNIT\s+AKTIF\s+KHUSUS/', $nama)) {
            $dibayar = in_array($status, ['processing', 'completed'], true) || $luarCasdana;

            if ($dibayar) {
                return $base + ['grup' => 'pengelola', 'dihitung' => true, 'alasan' => 'A4 - Pengelola Unit Aktif Khusus; status ' . $status];
            }

            return $base + ['grup' => 'belum_bayar', 'dihitung' => false, 'alasan' => 'A4 belum dibayar (status ' . $status . ')'];
        }
        if (preg_match('/(?<![A-Z0-9])PUW(?![A-Z0-9])|UNIT\s+WILAYAH|PENGELOLA/', $nama)) {
            return $base + ['grup' => 'puw', 'dihitung' => false, 'alasan' => 'Nama billing mengandung Pengelola / PUW (bukan A3)'];
        }

        if (! $isLg) {
            return $base + ['grup' => 'bukan_a3', 'dihitung' => false, 'alasan' => 'SKU ' . $kode . ' bukan LG (bukan A3)'];
        }

        // Pembayaran bacs = order unit pasif, tidak ditarik ke A3
        if ($metode === 'bacs') {
            return $base + ['grup' => 'bacs', 'dihitung' => false, 'alasan' => 'Pembayaran bacs (unit pasif, bukan A3)'];
        }

        // ---- A3: SEMUA order LG, kecuali yang nama billing-nya mengandung "Pengelola" (sudah disaring di atas) ----
        $base['kode'] = $nama;   // satu baris ringkasan per nama billing stokis
        $statusKasdana = $kasdana[$oid] ?? null;

        if ($statusKasdana !== null && in_array($statusKasdana, self::KASDANA_LUNAS, true)) {
            return $base + ['grup' => 'stokis', 'dihitung' => true, 'alasan' => 'A3 - Kasdana ' . $statusKasdana];
        }

        // Status order di biMBA Shop sudah processing/completed = sudah dibayar, walau Kasdana belum tercatat.
        if (in_array($status, ['processing', 'completed'], true)) {
            return $base + ['grup' => 'stokis', 'dihitung' => true,
                'alasan' => 'A3 - status ' . $status . ($statusKasdana ? ' (Kasdana ' . $statusKasdana . ')' : ', belum tercatat di Kasdana')];
        }
        // On-hold tetap ditarik (order LG sudah masuk ke logistik), diberi tanda supaya mudah dicek.
        if ($status === 'on-hold') {
            return $base + ['grup' => 'stokis', 'dihitung' => true,
                'alasan' => 'A3 - ON-HOLD' . ($statusKasdana ? ' (Kasdana ' . $statusKasdana . ')' : ', belum ada di Kasdana')];
        }
        if ($luarCasdana) {
            return ['lewat_casdana_bukan' => true] + $base + ['grup' => 'stokis', 'dihitung' => true,
                'alasan' => 'A3 - bayar di luar Casdana (' . ($metode ?: '-') . '), tidak ada di Kasdana'];
        }

        return $base + ['grup' => 'belum_kasdana', 'dihitung' => false,
            'alasan' => $statusKasdana === null
                ? 'LG stokis, TIDAK ada di Kasdana (belum dibayar)'
                : 'LG stokis, Kasdana status ' . $statusKasdana . ' (belum lunas)'];
    }

    /** Pola nama billing dari label stokis: "Tangerang 1" -> /TANGERANG\s*1(?![0-9])/ (cocok juga "TANGERANG1"). */
    private static function polaNama(string $label): string
    {
        $pola = str_replace(' ', '\\s*', preg_quote(strtoupper(trim($label)), '/'));

        return '/(?<![A-Z0-9])' . $pola . '(?![0-9])/';
    }

    /**
     * Rincian A3 dan A4 per stokis (HANYA untuk ditampilkan, tidak dijumlah lagi).
     * Memakai klasifikasi yang sama dengan hitung(), jadi jumlah rincian = A3 / A4.
     *
     * @param array<string,string> $petaA3 kode => nama stokis (mis. 'TNG1' => 'Tangerang 1')
     * @param array<string,string> $petaA4 kode => nama pengelola (mis. 'PUA1' => 'Pengelola Unit Aktif Khusus 1')
     * @return array{a3:array<string,int>, a3_lain:int, a4:array<string,int>, a4_lain:int}
     */
    public static function rincian(string $edisi, array $petaA3, array $petaA4): array
    {
        $hasil = ['a3' => [], 'a3_lain' => 0, 'a4' => [], 'a4_lain' => 0];

        $baris = self::query($edisi)->get(['order_id', 'status', 'payment_method', 'item_qty', 'item_sku',
            'billing_first_name', 'billing_last_name', 'billing_company']);

        $kasdana = self::kasdanaMap($baris->pluck('order_id'));

        foreach ($baris as $o) {
            $k = self::klasifikasi($o, $kasdana);
            if (! $k['dihitung']) {
                continue;
            }

            [$peta, $kunci, $lain] = $k['grup'] === 'stokis'
                ? [$petaA3, 'a3', 'a3_lain']
                : ($k['grup'] === 'pengelola' ? [$petaA4, 'a4', 'a4_lain'] : [null, null, null]);

            if ($peta === null) {
                continue;
            }

            $ketemu = false;
            foreach ($peta as $kode => $label) {
                if (preg_match(self::polaNama($label), $k['nama'])) {
                    $hasil[$kunci][$kode] = ($hasil[$kunci][$kode] ?? 0) + $k['qty'];
                    $ketemu = true;
                    break;
                }
            }

            if (! $ketemu) {
                $hasil[$lain] += $k['qty'];
            }
        }

        return $hasil;
    }

    /** Total A3 (stokis) dan A4 (pengelola) satu edisi. */
    public static function hitung(string $edisi): array
    {
        $hasil = ['stokis' => 0, 'pengelola' => 0];

        $baris = self::query($edisi)->get(['order_id', 'status', 'payment_method', 'item_qty', 'item_sku',
            'billing_first_name', 'billing_last_name', 'billing_company']);

        $kasdana = self::kasdanaMap($baris->pluck('order_id'));

        foreach ($baris as $o) {
            $k = self::klasifikasi($o, $kasdana);
            if ($k['dihitung'] && isset($hasil[$k['grup']])) {
                $hasil[$k['grup']] += $k['qty'];
            }
        }

        return $hasil;
    }
}