<?php

namespace App\Modules\Order\Http\Controllers\Concerns;

use App\Modules\Master\Models\Product;
use App\Modules\Master\Models\MatchingUserExport;
use App\Modules\Master\Models\UnitKemitraan;
use Carbon\Carbon;

trait OrderHelpers
{

/**
 * Hitung +3 Hari Kerja sambil mempertahankan jam & menit
 */
private function addBusinessDays(Carbon $startDate, int $days = 2)
{
    $date = $startDate->copy();

    $added = 0;

    while ($added < $days) {

        $date->addDay();

        if (
            $date->isSunday() ||
            $this->isHoliday($date)
        ) {
            continue;
        }

        $added++;
    }

    return $date;
}

/**
 * Daftar Hari Libur Nasional 2026
 */
private function isHoliday($date)
{
    $holidays = [
        '2026-01-01',
        '2026-01-16',
        '2026-02-17',
        '2026-03-19',
        '2026-03-21',
        '2026-03-22',
        '2026-04-03',
        '2026-04-05',
        '2026-05-01',
        '2026-05-14',
        '2026-05-27',
        '2026-06-01',
        '2026-06-16',
        '2026-08-17',
        '2026-12-25',
    ];

    return in_array($date->format('Y-m-d'), $holidays);
}

private function findProductBySku(string $sku, string $itemName = '')
{
    $searchCode = trim(explode('-', strtoupper($sku))[0] ?? '');

    if (empty($searchCode)) {
        return null;
    }

    // Prioritas utama: berdasarkan label
    $product = Product::where(function ($q) use ($searchCode) {
            $q->where('label', $searchCode)
              ->orWhere('label', 'like', $searchCode . '-%')
              ->orWhere('label', 'like', $searchCode . ' %');
        })->first();

    // Fallback ke nama produk hanya jika label tidak ditemukan
    if (!$product && !empty($itemName)) {
        $product = Product::where('name', $itemName)->first();
    }

    return $product;
}

/**
 * Cari Unit Kemitraan berdasarkan no_cab.
 * Jika satu no_cab punya beberapa baris (mis. "Kreo Larangan Joglo" dan
 * "English Kreo Larangan Joglo"), pilih yang namanya TANPA "English".
 */
private function unitKemitraanByNoCab($noCab)
{
    $noCab = trim((string) $noCab);

    if ($noCab === '') {
        return null;
    }

    $units = UnitKemitraan::where('no_cab', UnitKemitraan::padNoCab($noCab))->get();

    return $units->first(fn ($u) => stripos((string) $u->bimba_aiueo_unit, 'english') === false)
        ?? $units->first();
}

/**
 * Aturan masuk Penjualan TANPA Casdana untuk Payment Method wallet / other.
 *
 * Order biMBA Shop dengan payment_method "wallet" atau "other" tidak punya transaksi Casdana,
 * tapi tetap boleh masuk jika datanya lengkap:
 *  - status order biMBA Shop sudah dibayar (completed / processing)
 *  - No Cabang ada (billing_last_name)  [hanya jika $wajibNoCab]
 *  - Nama Unit ada (hasil resolveNamaUnit bukan kosong / "-")
 *  - total order lebih dari 0
 * Yang lolos diberi status pembayaran MANUAL dan mengikuti alur biasa (edit, proses, picking).
 *
 * @return array{metode: bool, lengkap: bool, kurang: string}
 */
private function cekTanpaCasdana($f, bool $wajibNoCab = true): array
{
    $metode = in_array(strtolower(trim((string) $f->payment_method)), ['wallet', 'other'], true);

    if (! $metode) {
        return ['metode' => false, 'lengkap' => false, 'kurang' => ''];
    }

    $kurang = [];

    $statusOrder = preg_replace('/^wc-/', '', strtolower(trim((string) $f->status)));

    if (! in_array($statusOrder, ['completed', 'processing'], true)) {
        $kurang[] = 'order belum dibayar (status ' . (trim((string) $f->status) ?: '-') . ')';
    }

    if ($wajibNoCab && trim((string) $f->billing_last_name) === '') {
        $kurang[] = 'No Cabang kosong';
    }

    $namaUnit = $this->resolveNamaUnit($f->billing_company, $f->billing_last_name, null);
    if (trim((string) $namaUnit) === '' || trim((string) $namaUnit) === '-') {
        $kurang[] = 'Nama Unit kosong';
    }

    if ((float) ($f->order_total ?? 0) <= 0) {
        $kurang[] = 'total order 0';
    }

    return [
        'metode'  => true,
        'lengkap' => $kurang === [],
        'kurang'  => implode(', ', $kurang),
    ];
}

/**
 * Status pembayaran untuk order tanpa Casdana berdasarkan Payment Method:
 * wallet -> DEPOSIT, other -> LICENSI, selain itu MANUAL.
 */
private function statusBayarTanpaCasdana($paymentMethod): string
{
    return match (strtolower(trim((string) $paymentMethod))) {
        'wallet' => 'DEPOSIT',
        'other'  => 'LICENSI',
        default  => 'MANUAL',
    };
}

private function resolveNamaUnit($billingCompany, $billingLastName, $defaultNamaUnit = null)
{
    $billingCompany  = trim($billingCompany ?? '');

    // Hilangkan spasi
    $billingLastName = trim($billingLastName ?? '');

    // Jika isinya angka, buang nol di depan
    if (is_numeric($billingLastName)) {
        $billingLastName = ltrim($billingLastName, '0');

        // Jika hasilnya kosong (misal 0000)
        if ($billingLastName === '') {
            $billingLastName = '0';
        }
    }

    // =====================================================
    // PRIORITAS 1 : UNIT KEMITRAAN
    // =====================================================
    if ($billingLastName !== '') {

        $unit = $this->unitKemitraanByNoCab($billingLastName);

        if ($unit) {
            return trim($unit->bimba_aiueo_unit);
        }
    }

    // =====================================================
    // PRIORITAS 2 : BILLING COMPANY
    // =====================================================
    if ($billingCompany !== '') {
        return $billingCompany;
    }

    // =====================================================
    // PRIORITAS 3 : DEFAULT
    // =====================================================
    return $defaultNamaUnit ?: '-';
}

private function extractVendorFromSku($skuOrPesanan)
{
    if (empty($skuOrPesanan)) {
        return 'Stokis Jakarta Aktif';
    }

    // Mapping lengkap dari data yang kamu berikan
    $vendorMap = [
        'JKT'    => 'Stokis Jakarta Aktif',
        'JKTP'   => 'Stokis Jakarta Pasif',
        'LG'     => 'Stokis Logistik',
        '-LG'    => 'Stokis Logistik',
        
        'UA1'    => 'PUA1',
        'UA2'    => 'PUA2',
        'UA3'    => 'PUA3',
        'DPK1'   => 'Stokis Depok 1',
        'SRG1'   => 'Stokis Serang 1',
        'KWG1'   => 'Stokis Karawang 1',
        'BKS1'   => 'Stokis Bekasi 1',
        'BGR1'   => 'Stokis Bogor 1',
        'TNG1'   => 'Stokis Tangerang 1',
        'SNG'    => 'Stokis Subang',
        'BGRT'   => 'Stokis Bogor 2',
        'PWK'    => 'Stokis Purwakarta 1',
        'TNG2'   => 'Stokis Tangerang 2',
        'KNG'    => 'Stokis Kuningan 1',
        'IDM'    => 'Stokis Indramayu 1',
        'SKB1'   => 'Stokis Sukabumi 1',
        'SKB2'   => 'Stokis Sukabumi 2',
        'BDG1'   => 'Stokis Bandung 1',
        'BDG2'   => 'Stokis Bandung 2',
        'CIL1'   => 'Stokis Cilincing 1',
        'SRG2'   => 'Stokis Serang 2',
        'DPR1'   => 'Stokis Denpasar',
        'KWG2'   => 'Stokis Karawang 2',
        'BGR3'   => 'Stokis Bogor 3',
        'DLC'    => 'Stokis Intervio',
        'EBT'    => 'Stokis English biMBA Talk',
        'SMG'    => 'Stokis Semarang',
        'SBY'    => 'Stokis Surabaya',
        'YYK'    => 'Stokis Yogyakarta',
        'INV'    => 'Stokis Inventori',
        'SGN'    => 'Stokis Sragen',
        'YK1'    => 'Stokis Yogyakarta 1',
        'ENB'    => 'Stokis English',
        'RB1'    => 'Stokis Cirebon 1',
        'TNG3'   => 'Stokis Tangerang 3',
    ];

    $skuUpper = strtoupper(trim($skuOrPesanan));

    // Cek satu per satu (prioritas urutan penting)
    foreach ($vendorMap as $code => $name) {
        if (stripos($skuUpper, $code) !== false) {
            return $name;
        }
    }

    // Jika tidak ada kode yang cocok → default JKT
    return 'Stokis Jakarta Aktif';
}
/**
 * Mendapatkan nama unit yang benar menggunakan MatchingUserExport
 */
private function getProperUnitName($billingLastName, $billingCompany = null, $fallback = null)
{
    $namaUnit = null;
    $billingLastName = trim($billingLastName ?? '');

    // Prioritas 1: MatchingUserExport
    if ($billingLastName !== '') {
        $match = MatchingUserExport::with('unitKemitraan')
            ->where('billing_last_name', $billingLastName)
            ->first();

        if ($match && $match->unitKemitraan) {
            $namaUnit = $match->unitKemitraan->bimba_aiueo_unit;
        }
    }

    // Prioritas 2: Fallback langsung ke UnitKemitraan (jika matching belum ada)
    if (!$namaUnit && $billingLastName !== '') {
        $unit = $this->unitKemitraanByNoCab($billingLastName);
        if ($unit) {
            $namaUnit = $unit->bimba_aiueo_unit;
        }
    }

    // Prioritas 3: Fallback umum
    if (!$namaUnit) {
        $namaUnit = $billingCompany 
            ?: trim(($billingLastName) . ' ' . ($billingCompany ?? ''))
            ?: $fallback;
    }

    return trim($namaUnit) ?: '-';
}
}
