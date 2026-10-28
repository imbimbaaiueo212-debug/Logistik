<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\LogistikPasif;
use App\Modules\Order\Models\LogistikPasifItem;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\Master\Models\Product;
use App\Modules\Order\Models\RealisasiLogistikPasif;
use App\Modules\Master\Models\StokisMitra;
use App\Modules\Master\Models\UserExportBimbaShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Modules\Order\Http\Controllers\Concerns\OrderHelpers;
use App\Modules\Order\Http\Controllers\Concerns\LogistikPasifRules;

/**
 * Logistik Pasif - order biMBAShop dengan SKU LG (-LG).
 * Alur sama dengan Jakarta Aktif: Sync Bimbashop + Casdana -> daftar -> edit -> proses.
 */
class LogistikPasifController extends Controller
{
    use OrderHelpers;
    use LogistikPasifRules;

public function logistikPasif(Request $request)
{
    $query = LogistikPasif::query();

    // === FILTER ===
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }
    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }
    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }
    if ($request->filled('status_pesan')) {
        $query->where('status_pesan', $request->status_pesan);
    }
    if ($request->filled('validasi')) {
        $query->where('validasi', $request->validasi);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }
    if ($request->filled('pesanan')) {
        $query->where('pesanan', 'like', '%' . $request->pesanan . '%');
    }
    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    $perPage = $request->get('per_page', 50);
    $perPage = in_array($perPage, [5, 10, 20, 50, 100, 200, 500]) ? $perPage : 50;

    $data = $query
        ->with(['casdana' => function ($q) {
            $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer');
        }])
        ->selectRaw('*, payment_date')
        ->orderBy('tgl_pesan', 'asc')
        ->paginate($perPage)
        ->appends($request->query());

    $listStatusBayar = LogistikPasif::select('status_pembayaran')
        ->whereNotNull('status_pembayaran')
        ->where('status_pembayaran', '!=', '')
        ->distinct()
        ->orderBy('status_pembayaran')
        ->pluck('status_pembayaran');

    $listPesanan = LogistikPasif::select('pesanan')
        ->whereNotNull('pesanan')
        ->where('pesanan', '!=', '')
        ->distinct()
        ->orderBy('pesanan')
        ->pluck('pesanan');

    $listNamaUnit = LogistikPasif::select('nama_unit')
        ->whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $listGrup = LogistikPasif::select('grup')
        ->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    $unitTidakPesan = [];

    // Daftar no_cab yang masih mismatch (untuk badge di index)
    $mismatchNoCab = \App\Modules\Master\Models\UnitNamaMismatch::where('is_resolved', false)
        ->pluck('no_cab')
        ->map(fn ($v) => trim((string) $v))
        ->filter()
        ->unique()
        ->flip()
        ->all();

    return view('order::order.logistik-pasif-index', compact(
        'data',
        'unitTidakPesan',
        'listStatusBayar',
        'listPesanan',
        'listNamaUnit',
        'listGrup',
        'mismatchNoCab'
    ));
}


/**
 * Sync dari Bimbashop + Casdana 
 * Hanya SKU Logistik (-LG) milik Stokis PASIF
 */
public function syncLgFromBimbashop()
{
    $totalOrder  = 0;
    $skipExists  = 0;
    $skipCasdana = 0;
    $skipStatus  = 0;
    $inserted    = 0;
    $updatedUnit = 0;
    $skipAktif   = 0;
    $diganti     = 0;
    $dipindahAktif = 0;
    $belumCocok  = [];

    // =====================================================
    // AMBIL ORDER LOGISTIK  (SKU berakhiran / mengandung "-LG")
    // =====================================================
    $bimbashopOrders = BimbashopOrder::where('item_sku', 'like', '%-LG%')
        ->get()
        ->groupBy('order_id');

    $totalOrder = $bimbashopOrders->count();

    // =====================================================
    // LOOP ORDER
    // =====================================================
    foreach ($bimbashopOrders as $orderId => $items) {

        // =================================================
        // CEK ORDER SUDAH ADA
        // =================================================
        if (
            LogistikPasif::where(
                'id_pesan',
                $orderId
            )->exists()
        ) {
            // Order sudah ada: perbarui Nama Unit & Cabang dari Database Stokis Mitra / User Export
            // (hanya untuk data yang belum diproses)
            $existing = LogistikPasif::where('id_pesan', $orderId)->first();
            $src      = $items->first();

            if ($existing && ! $existing->is_processed) {
                $resolved = $this->resolveUnitLogistik(
                    $src->billing_last_name,
                    $src->billing_company,
                    $existing->nama_unit,
                    $src->billing_first_name
                );

                // Aturan pasif (bacs / Jakarta Pasif / Inventaris / unit di Unit Kemitraan)
                $rule = $this->cekLogistikPasif($src);

                // Sudah bukan pasif menurut aturan sekarang (stokis aktif) -> hapus dari Pasif
                // supaya bisa masuk Logistik Aktif lewat Sync LG Aktif. Hanya data yang belum diproses.
                if (! $rule['pasif'] && ! $resolved['pasif']) {
                    $existing->items()->delete();
                    $existing->delete();
                    $dipindahAktif++;
                    $skipExists++;
                    continue;
                }

                if ($rule['override']) {
                    $resolved['nama_unit'] = $rule['override']['nama_unit'];
                    $resolved['no_cab']    = $rule['override']['no_cab'];
                }

                if (! $resolved['matched'] && ! $rule['override'] && count($belumCocok) < 8) {
                    $belumCocok[] = "{$orderId} [first=" . trim((string) $src->billing_first_name)
                        . ' | company=' . trim((string) $src->billing_company)
                        . ' | last=' . trim((string) $src->billing_last_name) . ']';
                }

                if (
                    $existing->nama_unit !== $resolved['nama_unit']
                    || $existing->cabang !== $resolved['no_cab']
                ) {
                    $existing->nama_unit = $resolved['nama_unit'];
                    $existing->cabang    = $resolved['no_cab'];
                    $existing->save();
                    $updatedUnit++;
                }
            }

            $skipExists++;
            continue;
        }

        $firstItem = $items->first();

        // =================================================
        // QUERY CASDANA
        // =================================================
        $casdana = CasdanaTransaction::where(
            function ($q) use ($orderId) {

                $q->where(
                    'invoice_number',
                    'like',
                    "%{$orderId}%"
                )
                ->orWhere(
                    'invoice_number',
                    $orderId
                );

            }
        )
        ->latest('id')
        ->first();

        // Order pasif yang dibayar di luar Casdana (payment bacs / Stokis Jakarta Pasif / Inventaris)
        // memang tidak punya transaksi Casdana. Tetap diproses jika order sudah dibayar
        // (status biMBA Shop completed/processing), dengan status pembayaran MANUAL.
        $tanpaCasdana = false;

        if (! $casdana) {
            $f    = $items->first();
            $teks = strtolower(trim(implode(' ', [
                (string) $f->billing_first_name,
                (string) $f->billing_last_name,
                (string) $f->billing_company,
            ])));

            $bayarLuarCasdana = strtolower(trim((string) $f->payment_method)) === 'bacs'
                || str_contains($teks, 'jakarta pasif')
                || str_contains($teks, 'inventaris');

            $sudahBayar = in_array(strtolower(trim((string) $f->status)), ['completed', 'processing'], true);

            if (! $bayarLuarCasdana || ! $sudahBayar) {
                $skipCasdana++;
                continue;
            }

            $tanpaCasdana = true;
        }

        // =================================================
        // STATUS PEMBAYARAN
        // =================================================
        $statusCasdana = $tanpaCasdana
            ? 'MANUAL'
            : strtoupper(
                trim(
                    $casdana?->status ?? ''
                )
            );

        if (
            ! $tanpaCasdana
            &&
            !in_array(
                $statusCasdana,
                [
                    'SUCCESS',
                    'SETTLED'
                ]
            )
        ) {
            $skipStatus++;
            continue;
        }

        // =====================================================
        // ESTIMASI WAKTU
        // =====================================================
        $paymentDate       = $casdana?->payment_date;
        $estimasiPrintPl   = null;
        $estimasiPersiapan = null;

        if ($paymentDate) {

            $payment = Carbon::parse(
                $paymentDate
            );

            $estimasiPrintPl = $payment->hour < 12
                ? $payment->copy()
                : $payment->copy()->addDay();

            while (
                $estimasiPrintPl->isSunday()
                ||
                $this->isHoliday(
                    $estimasiPrintPl
                )
            ) {
                $estimasiPrintPl->addDay();
            }

            $estimasiPersiapan =
                $this->addBusinessDays(
                    $estimasiPrintPl,
                    2
                );
        }

        // =====================================================
        // PRODUCT CACHE
        // =====================================================
        $productCache = [];

        // =====================================================
        // KATEGORI UTAMA ORDER
        // =====================================================
        $kategoriList = [];

        foreach ($items as $item) {

            $sku = strtoupper(
                trim(
                    $item->item_sku ?? ''
                )
            );

            if (empty($sku)) {
                continue;
            }

            // =================================================
            // KODE SKU
            // =================================================
            $searchCode = trim(
                explode(
                    '-',
                    $sku
                )[0]
            );

            // =================================================
            // CARI PRODUCT
            // =================================================
            if (
                !array_key_exists(
                    $searchCode,
                    $productCache
                )
            ) {

                $productCache[$searchCode] =
                    $this->findProductBySku(
                        $sku,
                        $item->item_name ?? ''
                    );
            }

            $product =
                $productCache[$searchCode];

            // =================================================
            // AMBIL KATEGORI UTAMA
            //
            // PENTING:
            //
            // products.kategori
            //      = KATEGORI UTAMA
            //
            // products.sub_kategori
            //      = SUB KATEGORI
            //
            // KITA HANYA PAKAI:
            //      $product->kategori
            //
            // JANGAN PAKAI sub_kategori
            // =================================================
            if ($product) {

                $kategori = trim(
                    (string) (
                        $product->kategori ?? ''
                    )
                );

            } else {

                // =================================================
                // FALLBACK JIKA PRODUCT TIDAK DITEMUKAN
                // =================================================
                $itemName = trim(
                    $item->item_name ?? ''
                );

                $kategori = str_ireplace(
                    [
                        'LG',
                        'biMBA',
                        'Unit',
                        'Reguler'
                    ],
                    '',
                    $itemName
                );

                $kategori = preg_replace(
                    '/\s+/',
                    ' ',
                    $kategori
                );

                $kategori = trim(
                    $kategori
                );

                if (empty($kategori)) {

                    $kategori = trim(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            str_ireplace(
                                [
                                    'LG',
                                    '-LG'
                                ],
                                '',
                                $sku
                            )
                        )
                    );
                }
            }

            // =================================================
            // NORMALISASI KATEGORI UTAMA
            // =================================================
            $kategoriLower = strtolower(
                trim(
                    $kategori
                )
            );

            // =================================================
            // MODUL
            //
            // SEMUA PRODUK YANG KATEGORINYA MODUL
            // DIGABUNG MENJADI:
            //
            // Modul biMBA
            //
            // Contoh:
            //
            // kategori:
            // Modul biMBA
            //
            // sub_kategori:
            // Modul Baca
            // Modul Matematika
            // Modul Dikte
            //
            // SEMUANYA:
            // Modul biMBA
            // =================================================
            if (
                $kategoriLower === 'modul bimba'
                ||
                $kategoriLower === 'modul bimba unit'
                ||
                str_contains(
                    $kategoriLower,
                    'modul'
                )
            ) {

                $kategoriUmum =
                    'Modul biMBA';

            }

            // =================================================
            // MAJALAH
            // =================================================
            elseif (
                str_contains(
                    $kategoriLower,
                    'majalah'
                )
            ) {

                $kategoriUmum =
                    'Majalah Sahabat biMBA';

            }

            // =================================================
            // SERTIFIKAT
            // =================================================
            elseif (
                str_contains(
                    $kategoriLower,
                    'sertifikat'
                )
            ) {

                $kategoriUmum =
                    'Sertifikat';

            }

            // =================================================
            // KATEGORI LAIN
            //
            // Contoh:
            // Kaos Anak
            // =================================================
            else {

                $kategoriUmum =
                    $kategori;

            }

            // =================================================
            // MASUKKAN KATEGORI
            // =================================================
            if (
                !empty(
                    trim(
                        $kategoriUmum
                    )
                )
            ) {

                $kategoriList[] =
                    trim(
                        $kategoriUmum
                    );
            }
        }

        // =====================================================
        // HAPUS DUPLIKAT KATEGORI
        // =====================================================
        $kategoriList = collect(
            $kategoriList
        )
        ->filter()
        ->unique()
        ->values();

        // =====================================================
        // BUAT PESANAN
        //
        // CONTOH ORDER 847896:
        //
        // Kaos Anak
        // Modul Mewarnai Kartun
        // Modul Mewarnai Transportasi
        // Modul Baca
        // Modul Matematika
        //
        // HASIL:
        //
        // Kaos Anak | Modul biMBA
        // =====================================================
        $pesanan = $kategoriList->isEmpty()
            ? 'Media Pembelajaran biMBA AIUEO'
            : $kategoriList->implode(' | ');

        // =====================================================
        // NAMA UNIT
        // =====================================================
        // Nama Unit & Cabang diambil dari Database Stokis Mitra,
        // lalu Database User Export (biMBA Shop)
        $unitLogistik = $this->resolveUnitLogistik(
            $firstItem->billing_last_name,
            $firstItem->billing_company,
            $firstItem->item_name
                ?? $casdana?->customer
                ?? '-',
            $firstItem->billing_first_name
        );

        // Logistik Pasif: payment bacs / Billing Name Jakarta Pasif & Inventaris /
        // unit pasif di Unit Kemitraan / Stokis Mitra berstatus PASIF
        $rule = $this->cekLogistikPasif($firstItem);

        if (! $rule['pasif'] && ! $unitLogistik['pasif']) {
            $skipAktif++;
            continue;
        }

        if ($rule['override']) {
            $unitLogistik['nama_unit'] = $rule['override']['nama_unit'];
            $unitLogistik['no_cab']    = $rule['override']['no_cab'];
            $unitLogistik['matched']   = true;

            if ($rule['alasan'] !== 'unit pasif (Unit Kemitraan)') {
                $diganti++;
            }
        }

        if (! $unitLogistik['matched'] && count($belumCocok) < 8) {
            $belumCocok[] = "{$orderId} [first=" . trim((string) $firstItem->billing_first_name)
                . ' | company=' . trim((string) $firstItem->billing_company)
                . ' | last=' . trim((string) $firstItem->billing_last_name) . ']';
        }

        $namaUnit = $unitLogistik['nama_unit'];
        $noCab    = $unitLogistik['no_cab'];

        // =====================================================
        // ALAMAT
        // =====================================================
        $kirim = trim(
            implode(
                ', ',
                array_filter(
                    [
                        $firstItem->shipping_address_1,
                        $firstItem->shipping_address_2,
                        $firstItem->shipping_city
                    ]
                )
            )
        ) ?: $namaUnit;

        // =====================================================
        // STATUS PENGIRIMAN
        // =====================================================
        $ongkir = (int) (
            $firstItem->ship_total ?? 0
        );

        $statusKirim = $ongkir > 0
            ? 'Dikirim'
            : 'Diambil';

        // =====================================================
        // DATA HEADER
        // =====================================================
        $data = [

            'tgl_input' =>
                now()->format('Y-m-d'),

            'tgl_pesan' =>
                $firstItem->order_date,

            'kirim' =>
                $kirim,

            'no_telpon' =>
                $firstItem->shipping_phone
                ?? null,

            'alamat_kirim' =>
                $firstItem->shipping_address_1
                ?? null,

            'kab_kota_provinsi' =>
                $firstItem->shipping_city
                ?? null,

            'ongkir' =>
                $ongkir,

            'nama_unit' =>
                $namaUnit,
            'cabang' =>
                $noCab,

            'pesanan' =>
                $pesanan,

            'harga' =>
                $items->sum(
                    fn ($item) =>
                        ($item->item_price ?? 0)
                        *
                        ($item->item_qty ?? 1)
                ),

            'berat' =>
                $firstItem->order_weight
                ?? 0,

            'item_qty' =>
                $items->sum(
                    'item_qty'
                ),

            'total' =>
                $casdana?->amount
                ??
                $firstItem->order_total
                ??
                0,

            'jenis_bank' =>
                $casdana?->payment_channel
                ??
                $firstItem->payment_method,

            'status_pembayaran' =>
                $statusCasdana,

            'status_pesan' =>
                $firstItem->status,

            'id_pesan' =>
                $orderId,

            'status' =>
                'aktif',

            'payment_date' =>
                $paymentDate,

            'amount' =>
                $casdana?->amount
                ?? 0,

            'billing_last_name' =>
                $firstItem->billing_last_name
                ?? null,

            'billing_company' =>
                $firstItem->billing_company
                ?? null,

            'status_kirim' =>
                $statusKirim,

            'estimasi_print_pl' =>
                $estimasiPrintPl,

            'estimasi_persiapan' =>
                $estimasiPersiapan,
        ];

        // =====================================================
        // TRANSACTION
        // =====================================================
        DB::transaction(
            function () use (
                $data,
                $items,
                $productCache,
                &$inserted
            ) {

                // =================================================
                // CREATE HEADER
                // =================================================
                $jakarta =
                    LogistikPasif::create(
                        $data
                    );

                // =================================================
                // CREATE DETAIL ITEM
                //
                // DETAIL TETAP DISIMPAN SATU-SATU
                //
                // Contoh:
                //
                // Modul Baca
                // Modul Matematika
                // Modul Dikte
                //
                // TETAP MENJADI ITEM TERPISAH.
                //
                // YANG DIGABUNG HANYA KATEGORI PADA HEADER
                // =================================================
                foreach (
                    $items
                    as $item
                ) {

                    $sku = strtoupper(
                        trim(
                            $item->item_sku
                            ?? ''
                        )
                    );

                    if (
                        empty($sku)
                    ) {
                        continue;
                    }

                    $searchCode =
                        trim(
                            explode(
                                '-',
                                $sku
                            )[0]
                        );

                    $product =
                        $productCache[
                            $searchCode
                        ] ?? null;

                    $qty = (int) (
                        $item->item_qty
                        ?? 1
                    );

                    $harga = (float) (
                        $item->item_price
                        ?? 0
                    );

                    LogistikPasifItem::create([

                        'logistik_pasif_id' =>
                            $jakarta->id,

                        'product_id' =>
                            $product?->id,

                        'sku' =>
                            $sku,

                        'label' =>
                            $product?->label
                            ??
                            $searchCode,

                        'nama_produk' =>
                            $product?->name
                            ??
                            $item->item_name,

                        'qty' =>
                            $qty,

                        'harga' =>
                            $harga,

                        'subtotal' =>
                            $qty * $harga,
                    ]);
                }

                $inserted++;
            }
        );
    }

    // =====================================================
    // HASIL SYNC
    // =====================================================
    return redirect()
        ->route(
            'order.logistik-pasif'
        )
        ->with(
            'success',
            "✅ Berhasil sync {$inserted} data Logistik Pasif (SKU LG, Stokis Pasif). Nama Unit/Cabang diperbarui: {$updatedUnit}."
                . ($skipAktif ? " Dilewati (bukan Stokis Pasif): {$skipAktif}." : '')
                . ($diganti ? " Unit aktif diganti ke Stokis Inventaris: {$diganti}." : '')
                . ($dipindahAktif ? " Bukan pasif, dihapus dari Pasif (jalankan Sync LG Aktif): {$dipindahAktif}." : '')
                . (count($belumCocok) ? ' | Belum cocok di Stokis Mitra: ' . implode('; ', $belumCocok) : '')
        );
}

/**
 * Normalisasi nama stokis untuk pencocokan:
 * huruf kecil, tanpa tanda baca, "stockis/stockist/stokist" => "stokis",
 * "pengelola unit aktif khusus" => "pua".
 */
private function normalisasiNamaStokis($v): string
{
    $v = strtolower(trim((string) $v));
    $v = preg_replace('/[^a-z0-9]+/', ' ', $v);
    $v = preg_replace('/\b(stockis|stockist|stokist)\b/', 'stokis', $v);
    $v = preg_replace('/\bpengelola unit aktif khusus\b/', 'pua', $v);

    return trim(preg_replace('/\s+/', ' ', $v));
}

/**
 * Cari baris Stokis Mitra: lewat kode (no_cab) lalu lewat nama.
 */
private function cariStokisMitra(array $kodeCands, array $namaCands)
{
    static $semua = null;

    if ($semua === null) {
        $semua = StokisMitra::all();
    }

    foreach ($kodeCands as $kode) {
        $kode = strtolower(trim((string) $kode));

        if ($kode === '') {
            continue;
        }

        foreach ($semua as $st) {
            if (strtolower(trim((string) $st->no_cab)) === $kode) {
                return $st;
            }
        }
    }

    foreach ($namaCands as $nama) {
        $n = $this->normalisasiNamaStokis($nama);

        if ($n === '') {
            continue;
        }

        foreach ($semua as $st) {
            if (
                $this->normalisasiNamaStokis($st->nama_stokis_db_bimbashop) === $n
                || $this->normalisasiNamaStokis($st->nama_stokis_db_kemitraan) === $n
            ) {
                return $st;
            }
        }
    }

    return null;
}

/**
 * Cari No Cab + Nama Unit Logistik.
 *
 * Contoh:
 *  - Nama "STOKIS" + Cabang "CIREBON 1"  => digabung "STOKIS CIREBON 1" => cocok Stokis Mitra => no cab + nama dari Stokis Mitra
 *  - Nama "Pengelola Unit Aktif Khusus" + Cabang "2" => "Pengelola Unit Aktif Khusus 2" (= PUA 2) => cocok Stokis Mitra
 *
 * Urutan: (1) Stokis Mitra via kode, (2) Stokis Mitra via gabungan/nama,
 * (3) User Export biMBA Shop (nama + kode di ujung first_name) lalu dicocokkan lagi ke Stokis Mitra,
 * (4) fallback gabungan nama unit + cabang.
 */
private function resolveUnitLogistik($billingLastName, $billingCompany, $fallback = null, $billingFirstName = null): array
{
    $last    = trim((string) $billingLastName);
    $company = trim((string) $billingCompany);
    $first   = trim((string) $billingFirstName);

    $keys = collect([$last, $company, $first])->filter()->unique()->values()->all();

    // kandidat nama: gabungan (nama depan / perusahaan + belakang) dulu, baru masing-masing
    $namaCands = [];

    foreach ([$company, $first] as $depan) {
        if ($last !== '' && $depan !== '') {
            $namaCands[] = $depan . ' ' . $last;
            $namaCands[] = $last . ' ' . $depan;
        }
    }

    if ($company !== '' && $first !== '') {
        $namaCands[] = $first . ' ' . $company;
    }

    foreach ($keys as $k) {
        $namaCands[] = $k;
    }

    // ---------- 1 & 2. STOKIS MITRA ----------
    $stokis = $this->cariStokisMitra($keys, $namaCands);

    // ---------- 3. USER EXPORT BIMBA SHOP ----------
    $namaUser  = null;
    $noCabUser = null;
    $user      = null;

    if (! $stokis) {
        foreach ($keys as $key) {
            $user = UserExportBimbaShop::where(function ($q) use ($key) {
                    $q->where('first_name', 'like', '% ' . $key)
                      ->orWhere('first_name', $key)
                      ->orWhere('last_name', $key)
                      ->orWhere('user_login', $key)
                      ->orWhere('billing_last_name', $key);
                })
                ->orderByDesc('ID')
                ->first();

            if ($user) {
                break;
            }
        }

        if ($user) {
            $namaUser  = trim((string) ($user->billing_first_name ?: $user->display_name ?: $user->first_name)) ?: null;
            $firstName = trim((string) $user->first_name);
            $userNama  = [];

            if (preg_match('/(\d+\.?[A-Za-z]*)\s*$/', $firstName, $m)) {
                $noCabUser = strtoupper($m[1]);
                $userNama[] = trim(preg_replace('/\s*' . preg_quote($m[1], '/') . '\s*$/', '', $firstName));
            } else {
                $userNama[] = $firstName;
            }

            if ($namaUser) {
                $userNama[] = $namaUser;
            }

            $stokis = $this->cariStokisMitra($noCabUser ? [$noCabUser] : [], $userNama);
        }
    }

    // ---------- HASIL ----------
    $noCab    = null;
    $namaUnit = null;

    if ($stokis) {
        $noCab    = trim((string) $stokis->no_cab) ?: null;
        $namaUnit = trim((string) ($stokis->nama_stokis_db_bimbashop ?: $stokis->nama_stokis_db_kemitraan)) ?: null;
    }

    if (! $namaUnit) {
        $namaUnit = $namaUser;
    }

    if (! $noCab) {
        $noCab = $noCabUser;
    }

    // ---------- 4. FALLBACK ----------
    if (! $namaUnit) {
        $kodeLike = $last !== '' && preg_match('/^\d{4,}\.?[A-Za-z]*$/', $last);

        $depan = $company !== '' ? $company : $first;

        if ($depan !== '' && $last !== '' && ! $kodeLike) {
            // belum ketemu di database: gabungkan nama unit + cabang
            $namaUnit = $depan . ' ' . $last;
            $noCab    = $noCab ?: '-';
        } else {
            $namaUnit = $depan !== '' ? $depan : ($fallback ?: '-');
        }
    }

    if (! $noCab) {
        $noCab = $last !== '' ? $last : null;
    }

    return [
        'no_cab'    => $noCab,
        'nama_unit' => $namaUnit,
        'matched'   => (bool) $stokis,
        'pasif'     => $stokis && strtoupper(trim((string) $stokis->status)) === 'PASIF',
    ];
}

    public function editLogistikPasif($id)
{
    $item = LogistikPasif::findOrFail($id);
    return view('order::order.logistik-pasif-edit', compact('item'));
}

public function updateLogistikPasif(Request $request, $id)
{
    $item = LogistikPasif::findOrFail($id);

    // =====================================================
    // KUNCI: Jika sudah REFUND / REFUNDED → tidak boleh diedit lagi
    // =====================================================
    if (in_array(strtoupper($item->status_pembayaran ?? ''), ['REFUND', 'REFUNDED'])) {
        return back()->with('error', 'Data sudah berstatus REFUND dan tidak dapat diedit lagi.');
    }

    $validated = $request->validate([
        'nama_unit'          => 'nullable|string|max:255',
        'billing_last_name'  => 'nullable|string|max:100',
        'pesanan'            => 'nullable|string|max:500',
        'alamat_pengiriman'  => 'nullable|string',
        'service_pengiriman' => 'nullable|string|max:100',
        'ekspedisi'          => 'nullable|string|max:100',
        'status_kirim'       => 'nullable|in:Dikirim,Diambil,Belum Dikirim',
        'status_pembayaran'  => 'nullable|string|max:50',
        'validasi'           => 'nullable|string|max:50',
        'harga'              => 'nullable|numeric|min:0',
        'diskon'             => 'nullable|numeric|min:0',
        'ongkir'             => 'nullable|numeric|min:0',
        'fee_payment'        => 'nullable|numeric|min:0',
        'berat'              => 'nullable|numeric|min:0',
        'total'              => 'nullable|numeric|min:0',
        'catatan'            => 'nullable|string',
    ]);

    // =====================================================
    // LOGIKA REFUND
    // =====================================================
    $totalBaru = isset($validated['total']) ? (float) $validated['total'] : (float) $item->total;
    $totalLama = (float) $item->total;

    if ($totalBaru == 0) {
        // Full Refund
        $validated['status_pembayaran'] = 'REFUND';
    } elseif ($totalBaru < $totalLama && $totalBaru > 0) {
        // Partial Refund
        $validated['status_pembayaran'] = 'PARTIAL_REFUND';
    }

    // Catatan
    $catatanBaru = $validated['catatan'] ?? $item->catatan;
    $catatanBaru = trim(($catatanBaru ?? '') . "\n\nDiubah manual pada " . now()->format('d/m/Y H:i:s'));

    if (($validated['status_pembayaran'] ?? '') === 'REFUND') {
        $catatanBaru .= "\n[Sistem] Full Refund - Order Total dijadikan 0";
    } elseif (($validated['status_pembayaran'] ?? '') === 'PARTIAL_REFUND') {
        $catatanBaru .= "\n[Sistem] Partial Refund - Total dari Rp " . number_format($totalLama, 0, ',', '.') . 
                        " menjadi Rp " . number_format($totalBaru, 0, ',', '.');
    }

    $item->update([
        'nama_unit'          => $validated['nama_unit'] ?? $item->nama_unit,
        'billing_last_name'  => $validated['billing_last_name'] ?? $item->billing_last_name,
        'pesanan'            => $validated['pesanan'] ?? $item->pesanan,
        'kirim'              => $validated['alamat_pengiriman'] ?? $item->kirim,
        'alamat_kirim'       => $validated['alamat_pengiriman'] ?? $item->alamat_kirim,
        'service_pengiriman' => $validated['service_pengiriman'] ?? $item->service_pengiriman,
        'ekspedisi'          => $validated['ekspedisi'] ?? $item->ekspedisi,
        'status_kirim'       => $validated['status_kirim'] ?? $item->status_kirim,
        'status_pembayaran'  => $validated['status_pembayaran'] ?? $item->status_pembayaran,
        'validasi'           => $validated['validasi'] ?? $item->validasi,
        'harga'              => $validated['harga'] ?? $item->harga,
        'diskon'             => $validated['diskon'] ?? $item->diskon,
        'ongkir'             => $validated['ongkir'] ?? $item->ongkir,
        'fee_payment'        => $validated['fee_payment'] ?? $item->fee_payment,
        'berat'              => $validated['berat'] ?? $item->berat,
        'total'              => $validated['total'] ?? $item->total,
        'catatan'            => $catatanBaru,
    ]);

    return redirect()->route('order.logistik-pasif')
                     ->with('success', '✅ Data berhasil diupdate!');
}

/**
 * Proses bulk Logistik Pasif.
 * Menandai data sebagai diproses + menyimpan distribusi / kurir / service / catatan.
 * (Belum membuat Realisasi / Rekap Aktual - tabel realisasi Logistik belum ada.)
 */
public function bulkActionLogistikPasif(Request $request)
{
    $action  = $request->input('action');
    $perItem = $request->input('per_item');

    if ($action !== 'processed' || empty($perItem)) {
        return redirect()->back()->with('error', 'Data tidak valid.');
    }

    $updates = json_decode($perItem, true);

    if (empty($updates)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $now = Carbon::now('Asia/Jakarta');
    $successCount = 0;

    foreach ($updates as $update) {
        $id = $update['id'] ?? null;

        if (! $id) {
            continue;
        }

        $logistik = LogistikPasif::with('items.product')->find($id);

        if (! $logistik) {
            continue;
        }

        // Data full refund tidak boleh diproses
        if (in_array(strtoupper($logistik->status_pembayaran ?? ''), ['REFUND', 'REFUNDED'])) {
            continue;
        }

        $logistik->is_processed = true;
        $logistik->processed_at = $now;

        if (! empty($update['status_kirim'])) {
            $logistik->status_kirim = $update['status_kirim'];
        }
        if (! empty($update['jasa_kurir'])) {
            $logistik->ekspedisi = $update['jasa_kurir'];
        }
        if (! empty($update['service_kurir'])) {
            $logistik->service_pengiriman = $update['service_kurir'];
        }
        if (! empty($update['catatan'])) {
            $logistik->catatan = ($logistik->catatan ?? '')
                . "\n\nDi proses bulk pada " . $now->format('d/m/Y H:i')
                . ': ' . trim($update['catatan']);
        }

        $logistik->save();

        // Masuk ke Rekap Aktual
        $this->buatRealisasiLogistik($logistik);

        $successCount++;
    }

    return redirect()
        ->route('order.logistik-pasif')
        ->with('success', "✅ {$successCount} data Logistik berhasil diproses.");
}

/**
 * Buat baris Rekap Aktual (realisasi) dari order Logistik Pasif.
 * Aman dipanggil berulang: satu order hanya punya satu realisasi.
 */
public function buatRealisasiLogistik(LogistikPasif $logistik): void
{
    if (RealisasiLogistikPasif::where('logistik_pasif_id', $logistik->id)->exists()) {
        return;
    }

    $items = $logistik->items()->with('product')->get();

    if ($items->isEmpty()) {
        return;
    }

    // Nama unit harus sama persis dengan yang tampil di Logistik Pasif
    $namaUnit = trim((string) $logistik->nama_unit) !== ''
        ? trim($logistik->nama_unit)
        : $this->resolveNamaUnit(
            $logistik->billing_company,
            $logistik->billing_last_name,
            $logistik->nama_unit
        );

    $namaBarang = $items
        ->groupBy(fn ($i) => trim($i->product?->kategori ?: $i->nama_produk ?: 'Lainnya'))
        ->map(function ($rows, $kategori) {
            $qty = $rows
                ->groupBy(fn ($i) => strtoupper(trim($i->sku ?? '')))
                ->sum(fn ($skuRows) => (int) ($skuRows->first()->qty ?? 0));

            return "{$kategori} ({$qty})";
        })
        ->values()
        ->implode(' | ');

    $productIds = $items->pluck('product_id')->filter()->unique()->values()->toArray();

    $diambil = in_array(strtolower($logistik->status_kirim ?? ''), ['diambil', 'ambil']);

    $estimasiHari = null;
    if ($logistik->payment_date && $logistik->estimasi_persiapan) {
        $estimasiHari = Carbon::parse($logistik->payment_date)
            ->diffInDays(Carbon::parse($logistik->estimasi_persiapan));
    }

    RealisasiLogistikPasif::create([
        'logistik_pasif_id'  => $logistik->id,
        'no_pl'              => $logistik->id_pesan,
        'tgl_turun_pl'       => $logistik->tgl_pesan,
        'nama_unit'          => $namaUnit,
        'billing_last_name'  => $logistik->billing_last_name,
        'billing_company'    => $logistik->billing_company,
        'pengiriman'         => $logistik->ekspedisi ?: ($diambil ? 'Diambil' : '-'),
        'service_pengiriman' => $logistik->service_pengiriman ?: ($diambil ? 'Diambil' : null),
        'nama_barang'        => $namaBarang,
        'kategori_order'     => 'Logistik',
        'product_id'         => $productIds[0] ?? null,
        'product_ids'        => $productIds ? json_encode($productIds) : null,
        'tgl_bayar'          => $logistik->payment_date,
        'jumlah_bayar'       => $logistik->total ?? 0,
        'order_weight'       => $logistik->berat ?? 0,
        'nama_stokis'        => 'Stokis Logistik Pasif',
        'tgl_estimasi'       => $logistik->estimasi_persiapan,
        'estimasi_hari'      => $estimasiHari,
        'penyebut'           => $namaUnit,
        'pengambil'          => $diambil ? 'Ambil Sendiri' : null,
        'ket'                => $logistik->catatan,
    ]);
}



public function getModalData(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    $data = LogistikPasif::whereIn('id', $ids)
        ->select([
            'id',
            'id_pesan',
            'nama_unit',
            'status_pembayaran',
            'jenis_bank',
            'pesanan',
            'status_kirim',
            'payment_date',
            'is_processed',
            'processed_at',
            'ekspedisi',              // ← tambahkan
            'service_pengiriman',     // ← tambahkan
        ])
        ->get()
        ->map(function ($item) {
            $vendor = $this->extractVendorFromSku($item->pesanan ?? '');

            // Default khusus majalah MANUAL
            $isManualMajalah = strtoupper(trim($item->status_pembayaran ?? '')) === 'MANUAL'
                && (
                    str_contains(strtolower($item->pesanan ?? ''), 'majalah')
                    || preg_match('/\bM\d{2,4}\b/i', $item->pesanan ?? '')
                );

            $jasaKurir = $item->ekspedisi;
            $service   = $item->service_pengiriman;

            if ($isManualMajalah) {
                $jasaKurir = $jasaKurir ?: 'Lion Parcel';
                $service   = $service   ?: 'REGPACK';
            }

            return [
                'id'                => $item->id,
                'invoice'           => $item->id_pesan ?? '-',
                'to_customer'       => $item->nama_unit ?? '-',
                'pesanan'           => $item->pesanan ?? '-',
                'payment_date'      => $item->payment_date
                                        ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i')
                                        : '-',
                'payment_channel'   => $item->jenis_bank ?? '-',
                'status_pembayaran' => $item->status_pembayaran ?? '-',
                'status_kirim'      => $item->status_kirim ?? 'Dikirim',
                'vendor'            => $vendor,
                'jasa_kurir'        => $jasaKurir,          // ← kirim ke frontend
                'service_kurir'     => $service,            // ← kirim ke frontend
                'is_processed'      => (bool) $item->is_processed,
                'processed_at'      => $item->processed_at
                                        ? \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i')
                                        : null,
            ];
        });

    return response()->json($data);
}

// === TAMBAHKAN METHOD INI DI DALAM CLASS OrderController ===
public function getFilteredIds(Request $request)
{
    $query = LogistikPasif::query();

    // =====================================================
    // FILTER BERDASARKAN MENU
    // =====================================================
    switch ($request->route) {

        // ================= MODUL =================
        case 'order.modul':
            $query->whereHas('items', function ($q) {
                $q->where(function ($x) {
                    $x->whereHas('product', function ($p) {
                        $p->where('kategori', 'Modul');
                    });
                    $x->orWhere('nama_produk', 'like', '%Modul%')
                      ->orWhere('label', 'like', '%Modul%');
                });
            });
            break;

        // ================= MAJALAH =================
        case 'order.majalah':
            $query->whereHas('items', function ($q) {
                $q->where(function ($x) {
                    $x->whereHas('product', function ($p) {
                        $p->where('kategori', 'Majalah');
                    });
                    $x->orWhere('nama_produk', 'like', '%Majalah%')
                      ->orWhere('nama_produk', 'like', '%M159%')
                      ->orWhere('label', 'like', '%M159%');
                });
            });
            break;

        // ================= SERTIFIKAT =================
        case 'order.sertifikat':
            $query->whereHas('items', function ($q) {
                $q->where(function ($x) {
                    $x->whereHas('product', function ($p) {
                        $p->where('kategori', 'Sertifikat');
                    });
                    $x->orWhere('nama_produk', 'like', '%STA%')
                      ->orWhere('nama_produk', 'like', '%STPB%')
                      ->orWhere('label', 'like', '%STA%')
                      ->orWhere('label', 'like', '%STPB%');
                });
            });
            break;

        // ================= SEMUA =================
        default:
            break;
    }

    // =====================================================
    // FILTER TANGGAL & LAINNYA
    // =====================================================
    if ($request->filled('start_date')) {
        $query->whereDate('tgl_pesan', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_pesan', '<=', $request->end_date);
    }

    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', '%' . $request->kirim . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('pesanan')) {
        $query->where('pesanan', 'like', '%' . $request->pesanan . '%');
    }

    if ($request->filled('status_pembayaran')) {
        $query->where('status_pembayaran', $request->status_pembayaran);
    }

    // =====================================================
    // SYARAT UTAMA
    // =====================================================
    $ids = $query
        ->where(function ($q) {
            $q->where('is_processed', 0)
              ->orWhereNull('is_processed');
        })
        // ===== BLOKIR FULL REFUND =====
        ->whereNotIn('status_pembayaran', ['REFUND', 'REFUNDED'])
        ->pluck('id');

    return response()->json([
        'ids'   => $ids->values(),
        'count' => $ids->count(),
    ]);
}

public function exportLogistikPasif(Request $request)
{
    $filename = 'Logistik_Aktif_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(
        new \App\Modules\Order\Exports\LogistikPasifExport($request), 
        $filename
    );
}
}
