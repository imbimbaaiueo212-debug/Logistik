<?php

namespace App\Imports;

use App\Models\BimbashopOrder;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BimbashopImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena heading + index mulai 0

            // Skip baris kosong
            if (empty($row['order_id']) && empty($row['id'])) {
                $skippedCount++;
                continue;
            }

            try {
                $orderId = trim($row['order_id'] ?? $row['id'] ?? '');

                if (empty($orderId)) {
                    Log::warning("Baris {$rowNumber}: order_id kosong");
                    $skippedCount++;
                    continue;
                }

                // Di dalam try block, ganti create() menjadi:
BimbashopOrder::create([
    'order_id'            => $orderId,

    'order_date'          => !empty($row['order_date']) 
                             ? Carbon::parse($row['order_date'])->format('Y-m-d H:i:s') 
                             : now(),

    'status'              => $row['status'] ?? 'pending',
    'order_total'         => (float)($row['order_total'] ?? 0),
    'ship_total'          => (float)($row['ship_total'] ?? 0),
    'discount_total'      => (float)($row['discount_total'] ?? 0),
    'refunded_total'      => (float)($row['refunded_total'] ?? 0),
    'payment_method'      => $row['payment_method'] ?? null,
    'order_weight'        => $row['order_weight'] ?? null,

    'item_sku'            => trim($row['item_sku'] ?? ''),
    'item_name'           => trim($row['item_name'] ?? ''),
    'item_price'          => (float)($row['item_price'] ?? 0),
    'item_qty'            => (int)($row['item_qty'] ?? 1),

    'billing_first_name'  => $row['billing_first_name'] ?? $row['bill_first_name'] ?? null,
    'billing_last_name'   => $row['billing_last_name'] ?? $row['bill_last_name'] ?? null,
    'billing_company'     => $row['billing_company'] ?? null,

    'shipping_first_name' => $row['shipping_first_name'] ?? $row['ship_first_name'] ?? null,
    'shipping_last_name'  => $row['shipping_last_name'] ?? $row['ship_last_name'] ?? null,
    'shipping_address_1'  => $row['shipping_address_1'] ?? null,
    'shipping_address_2'  => $row['shipping_address_2'] ?? null,
    'shipping_city'       => $row['shipping_city'] ?? null,
    'shipping_phone'      => $row['shipping_phone'] ?? null,

    'partial_pay_wallet_id' => $row['partial_pay_wallet_id'] ?? $row['partial_pay_through_wallet_id'] ?? null,
]);

                $successCount++;

            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Import Error baris {$rowNumber} (Order ID: {$orderId}): " . $e->getMessage());
            }
        }

        $message = "✅ Berhasil import {$successCount} baris data.";
        if ($errorCount > 0) {
            $message .= " ❌ {$errorCount} baris gagal.";
        }
        if ($skippedCount > 0) {
            $message .= " ⏭️ {$skippedCount} baris dilewati.";
        }

        // Log summary
        Log::info("Import Bimbashop selesai: {$successCount} sukses, {$errorCount} error, {$skippedCount} skipped.");

        if ($successCount === 0) {
            throw new \Exception("Tidak ada data yang berhasil diimport. Periksa format file Excel Anda.");
        }

        return $message; // optional
    }
}