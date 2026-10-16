<?php

namespace App\Modules\Import\Http\Controllers\Concerns;

use App\Modules\Import\Models\ManualOrder;
use App\Modules\Master\Models\UnitNamaMismatch;
use App\Modules\Import\Models\ManualRealisasi;
use App\Modules\Picking\Models\ManualPicking;
use App\Modules\Import\Models\DlcPeriode;
use App\Modules\Import\Models\DlcPesanan;
use App\Modules\Import\Models\PasifPeriode;
use App\Modules\Import\Models\BacaanPeriode;
use App\Modules\Import\Models\BacaanPesanan;
use App\Modules\Import\Models\SparePasif;
use App\Modules\Import\Models\PasifPesanan;
use App\Modules\Master\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Modules\Master\Models\UnitKemitraan;
use Illuminate\Support\Facades\DB;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use Illuminate\Support\Facades\Log;

trait ManualOrderSyncHelpers
{


/**
 * Sync Manual Order dari Bimbashop + Casdana
 * - Ganti order_id Manual yang auto menjadi order_id asli
 * - Isi payment_date jika Casdana SETTLED/SUCCESS
 * - Matching: billing_last_name (no_cab) + product_sku (edisi majalah)
 */
public function syncManualFromBimbashopCasdana()
{
    $updated = 0;
    $skippedNoCasdana = 0;
    $skippedNoMatch   = 0;

    // =====================================================
    // 1. Ambil semua order Bimbashop yang punya item Majalah
    // =====================================================
    $majalahOrders = BimbashopOrder::where(function ($q) {
            $q->where('item_sku', 'like', 'M%')
              ->orWhere('item_name', 'like', '%Majalah%')
              ->orWhere('item_name', 'like', '%Sahabat biMBA%');
        })
        ->get()
        ->groupBy('order_id');

    foreach ($majalahOrders as $orderId => $items) {

        // =================================================
        // 2. Cari Casdana (buang prefix "ID" jika ada)
        // =================================================
        $casdana = CasdanaTransaction::where(function ($q) use ($orderId) {
                $q->where('invoice_number', $orderId)
                  ->orWhere('invoice_number', 'ID' . $orderId)
                  ->orWhere('invoice_number', 'like', '%' . $orderId . '%');
            })
            ->latest('id')
            ->first();

        $paymentDate = null;
        $isPaid = false;

        if ($casdana) {
            $status = strtoupper(trim($casdana->status ?? ''));
            if (in_array($status, ['SUCCESS', 'SETTLED'])) {
                $paymentDate = $casdana->payment_date;
                $isPaid = true;
            }
        } else {
            $skippedNoCasdana++;
        }

        // =================================================
        // 3. Ambil daftar no_cab + edisi majalah dari order ini
        // =================================================
        $matches = [];

        foreach ($items as $item) {
            $noCab = trim($item->billing_last_name ?? '');
            if ($noCab === '') continue;

            // Normalisasi no_cab (buang leading zero untuk matching fleksibel)
            $noCabNormalized = ltrim($noCab, '0') ?: '0';

            // Ekstrak edisi dari SKU (M159-BDG1 → M159)
            $sku = strtoupper(trim($item->item_sku ?? ''));
            $edisi = null;

            if (preg_match('/\b(M\d{2,4})\b/i', $sku, $m)) {
                $edisi = strtoupper($m[1]);
            } elseif (preg_match('/\b(M\d{2,4})\b/i', $item->item_name ?? '', $m)) {
                $edisi = strtoupper($m[1]);
            }

            if (!$edisi) continue;

            $matches[] = [
                'no_cab'  => $noCab,
                'no_cab_n' => $noCabNormalized,
                'edisi'   => $edisi,
            ];
        }

        if (empty($matches)) {
            $skippedNoMatch++;
            continue;
        }

        // =================================================
        // 4. Cari ManualOrder yang cocok
        // =================================================
        foreach ($matches as $match) {

            $manualOrders = ManualOrder::where(function ($q) use ($match) {
                    $q->where('billing_last_name', $match['no_cab'])
                      ->orWhere('billing_last_name', $match['no_cab_n'])
                      ->orWhereRaw("TRIM(LEADING '0' FROM billing_last_name) = ?", [$match['no_cab_n']]);
                })
                ->where(function ($q) use ($match) {
                    $q->where('product_sku', $match['edisi'])
                      ->orWhere('product_sku', 'like', $match['edisi'] . '%')
                      ->orWhere('product_name', 'like', '%' . $match['edisi'] . '%');
                })
                // Hanya yang masih pakai order_id otomatis (6 digit) atau belum punya payment
                ->where(function ($q) {
                    $q->whereRaw("order_id REGEXP '^[0-9]{6}$'")
                      ->orWhereNull('payment_date');
                })
                ->get();

            foreach ($manualOrders as $manual) {

                // Skip jika sudah sama order_id-nya
                if ($manual->order_id == $orderId && $manual->payment_date) {
                    continue;
                }

                DB::transaction(function () use ($manual, $orderId, $paymentDate, $isPaid, &$updated) {

                    $oldOrderId = $manual->order_id;

                    // 1. Update ManualOrder
                    $manual->update([
                        'order_id'     => $orderId,
                        'payment_date' => $isPaid ? $paymentDate : $manual->payment_date,
                        'status'       => $isPaid ? 'completed' : $manual->status,
                        'catatan'      => ($manual->catatan ?? '') 
                            . "\n[SYNC] order_id diubah dari {$oldOrderId} → {$orderId} pada " 
                            . now()->format('d/m/Y H:i'),
                    ]);

                    // 2. Update ManualRealisasi
                    ManualRealisasi::where('manual_order_id', $manual->id)
                        ->update([
                            'no_pl'     => $orderId,
                            'tgl_bayar' => $isPaid ? $paymentDate : DB::raw('tgl_bayar'),
                        ]);

                    // 3. Update ManualPicking
                    ManualPicking::where('manual_order_id', $manual->id)
                        ->update([
                            'no_pl'        => $orderId,
                            'id_pesan'     => $orderId,
                            'payment_date' => $isPaid ? $paymentDate : DB::raw('payment_date'),
                        ]);

                    $updated++;
                });
            }
        }
    }

    return [
        'updated'            => $updated,
        'skipped_no_casdana' => $skippedNoCasdana,
        'skipped_no_match'   => $skippedNoMatch,
    ];
}


private function handleManualSyncResult($result, &$created, &$skipped, &$skippedList, &$errors, &$updated)
{
    if (is_string($result)) {
        if ($result === 'created') {
            $created++;
        } elseif ($result === 'updated') {
            $updated++;
        } elseif ($result === 'skipped') {
            $skipped++;
        } else {
            $errors[] = $result;
        }
        return;
    }

    if (is_array($result)) {
        $status = $result['status'] ?? null;

        if ($status === 'created') {
            $created++;
        } elseif ($status === 'updated') {
            $updated++;
        } elseif (in_array($status, ['skipped_qty0', 'skipped_existing'])) {
            $skipped++;
            $nama   = $result['nama'] ?? '-';
            $alasan = $result['alasan'] ?? 'Dilewati';
            $skippedList[] = $nama . ' → ' . $alasan;
        } else {
            $errors[] = ($result['nama'] ?? 'Unknown') . ': ' . ($result['message'] ?? json_encode($result));
        }
    }
}

/**
 * Buat 1 record Manual Order dari 1 unit
 *
 * @param  object       $unit
 * @param  string       $edisi
 * @param  string|null  $wilayah
 * @param  string|null  $contactPerson
 * @param  string       $group   A | B | F
 * @param  string|null  $noPs
 * @param  string       $tipe    majalah | bacaan | spare
 */
private function createManualOrderFromUnit(
    $unit,
    string $edisi,
    $wilayah = null,
    $contactPerson = null,
    string $group = 'B',
    $noPs = null,
    string $tipe = 'majalah'
) {
    if (($unit->jumlah_pesanan ?? 0) <= 0) {
        return [
            'status' => 'skipped_qty0',
            'nama'   => ($unit->nama_unit ?? 'Unit #' . ($unit->id ?? '?'))
                        . (!empty($unit->no_cabang) ? ' (' . trim($unit->no_cabang) . ')' : ''),
            'alasan' => 'Qty 0 / tidak pesan',
        ];
    }

    $noCab    = trim($unit->no_cabang ?? '');
    $qty      = (int) $unit->jumlah_pesanan;
    $namaUnit = trim($unit->nama_unit ?? '');
    $idPesan  = $unit->id_pesan ?? null;

    // Deteksi Pasif Manual
    $isPasifManual = !empty(trim((string) ($unit->custom_order_id ?? '')));

    // =====================================================
// CEK EXISTING (lebih ketat)
// =====================================================
if ($group === 'A') {
    $existingQuery = ManualOrder::where('product_sku', $edisi)
        ->where('grup', 'A');

    if ($tipe === 'bacaan') {
        $existingQuery->where('product_name', 'like', '%Bacaan%');
    } elseif ($tipe === 'spare') {
        $existingQuery->where(function ($q) {
            $q->where('product_name', 'like', '%Spare%')
              ->orWhere('customer_name', 'like', 'SPARE PASIF%');
        });
    } else {
        $existingQuery->where(function ($q) {
            $q->where('product_name', 'like', '%Majalah%')
              ->orWhere(function ($q2) {
                  $q2->where('product_name', 'not like', '%Bacaan%')
                     ->where('product_name', 'not like', '%Spare%');
              });
        });
    }

    $existing = null;

    if ($noCab !== '') {
        // Wajib cocok no_cab + nama
        $existing = (clone $existingQuery)
            ->where('billing_last_name', $noCab)
            ->where('customer_name', $namaUnit)
            ->first();
    } else {
        // Hanya jika tidak punya no_cab, baru pakai nama
        $existing = (clone $existingQuery)
            ->where('customer_name', $namaUnit)
            ->first();
    }

} elseif ($group === 'F') {

    $existingQuery = ManualOrder::where('product_sku', $edisi)
        ->where('grup', 'F');

    $existing = null;

    if ($isPasifManual) {
        $customOrderId = trim((string) ($unit->custom_order_id ?? ''));

        if ($customOrderId !== '') {
            $existing = ManualOrder::where('order_id', $customOrderId)->first();
        }

        if (!$existing && !empty($idPesan)) {
            $existing = (clone $existingQuery)
                ->where(function ($q) use ($idPesan) {
                    $q->where('notes', 'like', "%ID_PESAN:{$idPesan}%")
                      ->orWhere('catatan', 'like', "%ID_PESAN:{$idPesan}%");
                })
                ->first();

            if ($existing && $customOrderId !== '' && $existing->order_id !== $customOrderId) {
                if (!ManualOrder::where('order_id', $customOrderId)->exists()) {
                    $existing->update(['order_id' => $customOrderId]);
                }
            }
        }

    } else {
        // PASIF BIASA
        if ($noCab !== '') {
            $existing = (clone $existingQuery)
                ->where('billing_last_name', $noCab)
                ->where('customer_name', $namaUnit)
                ->first();
        } else {
            $existing = (clone $existingQuery)
                ->where('customer_name', $namaUnit)
                ->first();
        }
    }

} else {
    // ==================== GROUP B ====================
    $existing = null;

    if ($noCab !== '') {
        // WAJIB cocok no_cab + nama (tidak ada fallback nama saja)
        $existing = ManualOrder::where('product_sku', $edisi)
            ->where('billing_last_name', $noCab)
            ->where('customer_name', $namaUnit)
            ->where('grup', 'B')
            ->first();
    } else {
        // Hanya jika unit tidak punya no_cab
        $existing = ManualOrder::where('product_sku', $edisi)
            ->where('customer_name', $namaUnit)
            ->where('grup', 'B')
            ->first();
    }
}

    // =====================================================
    // JIKA SUDAH ADA → update / skip
    // =====================================================
    if ($existing) {
        if (
            in_array($group, ['A', 'F'])
            && $existing->status === 'pending'
            && !(bool) $existing->is_processed
            && (int) $existing->qty !== $qty
        ) {
            $existing->update([
                'qty'          => $qty,
                'order_weight' => (int) round(
                    ($existing->order_weight / max((int) $existing->qty, 1)) * $qty
                ),
            ]);
            return 'updated';
        }

        $needUpdate = empty($existing->no_ps)
            || ($noPs !== null && $existing->no_ps !== $noPs);

        if ($needUpdate && $noPs !== null) {
            try {
                $existing->update(['no_ps' => $noPs]);
                $this->syncNoPsToRelated($existing, $noPs);
                return 'updated';
            } catch (\Throwable $e) {
                Log::error("Gagal update no_ps ManualOrder #{$existing->id}: " . $e->getMessage());
                return $e->getMessage();
            }
        }

        // Kembalikan detail biar bisa dilihat
        return [
            'status'   => 'skipped_existing',
            'nama'     => $namaUnit . ($noCab ? " ($noCab)" : ''),
            'order_id' => $existing->order_id,
            'alasan'   => 'Sudah ada di Manual Order (Order ID: ' . $existing->order_id . ' | Qty: ' . $existing->qty . ')',
        ];
    }

    // =====================================================
    // NAMA: JANGAN paksa semua unit dengan no_cab sama jadi nama yang sama
    // =====================================================
    $namaDariUnit = trim($unit->nama_unit ?? '-');
    $namaUnit     = $namaDariUnit;          // ← prioritaskan nama asli dari sumber data
    $mitra        = $unit->mitra_pengelolaan ?? null;
    $isMismatch   = false;

    if ($noCab !== '') {
        // Cari mismatch yang cocok dengan nama unit ini (bukan sembarang latest)
        $mismatch = UnitNamaMismatch::where('no_cab', $noCab)
            ->where('is_resolved', false)
            ->where(function ($q) use ($namaDariUnit) {
                $q->where('nama_excel', $namaDariUnit)
                  ->orWhere('nama_master', $namaDariUnit);
            })
            ->latest('id')
            ->first();

        // Hanya override jika benar-benar ada mismatch yang cocok dengan nama ini
        if ($mismatch && !empty($mismatch->nama_excel) && strcasecmp($mismatch->nama_excel, $namaDariUnit) === 0) {
            // tetap pakai nama asli, hanya tandai mismatch
            $isMismatch = true;
        }

        $uk = UnitKemitraan::where('no_cab', $noCab)->first();

        if ($uk) {
            if (!empty($uk->bimba_aiueo_unit)) {
                $namaMaster = trim($uk->bimba_aiueo_unit);

                // Tandai mismatch jika nama sumber beda dengan master, tapi JANGAN ganti namanya
                if (strcasecmp($namaUnit, $namaMaster) !== 0) {
                    $isMismatch = true;

                    UnitNamaMismatch::updateOrCreate(
                        [
                            'no_cab'  => $noCab,
                            'periode' => now()->format('Y-m'),
                            'sumber'  => 'sync_manual_majalah',
                        ],
                        [
                            'nama_excel'  => $namaUnit,   // simpan nama asli unit
                            'nama_master' => $namaMaster,
                            'is_resolved' => false,
                        ]
                    );
                }
            }

            $mitra = $uk->mitra_pengelolaan ?? $mitra;
        }
    }

    // =====================================================
    // Generate / Ambil Order ID
    // =====================================================
    $customOrderId = trim((string) ($unit->custom_order_id ?? ''));

    if ($customOrderId !== '' && $isPasifManual) {
        $orderId = $customOrderId;

        if (ManualOrder::where('order_id', $orderId)->exists()) {
            return [
                'status'   => 'skipped_existing',
                'nama'     => $namaUnit . ($noCab ? " ($noCab)" : ''),
                'order_id' => $orderId,
                'alasan'   => 'Order ID custom sudah digunakan: ' . $orderId,
            ];
        }
    } else {
        $prefix = match ($group) {
            'A'     => 'A',
            'F'     => 'F',
            default => 'B',
        };

        $lastId = ManualOrder::where('order_id', 'like', $prefix . '%')
            ->whereRaw("order_id REGEXP '^{$prefix}[0-9]{4}$'")
            ->orderByRaw("CAST(SUBSTRING(order_id, 2) AS UNSIGNED) DESC")
            ->value('order_id');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 1) + 1;
        }

        $orderId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        while (ManualOrder::where('order_id', $orderId)->exists()) {
            $nextNumber++;
            $orderId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }

    // =====================================================
    // NOTES + NAMA PRODUK
    // =====================================================
    $parts = [];
    if ($isMismatch) {
        $parts[] = 'NAMA_MISMATCH';
    }
    if (!empty($contactPerson)) {
        $parts[] = 'CP: ' . $contactPerson;
    }

    if ($group === 'A' && $tipe === 'bacaan') {
        $parts[] = 'Sumber: Bacaan Unit';
    } elseif ($group === 'A' && $tipe === 'spare') {
        $parts[] = 'Sumber: Spare Pasif 3%';
    } elseif ($group === 'A') {
        $parts[] = 'Sumber: DLC';
    } elseif ($group === 'F') {
        if ($isPasifManual) {
            $parts[] = 'Sumber: Pasif Manual';
            if (!empty($idPesan)) {
                $parts[] = 'ID_PESAN:' . $idPesan;
            }
        } else {
            $parts[] = 'Sumber: Pasif';
        }
    }

    $notesText = implode(' | ', $parts);

    $productName = match (true) {
        $tipe === 'bacaan'     => 'Bacaan Unit ' . $edisi,
        $tipe === 'spare'      => 'Spare Pasif 3% ' . $edisi,
        $group === 'F'         => 'Unit Pasif majalah ' . $edisi,
        default                => 'Majalah Sahabat biMBA ' . $edisi,
    };

    // =====================================================
    // HITUNG BERAT
    // =====================================================
    $beratSatuanKg = 0.070;

    $product = Product::where('label', $edisi)
        ->whereNotNull('berat_satuan')
        ->where('berat_satuan', '>', 0)
        ->first();

    if ($product) {
        $beratSatuanKg = (float) $product->berat_satuan;
    }

    $orderWeightGram = (int) round($beratSatuanKg * 1000 * $qty);

    // =====================================================
    // Kurir & Status
    // =====================================================
    if (in_array($group, ['A', 'F'])) {
        $statusKirim = null;
        $ekspedisi   = null;
        $service     = null;
    } else {
        $statusKirim = 'Dikirim';
        $ekspedisi   = 'Lion Parcel';
        $service     = 'REGPACK';
    }

    try {
        ManualOrder::create([
            'order_id'            => $orderId,
            'order_date'          => now(),
            'customer_name'       => $namaUnit,          // ← sekarang pakai nama asli
            'phone'               => $unit->telepon ?? null,

            'product_sku'         => $edisi,
            'product_name'        => $productName,
            'qty'                 => $qty,
            'price'               => 0,
            'total'               => 0,

            'ship_total'          => 0,
            'order_weight'        => $orderWeightGram,
            'discount_total'      => 0,
            'refunded_total'      => 0,

            'payment_method'      => 'manual',
            'status'              => 'pending',
            'grup'                => $group,

            'billing_first_name'  => $mitra,
            'billing_last_name'   => $noCab ?: null,

            'shipping_first_name' => $namaUnit,
            'shipping_last_name'  => $noCab ?: null,
            'shipping_address_1'  => $unit->alamat_unit ?? $namaUnit,
            'shipping_address_2'  => null,
            'shipping_city'       => $wilayah,

            'status_kirim'        => $statusKirim,
            'ekspedisi'           => $ekspedisi,
            'service_pengiriman'  => $service,
            'is_processed'        => false,
            'payment_date'        => null,

            'no_ps'               => $noPs,

            'notes'               => $notesText,
            'catatan'             => $notesText,
        ]);

        return 'created';

    } catch (\Throwable $e) {
        Log::error("Gagal create ManualOrder dari unit " . ($unit->id ?? '?') . ": " . $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Update no_ps ke ManualOrder + Realisasi + Picking terkait
 */
private function syncNoPsToRelated(ManualOrder $order, ?string $noPs = null): void
{
    $noPs = $noPs !== null ? $noPs : $order->no_ps;

    // 1. Update ManualOrder sendiri (jika beda)
    if ($order->no_ps !== $noPs) {
        $order->update(['no_ps' => $noPs]);
    }

    // 2. Update semua Realisasi dari order ini
    ManualRealisasi::where('manual_order_id', $order->id)
        ->update(['no_ps' => $noPs]);

    // 3. Update semua Picking dari order ini
    ManualPicking::where('manual_order_id', $order->id)
        ->update(['no_ps' => $noPs]);
}

private function extractEdisiMajalah(?string $text): string
{
    if (empty($text)) {
        return 'Majalah';
    }

    if (preg_match('/\bM\s*(\d{2,4})\b/i', $text, $m)) {
        return 'M' . $m[1];
    }

    if (preg_match('/(?:edisi|bulan|juli|agustus|september|oktober|november|desember|januari|februari|maret|april|mei|juni)\s*[^\d]*(\d{3})\b/i', $text, $m)) {
        return 'M' . $m[1];
    }

    return 'Majalah';
}

/**
 * Logic hitung + simpan (dipakai di list & bisa dipanggil ulang)
 */
private function syncSparePasifData(): void
{
    $edisiList = collect()
        ->merge(DlcPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(PasifPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(BacaanPeriode::where('status', 'aktif')->pluck('edisi'))
        ->map(fn ($e) => strtoupper(trim($e)))
        ->filter()
        ->unique()
        ->sort()
        ->values();

    foreach ($edisiList as $edisi) {
        $dlcTotal = (int) DlcPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $pasifTotal = (int) PasifPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $bacaanTotal = (int) BacaanPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('bacaan_unit');

        $grandTotal = $dlcTotal + $pasifTotal + $bacaanTotal;
        $spareRaw   = $grandTotal * 0.03;
        $spare      = (int) round($spareRaw);
        $lembar     = $spare <= 0 ? 0 : (int) ceil($spare / 200);

        SparePasif::updateOrCreate(
            [
                'edisi'  => $edisi,
                'status' => 'aktif',
            ],
            [
                'judul'         => 'Spare Pasif 3% ' . $edisi,
                'dlc_total'     => $dlcTotal,
                'pasif_total'   => $pasifTotal,
                'bacaan_total'  => $bacaanTotal,
                'grand_total'   => $grandTotal,
                'spare_raw'     => round($spareRaw, 3),
                'spare'         => $spare,
                'lembar'        => $lembar,
                'grup'          => 'A',          // selalu grup A
                'created_by'    => Auth::id(),
            ]
        );
    }
}
}
