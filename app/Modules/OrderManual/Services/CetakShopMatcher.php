<?php

namespace App\Modules\OrderManual\Services;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\OrderManual\Models\ManualCetakOrder;
use Illuminate\Support\Facades\Log;

/**
 * Pencocokan Create Manual Kalender / Majalah dengan biMBA Shop + Kasdana.
 *
 *  - Status awal  : on-hold (tidak dihitung di Report Angka Cetak).
 *  - Processing   : bila order sudah ada di biMBA Shop (no_cab + tahun/edisi sama)
 *                   DAN sudah lunas di Kasdana (SUCCESS / SETTLED / PAID).
 *                   ID order biMBA Shop disimpan di bimba_order_id.
 *  - Angkanya sendiri sudah dihitung lewat order biMBA Shop, jadi Create Manual TIDAK masuk SOUV.
 */
class CetakShopMatcher
{
    public const KASDANA_LUNAS = ['SUCCESS', 'SETTLED', 'PAID'];

    public const STATUS_SELESAI = ['processing', 'completed'];

    /** @return array{processing:int, on_hold:int} */
    public function sync(?string $jenis = null): array
    {
        $query = ManualCetakOrder::query()
            ->whereNull('synced_penjualan_at')
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::STATUS_SELESAI);
            });

        if ($jenis !== null) {
            $query->where('jenis', $jenis);
        }

        $baris = $query->get();
        if ($baris->isEmpty()) {
            return ['processing' => 0, 'on_hold' => 0];
        }

        // ---- biMBA Shop: index per jenis -> tahun/edisi -> no_cab -> [order_id, ...] ----
        $shop = ['kalender' => [], 'majalah' => []];
        $infoShop = [];   // order_id => status shop

        $rows = BimbashopOrder::query()
            ->where(function ($q) {
                $q->whereNull('status')->orWhere(function ($q2) {
                    $q2->where('status', 'not like', '%cancel%')
                       ->where('status', 'not like', '%fail%')
                       ->where('status', 'not like', '%refund%');
                });
            })
            ->orderBy('order_date')->orderBy('id')
            ->get(['order_id', 'status', 'billing_last_name', 'item_sku', 'item_name']);

        foreach ($rows as $r) {
            $oid = (string) $r->order_id;
            $infoShop[$oid] = preg_replace('/^wc-/', '', strtolower(trim((string) $r->status)));

            $key = ShopMatcher::noCabKey($r->billing_last_name);
            if ($key === '') {
                continue;
            }

            $sku  = strtoupper(trim((string) $r->item_sku));
            $nama = strtolower((string) $r->item_name);
            $kal  = str_contains($nama, 'kalender') || str_starts_with($sku, 'KAL') || str_starts_with($sku, 'KLD');

            if ($kal) {
                $tahun = ShopMatcher::tahunFrom($r->item_sku, $r->item_name);
                if ($tahun) {
                    $shop['kalender'][$tahun][$key][$oid] = true;
                }
            } else {
                $edisi = ShopMatcher::edisiFrom($r->item_sku, $r->item_name);
                if ($edisi) {
                    $shop['majalah'][strtoupper($edisi)][$key][$oid] = true;
                }
            }
        }

        // ---- kandidat order_id per baris manual ----
        $kandidat = [];
        $semuaId  = [];
        foreach ($baris as $b) {
            $ids = [];

            // 1) ID biMBA Shop yang diketik di form (bila ada di Shop)
            $ketik = trim((string) $b->order_id);
            if ($ketik !== '' && isset($infoShop[$ketik])) {
                $ids[] = $ketik;
            }

            // 2) no_cab + tahun (kalender) / edisi (majalah), sama seperti ShopMatcher
            $key = ShopMatcher::noCabKey($b->no_cab ?: $b->billing_last_name);
            if ($key !== '') {
                if ($b->jenis === 'kalender') {
                    $tahun = ShopMatcher::tahunFrom($b->product_sku, $b->product_name);
                    $ids = array_merge($ids, array_keys($shop['kalender'][$tahun][$key] ?? []));
                } else {
                    $edisi = ShopMatcher::edisiFrom($b->product_sku, $b->product_name);
                    $ids = array_merge($ids, array_keys($shop['majalah'][strtoupper((string) $edisi)][$key] ?? []));
                }
            }

            $ids = array_values(array_unique($ids));
            $kandidat[$b->id] = $ids;
            $semuaId = array_merge($semuaId, $ids);
        }

        // ---- Kasdana: order_id => status terbaru ----
        $kasdana = [];
        $semuaId = array_values(array_unique($semuaId));
        foreach (array_chunk($semuaId, 500) as $chunk) {
            $invoice = array_merge($chunk, array_map(fn ($i) => 'ID' . $i, $chunk));

            CasdanaTransaction::whereIn('invoice_number', $invoice)
                ->orderBy('id')
                ->get(['invoice_number', 'status', 'payment_date'])
                ->each(function ($c) use (&$kasdana) {
                    $oid = preg_replace('/^ID/i', '', trim((string) $c->invoice_number));
                    $kasdana[$oid] = [
                        'status' => strtoupper(trim((string) $c->status)),
                        'date'   => $c->payment_date,
                    ];
                });
        }

        // order_id Shop yang sudah dipakai pesanan manual lain (jangan dipakai dua kali)
        $terpakai = ManualCetakOrder::whereNotNull('bimba_order_id')
            ->get(['manual_id', 'bimba_order_id'])
            ->groupBy('bimba_order_id')
            ->map(fn ($g) => $g->pluck('manual_id')->unique()->all())
            ->all();

        $processing = 0;
        $onHold     = 0;

        foreach ($baris as $b) {
            $hit = null;
            foreach ($kandidat[$b->id] as $oid) {
                $lunas = in_array($kasdana[$oid]['status'] ?? '', self::KASDANA_LUNAS, true);
                $milikLain = isset($terpakai[$oid]) && ! in_array($b->manual_id, $terpakai[$oid], true);

                if ($lunas && ! $milikLain) {
                    $hit = $oid;
                    break;
                }
            }

            if ($hit === null) {
                $onHold++;
                if ($b->status !== 'on-hold') {
                    $b->update(['status' => 'on-hold']);
                }
                continue;
            }

            try {
                $b->update([
                    'status'           => 'processing',
                    'bimba_order_id'   => $hit,
                    'status_bimbashop' => $infoShop[$hit] ?? null,
                    'status_bayar'     => $kasdana[$hit]['status'] ?? null,
                    'payment_date'     => $kasdana[$hit]['date'] ?? null,
                    'shop_synced_at'   => now(),
                ]);
                $terpakai[$hit][] = $b->manual_id;
                $processing++;
            } catch (\Throwable $e) {
                Log::warning('CetakShopMatcher gagal update: ' . $e->getMessage(), ['id' => $b->id]);
            }
        }

        return ['processing' => $processing, 'on_hold' => $onHold];
    }
}
