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
 *                   DAN sudah lunas di Kasdana (SUCCESS / SETTLED / PAID),
 *                   atau dibayar COD / wallet / other (tanpa Casdana).
 *                   ID order biMBA Shop disimpan di bimba_order_id.
 *  - Angkanya sendiri sudah dihitung lewat order biMBA Shop, jadi Create Manual TIDAK masuk SOUV.
 */
class CetakShopMatcher
{
    public const KASDANA_LUNAS = ['SUCCESS', 'SETTLED', 'PAID'];

    public const STATUS_SELESAI = ['processing', 'completed'];

    /** Payment method yang dibayar di luar Casdana (COD, wallet, other): dianggap sudah dibayar. */
    public static function bayarTanpaCasdana(?string $metode): bool
    {
        $m = strtolower(trim((string) $metode));

        return $m !== '' && (
            in_array($m, ['cod', 'wallet', 'other'], true)
            || str_contains($m, 'cash on delivery')
            || str_contains($m, 'wallet')
        );
    }

    /** Kunci nama (huruf besar, spasi dirapikan) untuk mencocokkan unit Pasif tanpa no_cab, mis. "JAKARTA PASIF". */
    public static function namaKey($v): string
    {
        return preg_replace('/\s+/', ' ', strtoupper(trim((string) $v)));
    }

    /** @return array{processing:int, on_hold:int} */
    public function sync(?string $jenis = null): array
    {
        $query = ManualCetakOrder::query()
            ->whereNull('synced_penjualan_at')
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::STATUS_SELESAI)
                  // Unit Pasif dinilai ulang selama belum diproses, supaya cocok yang salah bisa dikoreksi
                  ->orWhere(function ($p) {
                      $p->where('status_unit', 'pasif')
                        ->where(fn ($x) => $x->whereNull('is_processed')->orWhere('is_processed', false));
                  });
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
        $shopNama = [];   // tahun => namaKey => [order_id => true]  (untuk unit Pasif)
        $qtyShop  = [];   // order_id => ['total' => qty kalender, 'lines' => [qty per item]]
        $tglShop  = [];   // order_id => tanggal order Shop (untuk memilih yang terdekat)
        $tanpaCasdana = []; // order_id => true bila COD / wallet / other

        $rows = BimbashopOrder::query()
            ->where(function ($q) {
                $q->whereNull('status')->orWhere(function ($q2) {
                    $q2->where('status', 'not like', '%cancel%')
                       ->where('status', 'not like', '%fail%')
                       ->where('status', 'not like', '%refund%');
                });
            })
            ->orderBy('order_date')->orderBy('id')
            ->get(['order_id', 'status', 'payment_method', 'billing_first_name', 'billing_last_name', 'item_sku', 'item_name', 'item_qty', 'order_date']);

        foreach ($rows as $r) {
            $oid = (string) $r->order_id;
            $infoShop[$oid] = preg_replace('/^wc-/', '', strtolower(trim((string) $r->status)));
            if (self::bayarTanpaCasdana($r->payment_method)) {
                $tanpaCasdana[$oid] = true;
            }

            if ($r->order_date && ! isset($tglShop[$oid])) {
                $tglShop[$oid] = $r->order_date->getTimestamp();
            }

            $sku  = strtoupper(trim((string) $r->item_sku));
            $nama = strtolower((string) $r->item_name);
            $kal  = str_contains($nama, 'kalender') || str_starts_with($sku, 'KAL') || str_starts_with($sku, 'KLD');

            // Unit Pasif (mis. JAKARTA PASIF) dicocokkan lewat nama + qty, karena tidak punya no_cab
            if ($kal) {
                $tahunPasif = ShopMatcher::tahunFrom($r->item_sku, $r->item_name);
                if ($tahunPasif) {
                    $qtyShop[$oid]['total']   = ($qtyShop[$oid]['total'] ?? 0) + (int) $r->item_qty;
                    $qtyShop[$oid]['lines'][] = (int) $r->item_qty;
                    foreach ([self::namaKey($r->billing_last_name), self::namaKey(trim($r->billing_first_name . ' ' . $r->billing_last_name))] as $nk) {
                        if ($nk !== '') {
                            $shopNama[$tahunPasif][$nk][$oid] = true;
                        }
                    }
                }
            }

            $key = ShopMatcher::noCabKey($r->billing_last_name);
            if ($key === '') {
                continue;
            }

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

            // 3) Unit PASIF kalender: nama unit (mis. JAKARTA PASIF) + QTY harus sama persis,
            //    lalu pilih order Shop dengan tanggal paling dekat dengan tanggal pesanan manual.
            //    Berlaku juga untuk kandidat dari no_cab, karena satu unit bisa punya beberapa order Shop.
            if ($b->status_unit === 'pasif' && $b->jenis === 'kalender') {
                $tahun = ShopMatcher::tahunFrom($b->product_sku, $b->product_name);
                $q     = (int) $b->qty;
                $ketikId = ($ketik !== '' && isset($infoShop[$ketik])) ? [$ketik] : [];

                $cand = array_values(array_diff($ids, $ketikId));
                foreach ([$b->customer_name, $b->billing_last_name] as $n) {
                    $nk = self::namaKey($n);
                    if ($nk !== '' && $tahun) {
                        $cand = array_merge($cand, array_map('strval', array_keys($shopNama[$tahun][$nk] ?? [])));
                    }
                }

                $cand = array_values(array_filter(array_unique($cand), function ($oid) use ($qtyShop, $q) {
                    return ($qtyShop[$oid]['total'] ?? null) === $q
                        || in_array($q, $qtyShop[$oid]['lines'] ?? [], true);
                }));

                $tglManual = $b->order_date ? $b->order_date->getTimestamp() : null;
                if ($tglManual !== null) {
                    usort($cand, fn ($a, $c) => abs(($tglShop[$a] ?? PHP_INT_MAX / 2) - $tglManual) <=> abs(($tglShop[$c] ?? PHP_INT_MAX / 2) - $tglManual));
                }

                $ids = array_merge($ketikId, $cand);
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
            $pasif = $b->status_unit === 'pasif';
            foreach ($kandidat[$b->id] as $oid) {
                // Unit Pasif tidak lewat Kasdana: cukup ada di biMBA Shop dan sudah processing / completed
                $lunas = in_array($kasdana[$oid]['status'] ?? '', self::KASDANA_LUNAS, true)
                    || isset($tanpaCasdana[$oid])
                    || ($pasif && in_array($infoShop[$oid] ?? '', self::STATUS_SELESAI, true));
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
