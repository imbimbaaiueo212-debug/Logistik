<?php

namespace App\Modules\OrderManual\Http\Controllers\Modul;

use App\Http\Controllers\Controller;
use App\Modules\OrderManual\Models\ManualModulOrder;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManualModulSyncController extends Controller
{


    /**
     * Sync Manual Modul dari Bimba Shop + Casdana
     */
    public function syncManualModulFromBimbashopCasdana()
    {
        $updated          = 0;
        $skippedNoCasdana = 0;
        $skippedNoMatch   = 0;

        $manualOrders = ManualModulOrder::query()
            ->where(function ($q) {
                $q->whereNull('payment_date')
                  ->orWhereNull('estimasi_print_pl')
                  ->orWhereNull('estimasi_persiapan')
                  ->orWhereNull('order_date')
                  ->orWhereNull('status_bayar')
                  ->orWhereNull('status_bimbashop');
            })
            ->get();

        foreach ($manualOrders as $manual) {

            $orderId  = trim($manual->order_id ?? '');
            $noCab    = trim($manual->billing_last_name ?? '');
            $namaUnit = trim($manual->customer_name ?? '');

            if ($orderId === '' && $noCab === '') {
                $skippedNoMatch++;
                continue;
            }

            $bimbashopQuery = BimbashopOrder::query();

            if ($orderId !== '') {
                $bimbashopQuery->where('order_id', $orderId);
            } else {
                $bimbashopQuery->where(function ($q) use ($noCab) {
                    $q->where('billing_last_name', $noCab)
                      ->orWhereRaw("TRIM(LEADING '0' FROM billing_last_name) = ?", [ltrim($noCab, '0') ?: '0']);
                });

                if ($namaUnit !== '') {
                    $bimbashopQuery->where(function ($q) use ($namaUnit) {
                        $q->where('billing_company', 'like', '%' . $namaUnit . '%')
                          ->orWhere('billing_first_name', 'like', '%' . $namaUnit . '%');
                    });
                }
            }

            $bimbashopItem = $bimbashopQuery->first();

            if (!$bimbashopItem) {
                $skippedNoMatch++;
                continue;
            }

            $realOrderId = $bimbashopItem->order_id;
            $statusBimbashop = strtolower(trim($bimbashopItem->status ?? ''));

            $casdana = CasdanaTransaction::where(function ($q) use ($realOrderId) {
                    $q->where('invoice_number', $realOrderId)
                      ->orWhere('invoice_number', 'ID' . $realOrderId)
                      ->orWhere('invoice_number', 'like', '%' . $realOrderId . '%');
                })
                ->latest('id')
                ->first();

            $paymentDate = null;
            $isPaid      = false;
            $statusBayar = null;

            if ($casdana) {
                $statusBayar = strtoupper(trim($casdana->status ?? ''));
                if (in_array($statusBayar, ['SUCCESS', 'SETTLED', 'PAID'])) {
                    $paymentDate = $casdana->payment_date;
                    $isPaid = true;
                }
            } else {
                $skippedNoCasdana++;
            }

            $baseDate = $isPaid && $paymentDate
                ? Carbon::parse($paymentDate)
                : ($bimbashopItem->order_date
                    ? Carbon::parse($bimbashopItem->order_date)
                    : ($manual->order_date ? Carbon::parse($manual->order_date) : now()));

            $estimasiPrintPl = $baseDate->copy();
            if ($baseDate->hour >= 12) {
                $estimasiPrintPl->addDay();
            }
            while ($estimasiPrintPl->isSunday()) {
                $estimasiPrintPl->addDay();
            }
            $estimasiPersiapan = $estimasiPrintPl->copy()->addWeekdays(2);

            $dataUpdate = [];

            if ($manual->order_id != $realOrderId) {
                $dataUpdate['order_id'] = $realOrderId;
            }

            if (empty($manual->order_date) && $bimbashopItem->order_date) {
                $dataUpdate['order_date'] = $bimbashopItem->order_date;
            }

            if (empty($manual->payment_date) && $isPaid && $paymentDate) {
                $dataUpdate['payment_date'] = $paymentDate;
                $dataUpdate['status'] = 'completed';
            }

            if ($statusBayar && empty($manual->status_bayar)) {
                $dataUpdate['status_bayar'] = $statusBayar;
            }

            if ($statusBimbashop && empty($manual->status_bimbashop)) {
                $dataUpdate['status_bimbashop'] = $statusBimbashop;
            }

            if (empty($manual->estimasi_print_pl)) {
                $dataUpdate['estimasi_print_pl'] = $estimasiPrintPl;
            }
            if (empty($manual->estimasi_persiapan)) {
                $dataUpdate['estimasi_persiapan'] = $estimasiPersiapan;
            }

            $bimbashopQty = (int) ($bimbashopItem->item_qty ?? 0);
            if ($bimbashopQty > 0 && (empty($manual->qty) || (int) $manual->qty <= 1)) {
                $dataUpdate['qty'] = $bimbashopQty;
            }

            if (empty($manual->shipping_address_1) && !empty($bimbashopItem->shipping_address_1)) {
                $dataUpdate['shipping_address_1'] = $bimbashopItem->shipping_address_1;
            }
            if (empty($manual->shipping_city) && !empty($bimbashopItem->shipping_city)) {
                $dataUpdate['shipping_city'] = $bimbashopItem->shipping_city;
            }

            if (empty($manual->product_name) && !empty($bimbashopItem->item_name)) {
                $dataUpdate['product_name'] = $bimbashopItem->item_name;
            }
            if (empty($manual->product_sku) && !empty($bimbashopItem->item_sku)) {
                $dataUpdate['product_sku'] = $bimbashopItem->item_sku;
            }

            if (!empty($dataUpdate)) {
                $dataUpdate['catatan'] = trim(($manual->catatan ?? '') . "\n[SYNC BIMBA] " . now()->format('d/m/Y H:i'));
                $manual->update($dataUpdate);
                $updated++;
            }
        }

        return [
            'updated'            => $updated,
            'skipped_no_casdana' => $skippedNoCasdana,
            'skipped_no_match'   => $skippedNoMatch,
        ];
    }

    public function runSyncModul()
    {
        try {
            $result = $this->syncManualModulFromBimbashopCasdana();

            $msg = "✅ Sync selesai (hanya update data Manual yang sudah ada).<br>"
                 . "Berhasil di-update: <strong>{$result['updated']}</strong><br>"
                 . "Tidak ketemu Casdana: <strong>{$result['skipped_no_casdana']}</strong><br>"
                 . "Tidak ketemu di Bimba Shop: <strong>{$result['skipped_no_match']}</strong>";

            return redirect()
                ->route('order-manual-modul.manual')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Sync Modul gagal: ' . $e->getMessage());
            return redirect()
                ->route('order-manual-modul.manual')
                ->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }
}
