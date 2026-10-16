<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\Order\Models\RealisasiAktif;
use App\Modules\Picking\Models\Picking;          // Tambahkan
use App\Modules\Order\Models\JakartaAktifItem;
use App\Modules\Master\Models\Product;
use App\Modules\Majalah\Models\PesananMajalah;
use App\Modules\Order\Imports\JakartaAktifImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Modules\Order\Http\Controllers\Concerns\OrderHelpers;
use App\Modules\Order\Http\Controllers\Concerns\AktifPickingHelpers;

class JakartaAktifController extends Controller
{
    use OrderHelpers;
    use AktifPickingHelpers;


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
    $mismatchNoCab = \App\Modules\Master\Models\UnitNamaMismatch::where('is_resolved', false)
        ->pluck('no_cab')
        ->map(fn ($v) => trim((string) $v))
        ->filter()
        ->unique()
        ->flip()
        ->all();

    return view('order::order.jakarta-aktif-index', compact(
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
    $periodesPuw1 = \App\Modules\Majalah\Models\PesananMajalahPuw1::with('units')->get();

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

        // ====================== EDIT JAKARTA AKTIF ======================
    public function editJakartaAktif($id)
{
    $item = JakartaAktif::findOrFail($id);
    return view('order::order.jakarta-aktif-edit', compact('item'));
}

public function updateJakartaAktif(Request $request, $id)
{
    $item = JakartaAktif::findOrFail($id);

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
        'alamat_pengiriman'  => $validated['alamat_pengiriman'] ?? $item->alamat_pengiriman,
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

public function exportJakartaAktif(Request $request)
{
    $filename = 'Jakarta_Aktif_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(
        new \App\Modules\Order\Exports\JakartaAktifExport($request), 
        $filename
    );
}
}
