<?php

namespace App\Imports;

use App\Models\ManualOrder;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ManualOrderImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ManualOrder([
            'order_id'          => $row['order_id'] ?? null,
            'order_date'        => $row['order_date'] ?? now(),
            'customer_name'     => $row['customer_name'],
            'phone'             => $row['phone'] ?? null,
            'product_sku'       => $row['product_sku'] ?? null,
            'product_name'      => $row['product_name'],
            'qty'               => $row['qty'],
            'price'             => $row['price'],
            'total'             => $row['total'] ?? ($row['qty'] * $row['price']),
            'address'           => $row['address'] ?? null,
            'payment_method'    => $row['payment_method'] ?? 'cash',
            'status'            => $row['status'] ?? 'pending',
            'notes'             => $row['notes'] ?? null,
            // tambahkan field lain sesuai template Excel kamu
        ]);
    }
}