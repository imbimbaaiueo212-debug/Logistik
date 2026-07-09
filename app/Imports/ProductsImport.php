<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductsImport implements ToModel, WithStartRow, WithCalculatedFormulas, WithChunkReading
{
    private int $processed = 0;
    private int $imported = 0;
    private int $skipped = 0;

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        $this->processed++;
        $excelRow = $this->startRow() + $this->processed - 1;

        if (empty(array_filter($row))) {
            $this->skipped++;
            Log::warning("SKIP - Baris {$excelRow}: Baris kosong");
            return null;
        }

        $name = trim($row[5] ?? '');
        if (empty($name)) {
            $this->skipped++;
            Log::warning("SKIP - Baris {$excelRow}: Nama produk kosong");
            return null;
        }

        try {
            // ==================== HELPER BERAT ====================
            $toKg = function ($value) {

    if ($value === null || $value === '') {
        return null;
    }

    if (is_numeric($value)) {
        return round((float)$value, 4);
    }

    $value = strtolower(trim((string)$value));

    $value = str_replace(',', '.', $value);

    $value = str_replace([
        'kg',
        'kgs',
        'kilogram'
    ], '', $value);

    $value = preg_replace('/[^0-9.]/', '', $value);

    return is_numeric($value)
        ? round((float)$value, 4)
        : null;
};

            // ==================== HELPER HARGA ====================
            $toNumber = function ($value) {

    if ($value === null || $value === '') {
        return null;
    }

    if (is_numeric($value)) {
        return (float)$value;
    }

    $value = trim((string)$value);

    $value = str_replace([
        'Rp',
        'Rp.',
        'IDR',
        ' '
    ], '', $value);

    $value = str_replace('.', '', $value);

    $value = str_replace(',', '.', $value);

    $value = preg_replace('/[^0-9.]/', '', $value);

    return is_numeric($value)
        ? (float)$value
        : null;
};

            // ==================== HELPER TANGGAL (Diperbaiki) ====================
            $parseDate = function ($value) use ($excelRow) {

    if ($value === null || $value === '') {
        return null;
    }

    try {

        // Jika berupa serial date Excel
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        $value = trim((string) $value);

        // Format Indonesia dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value)
                ->format('Y-m-d');
        }

        // Format dd-mm-yyyy
        if (preg_match('/^\d{2}\-\d{2}\-\d{4}$/', $value)) {
            return Carbon::createFromFormat('d-m-Y', $value)
                ->format('Y-m-d');
        }

        // Format umum
        return Carbon::parse($value)->format('Y-m-d');

    } catch (\Throwable $e) {

        Log::warning("Tanggal gagal diparse", [
            'baris' => $excelRow,
            'raw'   => $value,
            'error' => $e->getMessage(),
        ]);

        return null;
    }
};

            // ==================== MAPPING ====================
            $jenis         = trim($row[1] ?? '');
            $kategoriNama  = trim($row[2] ?? '');
            $subKategori   = trim($row[3] ?? '');
            $label         = trim($row[4] ?? '');
            $satuan        = trim($row[6] ?? '');

            $beratSatuan   = $toKg($row[7] ?? null);
            $beratPaket    = $toKg($row[8] ?? null);

            $hargaBeli     = $toNumber($row[9] ?? null);
            $hargaJual     = $toNumber($row[10] ?? null);
            $hargaPenyesuaian = $toNumber($row[11] ?? null);

            $status        = trim($row[12] ?? '');

            $isi = is_numeric($row[13] ?? null) ? (int)$row[13] : 1;

            $role = strtolower(trim($row[14] ?? 'stock'));
            if (!in_array($role, ['jual', 'tidak_dijual', 'stock'])) {
                $role = 'stock';
            }

            // Tanggal Rilis (Pakai helper baru)
            $tanggalRilis = $parseDate($row[15] ?? null);

            // Category
            $category = null;
            if (!empty($kategoriNama)) {
                $category = Category::firstOrCreate([
                    'nama' => ucwords(strtolower($kategoriNama))
                ]);
            }

            // ==================== SIMPAN ====================
            $product = new Product([
                'name'                   => $name,
                'label'                  => $label,
                'jenis'                  => $jenis,
                'kategori_id'            => $category?->id,
                'kategori'               => $category?->nama,
                'sub_kategori'           => $subKategori,
                'satuan'                 => $satuan,
                'berat_satuan'           => $beratSatuan,
                'berat_paket'            => $beratPaket,
                'isi'                    => $isi,
                'harga_beli'             => $hargaBeli,
                'harga_jual'             => $hargaJual,
                'harga_jual_penyesuaian' => $hargaPenyesuaian,
                'status'                 => $status,
                'role'                   => $role,
                'tanggal_rilis'          => $tanggalRilis,
            ]);

            $this->imported++;

            Log::info("✅ BERHASIL Baris {$excelRow} | {$name}", [
                'tanggal_rilis' => $tanggalRilis,
                'berat_satuan'  => $beratSatuan,
            ]);

            return $product;

        } catch (\Exception $e) {
            $this->skipped++;
            Log::error("❌ ERROR Baris {$excelRow} - {$name}", [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function __destruct()
    {
        Log::info("=== IMPORT SELESAI ===", [
            'processed' => $this->processed,
            'imported'  => $this->imported,
            'skipped'   => $this->skipped,
        ]);
    }
}