<?php

namespace App\Imports;

use App\Models\CasdanaTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CasdanaImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 8; 
    }

    public function model(array $row)
    {
        // Skip baris kosong atau baris Total Amount
        if (empty(array_filter($row))) {
            return null;
        }

        $invoice = $row[1] ?? '';
        $merchant = $row[2] ?? '';

        // Skip baris Total
        if (stripos($invoice, 'total') !== false || 
            stripos($merchant, 'total') !== false || 
            stripos($row[0] ?? '', 'total') !== false) {
            Log::info('Baris Total Amount dilewati');
            return null;
        }

        Log::info('Casdana Row Data:', $row);

        return new CasdanaTransaction([
            'invoice_number'   => $row[1] ?? null,                    // Kolom B
            'merchant'         => $row[2] ?? null,                    // Kolom C
            'customer'         => $row[3] ?? null,                    // Kolom D
            'status'           => strtoupper($row[4] ?? 'PENDING'),   // Kolom E
            'payment_date'     => $this->parseDate($row[5] ?? null),  // Kolom F
            'payment_channel'  => $row[6] ?? null,                    // Kolom G
            'payment_code'     => $row[7] ?? null,                    // Kolom H
            'amount'           => $this->cleanAmount($row[8] ?? 0),   // Kolom I
            'raw_data'         => json_encode($row),
        ]);
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function cleanAmount($value)
    {
        if (empty($value)) return 0;
        return (float) preg_replace('/[^0-9.-]+/', '', $value);
    }
}