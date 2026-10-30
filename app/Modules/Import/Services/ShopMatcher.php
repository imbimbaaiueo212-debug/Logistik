<?php

namespace App\Modules\Import\Services;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\ManualOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Gerbang biMBA Shop untuk Majalah dan Kalender.
 *
 * Aturan: pesanan Majalah / Kalender hanya boleh diproses dan masuk ke Penjualan bila
 * sudah ada order di biMBA Shop dengan no_cab yang sama DAN edisi (Majalah) atau tahun (Kalender) yang sama.
 * Tidak memakai Casdana.
 *
 *  - Majalah : kunci = no_cab + edisi (contoh M159), dibaca dari item_sku / item_name di Shop.
 *  - Kalender: kunci = no_cab + tahun (contoh 2027), item Shop dikenali dari item_sku berawalan
 *              KALENDER_SKU_PREFIX atau item_name memuat kata "kalender".
 */
class ShopMatcher
{
    /** Awalan SKU item Kalender di biMBA Shop. Sesuaikan bila SKU aslinya berbeda. */
    public const KALENDER_SKU_PREFIX = ['KAL', 'KLD'];

    /** @var array<string, array<string, array>>|null  [jenis][edisi/tahun][noCabKey] = ['order_id','order_date'] */
    private ?array $shop = null;

    // ------------------------------------------------------------------ helper kunci

    public static function noCabKey($v): string
    {
        $v = strtoupper(trim((string) $v));

        if ($v === '') {
            return '';
        }
        $t = ltrim($v, '0');

        return $t === '' ? '0' : $t;
    }

    public static function edisiFrom(?string ...$texts): ?string
    {
        foreach ($texts as $t) {
            if ($t !== null && preg_match('/(?<![A-Za-z0-9])M\s*(\d{2,4})(?!\d)/i', $t, $m)) {
                return 'M' . $m[1];
            }
        }

        return null;
    }

    public static function tahunFrom(?string ...$texts): ?string
    {
        foreach ($texts as $t) {
            if ($t !== null && preg_match('/(?<!\d)(20\d{2})(?!\d)/', $t, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    private static function isKalenderItem(?string $sku, ?string $name): bool
    {
        $sku  = strtoupper(trim((string) $sku));
        $name = strtolower((string) $name);

        if (str_contains($name, 'kalender')) {
            return true;
        }
        foreach (self::KALENDER_SKU_PREFIX as $p) {
            if ($sku !== '' && str_starts_with($sku, $p)) {
                return true;
            }
        }

        return false;
    }

    /** Apakah baris Manual ini pesanan Majalah (wajib ada di biMBA Shop)? Modul / ATK / Sertifikat tidak. */
    public static function requiresShop(ManualOrder $o): bool
    {
        $sku  = (string) $o->product_sku;
        $name = (string) $o->product_name;

        return str_contains(strtolower($name), 'majalah')
            || self::edisiFrom($sku, $name) !== null;
    }

    public function isReady(ManualOrder $o): bool
    {
        return ! self::requiresShop($o) || (bool) $o->is_synced_bimba;
    }

    // ------------------------------------------------------------------ data Shop

    private function loadShop(): array
    {
        if ($this->shop !== null) {
            return $this->shop;
        }

        $this->shop = ['majalah' => [], 'kalender' => []];

        $rows = BimbashopOrder::query()
            ->whereNotNull('billing_last_name')
            ->where('billing_last_name', '!=', '')
            ->where(function ($q) {
                // abaikan order batal / gagal / refund
                $q->whereNull('status')->orWhere(function ($q2) {
                    $q2->where('status', 'not like', '%cancel%')
                       ->where('status', 'not like', '%fail%')
                       ->where('status', 'not like', '%refund%');
                });
            })
            ->orderBy('order_date')
            ->orderBy('id')
            ->get(['order_id', 'order_date', 'billing_last_name', 'item_sku', 'item_name']);

        foreach ($rows as $r) {
            $key = self::noCabKey($r->billing_last_name);
            if ($key === '') {
                continue;
            }

            if (self::isKalenderItem($r->item_sku, $r->item_name)) {
                $tahun = self::tahunFrom($r->item_sku, $r->item_name);
                if ($tahun) {
                    $this->shop['kalender'][$tahun][$key] ??= ['order_id' => $r->order_id, 'order_date' => $r->order_date];
                }
                continue;
            }

            $edisi = self::edisiFrom($r->item_sku, $r->item_name);
            if ($edisi) {
                $this->shop['majalah'][strtoupper($edisi)][$key] ??= ['order_id' => $r->order_id, 'order_date' => $r->order_date];
            }
        }

        return $this->shop;
    }

    /** [noCabKey => ['order_id', 'order_date']] untuk satu edisi Majalah. */
    public function setMajalah(?string $edisi): array
    {
        return $edisi ? ($this->loadShop()['majalah'][strtoupper($edisi)] ?? []) : [];
    }

    /** [noCabKey => ['order_id', 'order_date']] untuk satu tahun Kalender. */
    public function setKalender($tahun): array
    {
        return $tahun ? ($this->loadShop()['kalender'][(string) $tahun] ?? []) : [];
    }

    // ------------------------------------------------------------------ sinkron Manual

    /**
     * Cocokkan baris Manual (Majalah, belum diproses, belum cocok) dengan order biMBA Shop.
     * Yang cocok: is_synced_bimba = true, order_id diganti order_id Shop.
     *
     * @param  array<int>|null $ids  batasi ke id tertentu (null = semua)
     * @return array{cocok:int, menunggu:int}
     */
    public function syncManual(?array $ids = null): array
    {
        $query = ManualOrder::query()
            ->where('is_processed', 0)
            ->where(function ($q) {
                $q->where('is_synced_bimba', false)->orWhereNull('is_synced_bimba');
            });

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        $cocok = 0;
        $menunggu = 0;

        foreach ($query->get() as $o) {
            if (! self::requiresShop($o)) {
                continue;
            }

            $edisi = self::edisiFrom((string) $o->product_sku, (string) $o->product_name);
            $key   = self::noCabKey($o->billing_last_name ?: $o->no_cabang);
            $hit   = ($edisi && $key !== '') ? ($this->setMajalah($edisi)[$key] ?? null) : null;

            if (! $hit) {
                $menunggu++;
                continue;
            }

            $catatan = trim(($o->catatan ?? '') . "\n[SHOP] cocok dengan order biMBA Shop {$hit['order_id']} pada " . now()->format('d/m/Y H:i'));

            $data = [
                'bimba_order_id'   => $hit['order_id'],
                'bimba_order_date' => $hit['order_date'],
                'is_synced_bimba'  => true,
                'catatan'          => $catatan,
            ];

            try {
                $o->update($data + ['order_id' => $hit['order_id']]);
            } catch (QueryException $e) {
                // order_id Shop sudah dipakai baris lain: tetap tandai cocok, biarkan order_id lama
                Log::warning('ShopMatcher: order_id bentrok, dilewati', ['id' => $o->id, 'error' => $e->getMessage()]);
                $o->update($data);
            }

            $cocok++;
        }

        return ['cocok' => $cocok, 'menunggu' => $menunggu];
    }
}
