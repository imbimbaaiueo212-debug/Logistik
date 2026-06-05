<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithStartRow, WithCalculatedFormulas, WithChunkReading
{
    public function startRow(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        // ================= VALIDASI =================
        if (empty(array_filter($row))) {
            return null;
        }

        // Nama produk (kolom 5)
        if (empty($row[5])) {
            return null;
        }

        // ================= HELPER =================
        $toNumber = function ($value) {
            if ($value === null) return null;

            // Hapus Rp, spasi, dll
            $value = str_replace(['Rp', '.', ' '], '', $value);
            $value = str_replace(',', '.', $value);

            return is_numeric($value) ? (float) $value : null;
        };

        // ================= MAPPING =================
        $kode         = trim($row[1] ?? '');
        $kategoriNama = trim($row[2] ?? '');
        $jenis        = trim($row[3] ?? '');
        $label        = $row[4] ?? null;
        $name         = trim($row[5] ?? '');
        $satuan       = $row[6] ?? null;

        $beratSatuan  = $toNumber($row[7] ?? null);
        // $row[8] = berat paket → ❌ DIABAIKAN (pakai rumus model)

        $hargaBeli    = $toNumber($row[9] ?? null);
        // $row[10] = harga jual → ❌ DIABAIKAN (pakai rumus model)

        $status       = $row[12] ?? null;
        $isi          = is_numeric($row[13] ?? null) ? (int) $row[13] : 1;

        $role         = strtolower($row[14] ?? 'stock');
        $tanggalRilis = !empty($row[15]) ? date('Y-m-d', strtotime($row[15])) : null;

        // ================= NORMALISASI =================
        $role = in_array($role, ['jual', 'tidak_dijual', 'stock']) ? $role : 'stock';

        // ================= HANDLE KATEGORI =================
        $category = null;

        if (!empty($kategoriNama)) {
            $kategoriNama = ucwords(strtolower($kategoriNama));

            $category = Category::firstOrCreate([
                'nama' => $kategoriNama
            ]);
        }

        // ================= SIMPAN =================
        return Product::updateOrCreate(
            [
                'kode' => $kode ?: $name
            ],
            [
                'kode'          => $kode ?: null,
                'name'          => $name,
                'sku'           => $kode ?: null,
                'label'         => $label,
                'jenis'         => $jenis,
                'satuan'        => $satuan,
                'berat_satuan'  => $beratSatuan,
                'isi'           => $isi,
                'harga_beli'    => $hargaBeli,
                'status'        => $status,
                'role'          => $role,
                'tanggal_rilis' => $tanggalRilis,
                'kategori_id'   => $category?->id,
            ]
        );
    }
}