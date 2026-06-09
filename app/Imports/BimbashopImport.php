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

        foreach ($rows as $index => $row) {
            // Skip baris kosong
            if (empty($row['order_id']) && empty($row['id'])) {
                continue;
            }

            try {
                BimbashopOrder::updateOrCreate(
                    ['order_id' => trim($row['order_id'] ?? $row['id'])], // Gunakan order_id sebagai unique key
                    [
                        'order_date'          => !empty($row['order_date']) ? Carbon::parse($row['order_date']) : now(),
                        'status'              => $row['status'] ?? 'pending',
                        'order_total'         => $row['order_total'] ?? 0,
                        'ship_total'          => $row['ship_total'] ?? 0,
                        'discount_total'      => $row['discount_total'] ?? 0,
                        'refunded_total'      => $row['refunded_total'] ?? 0,
                        'payment_method'      => $row['payment_method'] ?? null,
                        'order_weight'        => $row['order_weight'] ?? null,
                        'item_sku'            => $row['item_sku'] ?? null,
                        'item_name'           => $row['item_name'] ?? null,
                        'item_price'          => $row['item_price'] ?? 0,
                        'item_qty'            => $row['item_qty'] ?? 1,
                        'billing_first_name'  => $row['billing_first_name'] ?? $row['bill_first_name'] ?? null,
                        'billing_last_name'   => $row['billing_last_name'] ?? $row['bill_last_name'] ?? null,
                        'billing_company'     => $row['billing_company'] ?? null,
                        'shipping_first_name' => $row['shipping_first_name'] ?? $row['ship_first_name'] ?? null,
                        'shipping_last_name'  => $row['shipping_last_name'] ?? $row['ship_last_name'] ?? null,
                        'shipping_address_1'  => $row['shipping_address_1'] ?? null,
                        'shipping_address_2'  => $row['shipping_address_2'] ?? null,
                        'shipping_city'       => $row['shipping_city'] ?? null,
                        'partial_pay_wallet_id' => $row['partial_pay_wallet_id'] ?? $row['partial_pay_through_wallet_id'] ?? null,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Import error pada baris " . ($index + 2) . ": " . $e->getMessage());
            }
        }

        if ($successCount === 0) {
            throw new \Exception("Tidak ada data yang berhasil diimport. Periksa kolom di file CSV/Excel Anda.");
        }
    }
}