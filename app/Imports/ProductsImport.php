<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
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
    if (empty(array_filter($row)) || empty(trim($row[5] ?? ''))) {
        return null;
    }

    // ================= HELPER KG → GRAM DENGAN SAFETY KETAT =================
$toGram = function ($value) {
    if ($value === null || $value === '' || $value === false) {
        return null;
    }

    // Ambil hanya angka
    if (is_numeric($value)) {
        $kg = (float) $value;
    } else {
        $str = (string) $value;
        $str = preg_replace('/[^0-9.]/', '', $str);
        $kg = is_numeric($str) ? (float) $str : 0;
    }

    $gram = $kg * 1000;

    // BATAS KETAT: maksimal 500 kg
    if ($gram > 500000 || $gram < 0) {
        return null;   // skip jika terlalu besar
    }

    return $gram;
};

    // ================= HELPER HARGA =================
    $toNumber = function ($value) {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            return (float) $value;
        }

        $str = (string) $value;
        $str = str_replace(['Rp', ' ', 'Rp.', 'IDR', ','], '', $str);
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        $str = preg_replace('/[^0-9.]/', '', $str);

        return is_numeric($str) ? (float) $str : null;
    };

    // ================= MAPPING =================
    $kode         = trim($row[1] ?? '');
    $kategoriNama = trim($row[2] ?? '');
    $jenis        = trim($row[3] ?? '');
    $label        = $row[4] ?? null;
    $name         = trim($row[5] ?? '');
    $satuan       = $row[6] ?? null;

    $beratSatuan  = $toGram($row[7] ?? null);
    $beratPaket   = $toGram($row[8] ?? null);

    $hargaBeli    = $toNumber($row[9] ?? null);
    $hargaJual    = $toNumber($row[10] ?? null);

    $status       = $row[12] ?? null;
    $isi          = is_numeric($row[13] ?? null) ? (int)$row[13] : 1;

    $role         = strtolower($row[14] ?? 'stock');
    $tanggalRilis = !empty($row[15]) ? date('Y-m-d', strtotime($row[15])) : now()->format('Y-m-d');

    $role = in_array($role, ['jual', 'tidak_dijual', 'stock']) ? $role : 'stock';

    // ================= KATEGORI =================
    $category = null;
    if (!empty($kategoriNama)) {
        $kategoriNama = ucwords(strtolower($kategoriNama));
        $category = Category::firstOrCreate(['nama' => $kategoriNama]);
    }

    // ================= SIMPAN =================
    return Product::updateOrCreate(
        ['kode' => $kode ?: $name],
        [
            'kode'          => $kode ?: null,
            'name'          => $name,
            'sku'           => $kode ?: null,
            'label'         => $label,
            'jenis'         => $jenis,
            'satuan'        => $satuan,
            'berat_satuan'  => $beratSatuan,
            'berat_paket'   => $beratPaket,
            'isi'           => $isi,
            'harga_beli'    => $hargaBeli,
            'harga_jual'    => $hargaJual,
            'status'        => $status,
            'role'          => $role,
            'tanggal_rilis' => $tanggalRilis,
            'kategori_id'   => $category?->id,
        ]
    );
}
}