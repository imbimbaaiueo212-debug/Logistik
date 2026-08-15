<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Models\RealisasiAktif;
use App\Models\Picking;          // Tambahkan
use App\Models\PickingItem;      // Tambahkan
use App\Models\JakartaAktifItem;
use App\Models\Product;
use App\Models\MatchingUserExport;
use App\Models\PesananMajalah;
use App\Models\UnitKemitraan;
use App\Models\DlcPeriode;
use App\Models\DlcPesanan;
use App\Imports\JakartaAktifImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // Tambahkan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        return view('order.index');
    }

public function jakartaAktif(Request $request)
{
    $query = JakartaAktif::query();

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

    $listStatusBayar = JakartaAktif::select('status_pembayaran')
        ->whereNotNull('status_pembayaran')
        ->where('status_pembayaran', '!=', '')
        ->distinct()
        ->orderBy('status_pembayaran')
        ->pluck('status_pembayaran');

    $listPesanan = JakartaAktif::select('pesanan')
        ->whereNotNull('pesanan')
        ->where('pesanan', '!=', '')
        ->distinct()
        ->orderBy('pesanan')
        ->pluck('pesanan');

    $listNamaUnit = JakartaAktif::select('nama_unit')
        ->whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $listGrup = JakartaAktif::select('grup')
        ->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    $unitTidakPesan = $this->getUnitTidakPesanMajalah();

    // Daftar no_cab yang masih mismatch (untuk badge di index)
    $mismatchNoCab = \App\Models\UnitNamaMismatch::where('is_resolved', false)
        ->pluck('no_cab')
        ->map(fn ($v) => trim((string) $v))
        ->filter()
        ->unique()
        ->flip()
        ->all();

    return view('order.jakarta-aktif-index', compact(
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
 * Ambil daftar unit dari Pesanan Majalah yang qty = 0
 */
private function getUnitTidakPesanMajalah(): array
{
    $list = [];

    // Kabupaten
    $periodes = PesananMajalah::with(['kabupaten.units', 'kotamadya.units'])->get();

    foreach ($periodes as $periode) {
        foreach ($periode->kabupaten ?? [] as $kab) {
            foreach ($kab->units ?? [] as $unit) {
                if (($unit->jumlah_pesanan ?? 0) <= 0) {
                    $list[] = [
                        'nama'     => $unit->nama_unit ?? '-',
                        'no_cab'   => trim($unit->no_cabang ?? ''),
                        'wilayah'  => $kab->nama_kabupaten ?? '-',
                        'sumber'   => 'Kabupaten',
                        'periode'  => ($periode->bulan ?? '') . ' ' . ($periode->tahun ?? ''),
                    ];
                }
            }
        }

        foreach ($periode->kotamadya ?? [] as $kota) {
            foreach ($kota->units ?? [] as $unit) {
                if (($unit->jumlah_pesanan ?? 0) <= 0) {
                    $list[] = [
                        'nama'     => $unit->nama_unit ?? '-',
                        'no_cab'   => trim($unit->no_cabang ?? ''),
                        'wilayah'  => $kota->nama_kotamadya ?? '-',
                        'sumber'   => 'Kotamadya',
                        'periode'  => ($periode->bulan ?? '') . ' ' . ($periode->tahun ?? ''),
                    ];
                }
            }
        }
    }

    // PUW1
    $periodesPuw1 = \App\Models\PesananMajalahPuw1::with('units')->get();

    foreach ($periodesPuw1 as $periode) {
        foreach ($periode->units ?? [] as $unit) {
            if (($unit->jumlah_pesanan ?? 0) <= 0) {
                $list[] = [
                    'nama'     => $unit->nama_unit ?? '-',
                    'no_cab'   => trim($unit->no_cabang ?? ''),
                    'wilayah'  => $unit->kabupaten_kota ?? '-',
                    'sumber'   => 'PUW1',
                    'periode'  => ($periode->bulan ?? '') . ' ' . ($periode->tahun ?? ''),
                ];
            }
        }
    }

    // Unik berdasarkan no_cab + nama
    $unique = collect($list)
        ->unique(fn ($i) => $i['no_cab'] . '|' . $i['nama'])
        ->values()
        ->all();

    return $unique;
}

    // Import Manual
    public function importJakartaAktif(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ]);

        try {
            Excel::import(new JakartaAktifImport, $request->file('file'));
            return redirect()->route('order.jakarta-aktif')
                             ->with('success', '✅ Data berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', '❌ Gagal import: ' . $e->getMessage());
        }
    }

       
/**
 * Sync dari Bimbashop + Casdana 
 * Hanya JKT murni (dikecualikan JKTP dan semua stokis lain)
 */
public function syncJktFromBimbashop()
{
    $totalOrder  = 0;
    $skipExists  = 0;
    $skipCasdana = 0;
    $skipStatus  = 0;
    $inserted    = 0;

    $excludedSkus = [
        'JKTP', 'PUA1', 'PUA2', 'PUA3', 'DPK1', 'SRG1', 'KWG1', 'BKS1',
        'BGR1', 'TNG1', 'SNG', 'BGRT', 'PWK', 'TNG2', 'KNG', 'IDM',
        'SKB1', 'SKB2', 'BDG1', 'BDG2', 'CIL1', 'SRG2', 'DPR1', 'KWG2',
        'BGR3', '-LG', 'DLC', 'EBT', 'SMG', 'SBY', 'YYK', 'INV', 'SGN',
        'YK1', 'GR2', 'ENB', 'RB1', 'TNG3'
    ];

    // =====================================================
    // AMBIL ORDER JKT
    // =====================================================
    $bimbashopOrders = BimbashopOrder::where(
            'item_sku',
            'like',
            '%JKT%'
        )
        ->whereNotIn(
            'item_sku',
            $excludedSkus
        )
        ->where(function ($q) use ($excludedSkus) {

            foreach ($excludedSkus as $sku) {
                $q->where(
                    'item_sku',
                    'not like',
                    "%{$sku}%"
                );
            }

        })
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
            JakartaAktif::where(
                'id_pesan',
                $orderId
            )->exists()
        ) {
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

        if (!$casdana) {
            $skipCasdana++;
            continue;
        }

        // =================================================
        // STATUS PEMBAYARAN
        // =================================================
        $statusCasdana = strtoupper(
            trim(
                $casdana->status ?? ''
            )
        );

        if (
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
        $paymentDate       = $casdana->payment_date;
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
                        'JKT',
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
                                    'JKT',
                                    '-JKT'
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
        $namaUnit = $this->resolveNamaUnit(
            $firstItem->billing_company,
            $firstItem->billing_last_name,
            $firstItem->item_name
                ?? $casdana->customer
                ?? '-'
        );

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
                $casdana->amount
                ??
                $firstItem->order_total
                ??
                0,

            'jenis_bank' =>
                $casdana->payment_channel
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
                $casdana->amount
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
                    JakartaAktif::create(
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

                    JakartaAktifItem::create([

                        'jakarta_aktif_id' =>
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
            'order.jakarta-aktif'
        )
        ->with(
            'success',
            "✅ Berhasil sync {$inserted} data JKT murni!"
        );
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
        $unit = \App\Models\UnitKemitraan::where('no_cab', $billingLastName)->first();
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

    public function unitAktif() { return view('order.unit-aktif'); }
    public function unitPasif() { return view('order.unit-pasif'); }

        // ====================== EDIT JAKARTA AKTIF ======================
    public function editJakartaAktif($id)
{
    $item = JakartaAktif::findOrFail($id);
    return view('order.jakarta-aktif-edit', compact('item'));
}

public function updateJakartaAktif(Request $request, $id)
{
    $item = JakartaAktif::findOrFail($id);

    $request->validate([
    'nama_unit'          => 'nullable|string|max:255',
    'billing_last_name'  => 'nullable|string|max:100',
    'pesanan'            => 'nullable|string|max:255',
    'alamat_pengiriman'  => 'nullable|string',
    'service_pengiriman' => 'nullable|string|max:100',     // ← BARU
    'ekspedisi'          => 'nullable|string|max:100',
    'status_kirim'       => 'nullable|in:Dikirim,Belum Dikirim',
    'status_pembayaran'  => 'nullable|string|max:50',
    'validasi'           => 'nullable|string|max:50',
]);

$item->update([
    'nama_unit'          => $request->nama_unit,
    'billing_last_name'  => $request->billing_last_name,
    'pesanan'            => $request->pesanan,
    'alamat_pengiriman'  => $request->alamat_pengiriman,
    'kirim'              => $request->alamat_pengiriman,
    'service_pengiriman' => $request->service_pengiriman,   // ← BARU
    'ekspedisi'          => $request->ekspedisi,
    'status_kirim'       => $request->status_kirim,
    'status_pembayaran'  => $request->status_pembayaran,
    'validasi'           => $request->validasi,
    'catatan'            => ($item->catatan ?? '') . "\n\nDiubah manual pada " . now()->format('d/m/Y H:i:s'),
]);

    return redirect()->route('order.jakarta-aktif')
                     ->with('success', '✅ Data berhasil diupdate!');
}
public function bulkActionJakartaAktif(Request $request)
{
    $action  = $request->input('action');
    $perItem = $request->input('per_item');

    // ======================================================
    // VALIDASI REQUEST
    // ======================================================
    if ($action !== 'processed' || empty($perItem)) {
        return redirect()->back()
            ->with('error', 'Data tidak valid.');
    }

    $updates = json_decode($perItem, true);

    if (empty($updates)) {
        return redirect()->back()
            ->with('error', 'Tidak ada data yang dipilih.');
    }

    $now = Carbon::now('Asia/Jakarta');

    $route = $request->input(
        'redirect',
        'order.jakarta-aktif'
    );

    $successCount = 0;

    // ======================================================
    // LOOP DATA YANG DIPILIH
    // ======================================================
    foreach ($updates as $update) {

        $id = $update['id'] ?? null;

        if (!$id) {
            continue;
        }

        $jakarta = JakartaAktif::find($id);

        if (!$jakarta) {
            continue;
        }

        // ==================================================
        // DATA UPDATE
        // ==================================================
        $statusKirim = $update['status_kirim']
            ?? $jakarta->status_kirim;

        $jasaKurir = $update['jasa_kurir']
            ?? $jakarta->ekspedisi;

        $serviceKurir = $update['service_kurir']
            ?? $jakarta->service_pengiriman;

        $catatan = $update['catatan'] ?? null;

        // ==================================================
        // UPDATE JAKARTA AKTIF
        // ==================================================
        $setClauses = [
            "is_processed = 1",
            "processed_at = ?",
            "updated_at = ?"
        ];

        $bindings = [
            $now,
            $now
        ];

        if ($statusKirim) {
            $setClauses[] = "status_kirim = ?";
            $bindings[] = $statusKirim;
        }

        if ($jasaKurir) {
            $setClauses[] = "ekspedisi = ?";
            $bindings[] = $jasaKurir;
        }

        if ($serviceKurir) {
            $setClauses[] = "service_pengiriman = ?";
            $bindings[] = $serviceKurir;
        }

        if ($catatan) {

            $newNote =
                "\n\nDi proses bulk pada "
                . $now->format('d/m/Y H:i')
                . ": "
                . trim($catatan);

            $setClauses[] =
                "catatan = CONCAT(COALESCE(catatan, ''), ?)";

            $bindings[] = $newNote;
        }

        DB::update(
            "UPDATE jakarta_aktif SET "
            . implode(', ', $setClauses)
            . " WHERE id = ?",
            array_merge(
                $bindings,
                [$id]
            )
        );

        // ======================================================
        // AMBIL SEMUA ITEM
        // ======================================================
        $allItems = $jakarta
            ->items()
            ->with('product')
            ->get();

        if ($allItems->isEmpty()) {
            continue;
        }

        // ======================================================
        // PEMBAGIAN KATEGORI ORDER
        // ======================================================
        if ($route === 'order.jakarta-aktif') {

            $groups = [
                'Modul'      => collect(),
                'Majalah'    => collect(),
                'Sertifikat' => collect(),
            ];

            foreach ($allItems as $item) {

                // ==================================================
                // AMBIL KATEGORI TERBARU DARI MASTER PRODUCTS
                // ==================================================
                $kategori = trim(
                    $item->product?->kategori ?? ''
                );

                $kategoriLower = strtolower($kategori);

                // ==================================================
                // MAJALAH
                // ==================================================
                if (str_contains($kategoriLower, 'majalah')) {

                    $groups['Majalah']->push($item);

                }

                // ==================================================
                // SERTIFIKAT
                // ==================================================
                elseif (str_contains($kategoriLower, 'sertifikat')) {

                    $groups['Sertifikat']->push($item);

                }

                // ==================================================
                // SELAIN MAJALAH & SERTIFIKAT
                // MASUK MODUL
                // ==================================================
                else {

                    $groups['Modul']->push($item);
                }
            }

        } else {

            // ==============================================================
            // MENU KHUSUS
            // ==============================================================
            $kategoriOrder = match ($route) {

                'order.modul'
                    => 'Modul',

                'order.majalah'
                    => 'Majalah',

                'order.sertifikat'
                    => 'Sertifikat',

                default
                    => 'Lainnya',
            };

            $groups = [
                $kategoriOrder =>
                    $this->getFilteredItems(
                        $jakarta,
                        $route
                    )
            ];
        }

        // ======================================================
        // PROSES SETIAP GROUP
        // ======================================================
        foreach (
            $groups as $kategoriOrder => $items
        ) {

            if ($items->isEmpty()) {
                continue;
            }

            // ==================================================
            // CEK SUDAH ADA REALISASI UNTUK KATEGORI INI
            // ==================================================
            if (
                $this->hasRealisasiForCategory(
                    $jakarta->id,
                    $kategoriOrder
                )
            ) {
                continue;
            }

            // ==================================================
            // NAMA UNIT
            // ==================================================
            $namaUnit =
                $this->resolveNamaUnit(
                    $jakarta->billing_company,
                    $jakarta->billing_last_name,
                    $jakarta->nama_unit
                );

            // ==================================================
            // NAMA STOKIS
            // ==================================================
            $skuList = $items
                ->pluck('sku')
                ->filter()
                ->implode('|');

            $namaStokis =
                $this->extractVendorFromSku(
                    $skuList
                );

            // ==================================================
            // NAMA BARANG
            //
            // Tetap menggunakan kategori TERBARU dari Master Product
            // ==================================================
            $namaBarang = $items
    ->groupBy(function ($item) {

        return trim(
            $item->product?->kategori
            ?? $item->kategori
            ?? 'Lainnya'
        );
    })
    ->map(function ($rows, $kategori) {

        $qty = $rows
            ->groupBy(function ($item) {
                return strtoupper(
                    trim($item->sku ?? '')
                );
            })
            ->sum(function ($skuRows) {

                return (int) (
                    $skuRows->first()->qty ?? 0
                );
            });

        return "{$kategori} ({$qty})";
    })
    ->values()
    ->implode(' | ');

            // ==================================================
            // PRODUCT ID UNTUK REALISASI AKTIF
            //
            // Ambil product_id dari item yang benar-benar ada
            // pada group ini.
            //
            // Jika hanya ada 1 product_id -> simpan ID tersebut.
            // Jika ada beberapa product_id -> ambil product_id
            // pertama yang valid sebagai relasi utama.
            // ==================================================
            $productId = $items
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values()
                ->first();

            // ==================================================
            // ESTIMASI HARI
            // ==================================================
            $estimasiHari = null;

            if (
                $jakarta->payment_date
                &&
                $jakarta->estimasi_persiapan
            ) {

                $estimasiHari =
                    Carbon::parse(
                        $jakarta->payment_date
                    )->diffInDays(
                        Carbon::parse(
                            $jakarta->estimasi_persiapan
                        )
                    );
            }

            // ==================================================
            // PENGIRIMAN
            // ==================================================
            $pengiriman =
                $jasaKurir
                ?: (
                    $jakarta->ekspedisi
                    ?? (
                        $statusKirim === 'Diambil'
                            ? 'Diambil'
                            : '-'
                    )
                );

            // ==================================================
            // SERVICE PENGIRIMAN
            // ==================================================
            $servicePengiriman =
                $serviceKurir
                ?: (
                    in_array(
                        strtolower(
                            $statusKirim ?? ''
                        ),
                        [
                            'diambil',
                            'ambil'
                        ]
                    )
                        ? 'Diambil'
                        : null
                );

            // ==================================================
            // CREATE REALISASI AKTIF
            // ==================================================
            // Ambil semua product_id yang valid
$productIds = $items
    ->pluck('product_id')
    ->filter()
    ->unique()
    ->values()
    ->toArray();

// Simpan sebagai JSON string
$productIdsJson = !empty($productIds) ? json_encode($productIds) : null;

$realisasi = RealisasiAktif::create([
    'jakarta_aktif_id'   => $jakarta->id,
    'no_pl'              => $jakarta->id_pesan,
    'tgl_turun_pl'       => $jakarta->tgl_pesan,
    'nama_unit'          => $namaUnit,
    'pengiriman'         => $pengiriman,
    'service_pengiriman' => $servicePengiriman,
    'nama_barang'        => $namaBarang,
    'kategori_order'     => $kategoriOrder,
    'product_id'         => $productIds[0] ?? null,     // tetap simpan yang pertama
    'product_ids'        => $productIdsJson,            // ← SEMUA ID
    'tgl_bayar'          => $jakarta->payment_date,
    'jumlah_bayar'       => $jakarta->total ?? 0,
    'nama_stokis'        => $namaStokis,
    'tgl_estimasi'       => $jakarta->estimasi_persiapan,
    'estimasi_hari'      => $estimasiHari,
    'penyebut'           => $namaUnit,
    'pengambil'          => $statusKirim === 'Diambil' ? 'Ambil Sendiri' : null,
    'ket'                => $jakarta->catatan,
    'order_weight'       => $jakarta->berat ?? 0,
    'billing_last_name'  => $jakarta->billing_last_name,
    'billing_company'    => $jakarta->billing_company,
]);

            // ==================================================
            // CREATE PICKING
            // Tetap menggunakan ITEMS asli
            // ==================================================
            $this->createPicking(
                $realisasi,
                $items
            );
        }

        // ======================================================
        // HITUNG ORDER BERHASIL DIPROSES
        // ======================================================
        $successCount++;
    }

    // ======================================================
    // REDIRECT
    // ======================================================
    return redirect()
        ->route($route)
        ->with(
            'success',
            "$successCount data berhasil diproses!"
        );
}

/**
 * Ambil items yang sudah difilter sesuai kategori/route
 */
private function getFilteredItems(JakartaAktif $jakarta, string $route)
{
    $items = $jakarta->items()->with('product')->get();

    return match ($route) {

        // ===========================================
        // MODUL
        // Semua selain Majalah & Sertifikat
        // ===========================================
        'order.modul' => $items->filter(function ($item) {

            $kategori = strtolower(trim($item->product->kategori ?? ''));

            return !str_contains($kategori, 'majalah')
                && !str_contains($kategori, 'sertifikat');

        }),

        // ===========================================
        // MAJALAH
        // ===========================================
        'order.majalah' => $items->filter(function ($item) {

            $kategori = strtolower(trim($item->product->kategori ?? ''));
            $nama     = strtoupper($item->nama_produk ?? '');
            $label    = strtoupper($item->label ?? '');

            return str_contains($kategori, 'majalah')
                || str_contains($nama, 'MAJALAH')
                || str_contains($nama, 'M159')
                || str_contains($nama, 'M160')
                || str_contains($label, 'M159')
                || str_contains($label, 'M160');

        }),

        // ===========================================
        // SERTIFIKAT
        // ===========================================
        'order.sertifikat' => $items->filter(function ($item) {

            $kategori = strtolower(trim($item->product->kategori ?? ''));
            $nama     = strtoupper($item->nama_produk ?? '');
            $label    = strtoupper($item->label ?? '');

            return str_contains($kategori, 'sertifikat')
                || str_contains($nama, 'SERTIFIKAT')
                || str_contains($nama, 'STA')
                || str_contains($nama, 'STPB')
                || str_contains($label, 'STA')
                || str_contains($label, 'STPB');

        }),

        default => $items,
    };
}

/**
 * Cek apakah realisasi untuk kategori ini sudah pernah dibuat
 */
private function hasRealisasiForCategory(int $jakartaAktifId, string $route): bool
{
    $kategori = match ($route) {
        'order.modul'       => 'Modul',
        'order.majalah'     => 'Majalah',
        'order.sertifikat'  => 'Sertifikat',
        default             => null,
    };

    if (!$kategori) return false;

    return RealisasiAktif::where('jakarta_aktif_id', $jakartaAktifId)
        ->where('kategori_order', $kategori)
        ->exists();
}

private function createPicking(RealisasiAktif $realisasi, $items = null)
{
    $jakarta = JakartaAktif::find($realisasi->jakarta_aktif_id);

    if (!$jakarta) {
        return null;
    }

    if ($items === null || $items->isEmpty()) {
        $items = $jakarta->items()->with('product')->get();
    }

    // Hindari item SKU yang sama
    $items = $items->unique('sku')->values();

    // Update jika sudah ada, buat jika belum
    $picking = Picking::updateOrCreate(
        [
            'realisasi_aktif_id' => $realisasi->id,
        ],
        [
            'jakarta_aktif_id'         => $realisasi->jakarta_aktif_id,
            'no_pl'                    => $realisasi->no_pl,
            'kategori_order'           => $realisasi->kategori_order,
            'tgl_order'                => $realisasi->tgl_turun_pl,
            'tgl_picking'              => now()->toDateString(),
            'payment_date'             => $realisasi->tgl_bayar,
            'waktu_estimasi_persiapan' => $jakarta->estimasi_persiapan
                ? Carbon::parse($jakarta->estimasi_persiapan)->toDateString()
                : now()->toDateString(),
            'jam_picking'        => now()->format('H:i:s'),
            'id_pesan'           => $realisasi->no_pl,
            'vendor'             => $realisasi->nama_stokis,
            'nama_unit'          => $realisasi->nama_unit,
            'billing_last_name'  => $realisasi->billing_last_name,
            'billing_company'    => $realisasi->billing_company,
            'kirim'              => $jakarta->kirim,
            'no_telpon'          => $jakarta->no_telpon,
            'alamat_kirim'       => $jakarta->alamat_kirim,
            'kab_kota_provinsi'  => $jakarta->kab_kota_provinsi,
            'ekspedisi'          => $realisasi->pengiriman,
            'service_pengiriman' => $realisasi->service_pengiriman,
            'pesanan'            => $realisasi->nama_barang,
            'total'              => $realisasi->jumlah_bayar,
            'berat'              => $realisasi->order_weight,
            'total_item'         => $items->count(),
            'total_qty'          => $items->sum('qty'),
            'status'             => 'completed',
            'printed_at'         => now(),
            'created_by'         => Auth::id(),
            'catatan'            => 'Auto Generate dari Realisasi',
        ]
    );

    // Hapus item lama agar tidak dobel
    $picking->pickingItems()->delete();

    foreach ($items as $item) {

        PickingItem::create([
    'picking_id' => $picking->id,

    'product_id' => $item->product_id,

    'item_name' => $item->product?->nama_produk
        ?? $item->nama_produk
        ?? $item->label
        ?? '-',

    'item_sku' => $item->sku,
    'item_qty' => (int) $item->qty,

    'qty_picked' => 0,
    'cek' => false,
]);
    }

    return $picking;
}
// ====================== MENU PRINT (Sudah Diproses) ======================
public function jakartaPrinted(Request $request)
{
    $query = RealisasiAktif::query();

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    if ($request->filled('id_pesan')) {
        $query->where('no_pl', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_turun_pl', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_turun_pl', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 30);

    $data = (clone $query)
    ->with([
        'picking',
        'product',
    ])
    ->orderBy('tgl_turun_pl')
    ->orderBy('created_at')
    ->paginate($perPage)
    ->appends($request->query());

$allData = (clone $query)
    ->with([
        'picking',
        'product',
    ])
    ->orderBy('tgl_turun_pl')
    ->orderBy('created_at')
    ->get();

    // =====================================================
    // ASSIGN REKAP NUMBER BERDASARKAN SELURUH DATA
    // =====================================================
    if ($allData->isNotEmpty()) {

        foreach ($allData->groupBy(function ($item) {
            return Carbon::parse($item->tgl_turun_pl)->toDateString();
        }) as $tanggal => $rows) {

            $rekapNumber = $this->generateRekapNumber($tanggal);

            RealisasiAktif::whereIn('id', $rows->pluck('id'))
                ->whereNull('rekap_number')
                ->update([
                    'rekap_number' => $rekapNumber,
                    'updated_at'   => now(),
                ]);

            foreach ($rows as $row) {
                $row->rekap_number = $rekapNumber;
            }
        }
    }

    // =====================================================
    // GROUP BERDASARKAN SELURUH DATA
    // =====================================================
    $groupedData = $allData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    return view('order.jakarta-printed', [
        'data'        => $data,
        'groupedData' => $groupedData,
    ]);
}


/**
 * Hapus data dari Realisasi Aktif (Jakarta Printed)
 */
public function deleteRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);   // ← Ganti ke RealisasiAktif

    $item->delete();

    return redirect()->route('order.jakarta-printed')
                     ->with('success', '✅ Data berhasil dihapus dari Realisasi Aktif!');
}


public function getModalData(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    $data = JakartaAktif::whereIn('id', $ids)
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
public function printRealisasiPdf(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data beserta relasi
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with(['jakartaAktif'])
        ->get();

    // Hanya yang Picking sudah selesai
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang siap dicetak (Picking List belum selesai).');
    }

    // Tandai sudah print
    if ($request->boolean('mark_printed')) {
        RealisasiAktif::whereIn('id', $filteredData->pluck('id'))
            ->whereNull('printed_at')
            ->update([
                'printed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    // Refresh data
    $filteredData = RealisasiAktif::whereIn('id', $filteredData->pluck('id'))
        ->with(['jakartaAktif'])
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Urutkan data
    |--------------------------------------------------------------------------
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // tanggal
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // nomor order
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    // Nomor dokumen
    $firstDate = optional($filteredData->first())->tgl_turun_pl;

    $docNumber = $this->generateRekapNumber($firstDate);

    // Generate PDF
    $pdf = PDF::loadView('order.jakarta-printed-pdf', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'            => 'sans-serif',
        'isHtml5ParserEnabled'   => true,
        'isRemoteEnabled'        => true,
    ]);

    return $pdf->stream(
        'RA-Pricing-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}


private function generateRekapNumber($tanggal = null)
{
    if (!$tanggal) {
        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
    }

    $tanggal = Carbon::parse($tanggal)->toDateString();

    // Cek apakah sudah ada rekap_number untuk tanggal tersebut
    $existing = RealisasiAktif::whereDate('tgl_turun_pl', $tanggal)
        ->whereNotNull('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing;
    }

    // Ambil nomor tertinggi yang pernah ada
    $lastNumber = RealisasiAktif::whereNotNull('rekap_number')
        ->max(DB::raw("CAST(REPLACE(rekap_number, '#', '') AS UNSIGNED)"));

    $next = ($lastNumber ?? 0) + 1;

    return '#' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

/**
 * Update rekap_number secara per tanggal
 */
public function assignRekapNumber($ids)
{
    if (empty($ids)) {
        return 0;
    }

    $data = RealisasiAktif::whereIn('id', $ids)
        ->whereNull('rekap_number')
        ->orderBy('tgl_turun_pl')
        ->get();

    $updatedCount = 0;

    foreach ($data->groupBy(fn($row) => Carbon::parse($row->tgl_turun_pl)->toDateString()) as $tanggal => $rows) {
        
        $rekapNumber = $this->generateRekapNumber($tanggal);

        $affected = RealisasiAktif::whereIn('id', $rows->pluck('id'))
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $rekapNumber,
                'updated_at'   => now(),
            ]);

        $updatedCount += $affected;
    }

    return $updatedCount;
}

// Contoh di method bulk atau print rekap
public function assignRekapBulk(Request $request)
{
    $ids = $request->input('ids'); // array of realisasi ids

    $updated = $this->assignRekapNumber($ids);

    return redirect()->back()
        ->with('success', "$updated rekap number berhasil ditambahkan.");
}

public function printSingleRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);

    if (!$item->printed_at) {
        $item->update(['printed_at' => now()]);
    }

    $data = collect([$item]); // agar view PDF tetap bisa pakai @foreach

    $pdf = PDF::loadView('order.jakarta-printed-pdf', compact('data'))
               ->setPaper('A5', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
                   'isRemoteEnabled' => true,
               ]);

    return $pdf->stream('Realisasi-' . $item->no_pl . '.pdf');
}

// === TAMBAHKAN METHOD INI DI DALAM CLASS OrderController ===
public function getFilteredIds(Request $request)
{
    $query = JakartaAktif::query();

    // =====================================================
    // FILTER BERDASARKAN MENU
    // =====================================================
    switch ($request->route) {

        // ================= MODUL =================
        case 'order.modul':

            $query->whereHas('items', function ($q) {

                $q->where(function ($x) {

                    // Jika product sudah terhubung
                    $x->whereHas('product', function ($p) {
                        $p->where('kategori', 'Modul');
                    });

                    // Fallback jika product_id masih kosong
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
    // FILTER TANGGAL
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

    $ids = $query
        ->where('is_processed', 0)
        ->pluck('id');

    return response()->json([
        'ids'   => $ids->values(),
        'count' => $ids->count(),
    ]);
}
// Tambahkan method ini
public function markPickingPrinted($id)
{
    $item = RealisasiAktif::findOrFail($id);

    $item->update([
        'picking_printed_at' => now()
    ]);

    return response()->json([
        'success' => true
    ]);
}

public function exportJakartaAktif(Request $request)
{
    $filename = 'Jakarta_Aktif_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(
        new \App\Exports\JakartaAktifExport($request), 
        $filename
    );
}
/**
 * Print Picking List - Multi Item dari BimbashopOrder
 */
/**
 * Print Picking List - Menggunakan PickingItem (Sudah difilter per kategori)
 */
public function printPickingList($id)
{
    $main = RealisasiAktif::with([
        'picking',
        'picking.pickingItems.product',
        'jakartaAktif'                      // ← relasi ditambahkan
    ])->findOrFail($id);

    if (!$main->picking_printed_at) {
        $main->update([
            'picking_printed_at' => now()
        ]);
    }

    if (!$main->picking) {
        return back()->with('error', 'Picking belum dibuat.');
    }

    $items = $main->picking
        ->pickingItems
        ->sortBy('item_sku')
        ->values();

    return view('order.picking-list', [
        'item'              => $main,
        'picking'           => $main->picking,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_aktif'     => $main->jakartaAktif,   // ← dikirim ke view
    ]);
}

/**
 * Print Picking List PDF - Menggunakan PickingItem
 */
public function printPickingListPdf($id)
{
    $main = RealisasiAktif::with([
        'picking.pickingItems',
        'jakartaAktif'                          // ← relasi ditambahkan
    ])->findOrFail($id);

    // Tandai sudah dicetak
    if (!$main->picking_printed_at) {
        $main->update(['picking_printed_at' => now()]);
    }

    $picking = $main->picking;

    if (!$picking) {
        abort(404, 'Picking data not found');
    }

    $items = $picking->pickingItems()
                ->orderBy('item_sku')
                ->get()
                ->transform(function ($item) {
                    // Rapikan nama produk
                    $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name));
                    return $item;
                });

    $pdf = Pdf::loadView('order.picking-list-pdf', [
        'item'              => $main,
        'picking'           => $picking,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_aktif'     => $main->jakartaAktif,   // ← dikirim ke view PDF
    ]);

    $pdf->setPaper('A5', 'portrait');
    
    $pdf->setOptions([
        'margin-top'           => 8,
        'margin-right'         => 6,
        'margin-bottom'        => 18,
        'margin-left'          => 6,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled'         => true,
    ]);

    $filename = 'Picking_List_' . ($main->no_pl ?? 'unknown') . 
                '_' . ($main->kategori_order ?? '') . 
                '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->stream($filename);
}

/**
 * Print QC - Hanya data yang picking list sudah selesai
 */
public function printQC(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk QC.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order.print-qc', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'QC-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Pemesanan (RA Picking)
 */
public function printPemesanan(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking selesai
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah kategori / item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            // Hitung jumlah item dari nama_barang
            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    // Group per tanggal
    $groupedData = $filteredData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order.print-pemesanan', [
        'data'        => $filteredData,
        'groupedData' => $groupedData,
        'docNumber'   => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'           => 'sans-serif',
        'isHtml5ParserEnabled'  => true,
        'isRemoteEnabled'       => true,
    ]);

    return $pdf->stream(
        'RA-Pemesanan-Picking-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Packing - Hanya data yang picking list sudah selesai
 */
public function printPacking(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Packing.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order.print-packing', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'Packing-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Distribusi / Ekspedisi - Hanya data yang picking list sudah selesai
 */
public function printEkspedisi(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Distribusi.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order.print-ekspedisi', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'Ekspedisi-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}


public function modul(Request $request)
{
    $query = JakartaAktif::query()
        ->with([
            'casdana',
            'items.product'
        ]);

    // ==========================
    // HANYA ORDER YANG MEMILIKI ITEM MODUL
    // ==========================
    $query->whereHas('items', function ($q) {

        $q->where(function ($x) {

            // jika product sudah terhubung
            $x->whereHas('product', function ($p) {
                $p->where('kategori', 'Modul');
            });

            // fallback jika product_id masih null
            $x->orWhere('nama_produk', 'like', '%Modul%');
            $x->orWhere('label', 'like', '%Modul%');
            $x->orWhere('sku', 'like', '%MOD%');
        });

    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', "%{$request->id_pesan}%");
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', "%{$request->kirim}%");
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', "%{$request->nama_unit}%");
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

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}

public function majalah(Request $request)
{
    $query = JakartaAktif::query()
        ->with([
            'casdana',
            'items.product'
        ]);

    // ==========================
    // HANYA MAJALAH
    // ==========================
    $query->whereHas('items', function ($q) {

        $q->where(function ($x) {

            // Jika product sudah terhubung
            $x->whereHas('product', function ($p) {

                $p->where('kategori', 'Majalah');

            });

            // Fallback
            $x->orWhere('nama_produk', 'like', '%Majalah%')
              ->orWhere('nama_produk', 'like', '%M159%')
              ->orWhere('label', 'like', '%M159%')
              ->orWhere('sku', 'like', '%M159%');

        });

    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', "%{$request->id_pesan}%");
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', "%{$request->kirim}%");
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', "%{$request->nama_unit}%");
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

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
}


public function sertifikat(Request $request)
{
    $query = JakartaAktif::query()
        ->with([
            'casdana',
            'items.product'
        ]);

    // ==========================
    // HANYA SERTIFIKAT
    // ==========================
    $query->whereHas('items', function ($q) {

        $q->where(function ($x) {

            // Jika product sudah terhubung
            $x->whereHas('product', function ($p) {
                $p->where('kategori', 'Sertifikat');
            });

            // Fallback jika product_id masih kosong
            $x->orWhere('nama_produk', 'like', '%Sertifikat%')
              ->orWhere('nama_produk', 'like', '%STA%')
              ->orWhere('nama_produk', 'like', '%STPB%')
              ->orWhere('label', 'like', '%STA%')
              ->orWhere('label', 'like', '%STPB%')
              ->orWhere('sku', 'like', '%STA%')
              ->orWhere('sku', 'like', '%STPB%');

        });

    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('id_pesan')) {
        $query->where('id_pesan', 'like', "%{$request->id_pesan}%");
    }

    if ($request->filled('kirim')) {
        $query->where('kirim', 'like', "%{$request->kirim}%");
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', "%{$request->nama_unit}%");
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

    $perPage = $request->get('per_page', 20);

    $data = $query
        ->latest('tgl_pesan')
        ->paginate($perPage)
        ->appends($request->query());

    return view('order.jakarta-aktif-index', compact('data'));
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

        $unit = UnitKemitraan::where('no_cab', $billingLastName)->first();

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

}