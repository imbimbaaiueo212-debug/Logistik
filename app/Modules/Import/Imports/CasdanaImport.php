<?php

namespace App\Modules\Import\Imports;

use App\Modules\Import\Models\CasdanaTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CasdanaImport implements ToModel, WithStartRow
{
    public int $created = 0;
    public int $updated = 0;

    public function startRow(): int
    {
        return 8;
    }

    public function model(array $row)
    {
        // Skip baris kosong
        if (empty(array_filter($row))) {
            return null;
        }

        $invoice  = trim((string) ($row[1] ?? ''));
        $merchant = trim((string) ($row[2] ?? ''));

        // Skip baris Total
        if (stripos($invoice, 'total') !== false ||
            stripos($merchant, 'total') !== false ||
            stripos((string) ($row[0] ?? ''), 'total') !== false) {
            Log::info('Baris Total Amount dilewati');
            return null;
        }

        // Skip baris tanpa nomor invoice
        if ($invoice === '') {
            return null;
        }

        // Update kalau invoice sudah ada, buat baru kalau belum
        $trx = CasdanaTransaction::updateOrCreate(
            ['invoice_number' => $invoice],
            [
                'merchant'        => $merchant,
                'customer'        => $row[3] ?? null,
                'status'          => strtoupper($row[4] ?? 'PENDING'),
                'payment_date'    => $this->parseDate($row[5] ?? null),
                'payment_channel' => $row[6] ?? null,
                'payment_code'    => $row[7] ?? null,
                'amount'          => $this->cleanAmount($row[8] ?? 0),
                'raw_data'        => json_encode($row),
            ]
        );

        $trx->wasRecentlyCreated ? $this->created++ : $this->updated++;

        // Sudah disimpan lewat updateOrCreate, jadi tidak perlu return model
        return null;
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
