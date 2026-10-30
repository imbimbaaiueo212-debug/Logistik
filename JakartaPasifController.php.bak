<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\Picking\Models\Picking;          // Tambahkan
use App\Modules\Order\Models\JakartaPasif;
use App\Modules\Order\Models\JakartaPasifItem;
use App\Modules\Master\Models\Product;
use App\Modules\Order\Models\RealisasiPasif;
use App\Modules\Order\Support\RumahJakarta;
use App\Modules\Picking\Models\PickingPasif;
use App\Modules\Picking\Models\PickingPasifItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // Tambahkan
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Modules\Order\Http\Controllers\Concerns\OrderHelpers;

class JakartaPasifController extends Controller
{
    use OrderHelpers;



// ====================== JAKARTA PASIF ======================
/** Jenis (rumah) order Pasif */
public const JENIS_PASIF = ['JKTP', 'DLC', 'EBT', 'INV', 'BSS'];

/** Tentukan jenis dari SKU (JKTP / DLC / EBT / INV / BSS), null jika bukan salah satunya. */
private function jenisDariSku($sku): ?string
{
    $sku = strtoupper(trim((string) $sku));

    foreach (self::JENIS_PASIF as $jenis) {
        if (preg_match('/(^|[^A-Z])' . $jenis . '([^A-Z]|$)/', $sku)) {
            return $jenis;
        }
    }

    return null;
}

/**
 * Nama unit untuk order Pasif.
 * DLC  : Nama Unit = Billing Name (billing_first_name + billing_last_name di biMBA Shop).
 * Lain : aturan lama (resolveNamaUnit).
 */
private function namaUnitPasif($jenis, $idPesan, $billingCompany, $billingLastName, $default = null, $billingFirstName = null)
{
    if (strtoupper(trim((string) $jenis)) === 'DLC') {
        if ($billingFirstName === null) {
            $billingFirstName = BimbashopOrder::where('order_id', $idPesan)
                ->whereNotNull('billing_first_name')
                ->value('billing_first_name');
        }

        $billingName = trim(trim((string) $billingFirstName) . ' ' . trim((string) $billingLastName));

        if ($billingName !== '') {
            return $billingName;
        }
    }

    return $this->resolveNamaUnit($billingCompany, $billingLastName, $default);
}

public function jakartaPasif(Request $request)
{
    $query = JakartaPasif::query();

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
    if ($request->filled('jenis') && in_array($request->jenis, self::JENIS_PASIF, true)) {
        $query->where('jenis', $request->jenis);
    }

    $perPage = $request->get('per_page', 50);
    $perPage = in_array($perPage, [5, 10, 20, 50, 100, 200, 500]) ? $perPage : 50;

    $data = $query
        ->with(['casdana' => function ($q) {
            $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer');
        }])
        ->orderBy('tgl_pesan', 'asc')
        ->paginate($perPage)
        ->appends($request->query());

    $listStatusBayar = JakartaPasif::select('status_pembayaran')
        ->whereNotNull('status_pembayaran')
        ->where('status_pembayaran', '!=', '')
        ->distinct()
        ->orderBy('status_pembayaran')
        ->pluck('status_pembayaran');

    $listPesanan = JakartaPasif::select('pesanan')
        ->whereNotNull('pesanan')
        ->where('pesanan', '!=', '')
        ->distinct()
        ->orderBy('pesanan')
        ->pluck('pesanan');

    $listNamaUnit = JakartaPasif::select('nama_unit')
        ->whereNotNull('nama_unit')
        ->where('nama_unit', '!=', '')
        ->distinct()
        ->orderBy('nama_unit')
        ->pluck('nama_unit');

    $listGrup = JakartaPasif::select('grup')
        ->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    $unitTidakPesan = [];
    $mismatchNoCab = [];

    // Sementara pakai view yang sama, nanti bisa diganti
    return view('order::order.jakarta-pasif-index', compact(
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
 * Sync dari Bimbashop 
 * JKTP -> jakarta_pasif | DLC -> jakarta_dlc | EBT -> jakarta_ebt | INV -> jakarta_inv | BSS -> jakarta_bss
 * (tiap rumah punya tabel sendiri; ditampilkan bergabung di halaman Jakarta)
 * Catatan: Pasif tidak wajib ada pembayaran di Casdana
 */
public function syncJktPasifFromBimbashop()
{
    $totalOrder  = 0;
    $skipExists  = 0;
    $inserted    = 0;
    $perRumah    = array_fill_keys(self::JENIS_PASIF, 0);
    $errors      = [];

    // =====================================================
    // AMBIL ORDER YANG SKU-NYA JKTP / DLC / EBT / INV / BSS
    // =====================================================
    $bimbashopOrders = BimbashopOrder::where(function ($q) {
            foreach (self::JENIS_PASIF as $j) {
                $q->orWhere('item_sku', 'like', "%{$j}%");
            }
        })
        ->get()
        ->filter(fn ($o) => $this->jenisDariSku($o->item_sku) !== null)
        ->groupBy('order_id');

    $totalOrder = $bimbashopOrders->count();

    Log::info("JKTP Sync dimulai. Total order ditemukan: {$totalOrder}");

    foreach ($bimbashopOrders as $orderId => $items) {

        // =================================================
        // CEK SUDAH ADA
        // =================================================
        // Tiap rumah punya tabel sendiri: JKTP -> jakarta_pasif, DLC/EBT/INV/BSS -> jakarta_{rumah}
        $jenisOrder     = $items->map(fn ($i) => $this->jenisDariSku($i->item_sku))->filter()->first() ?: 'JKTP';
        $kelasHeader    = RumahJakarta::header($jenisOrder);
        $kelasItem      = RumahJakarta::item($jenisOrder);
        $kelasRealisasi = RumahJakarta::realisasi($jenisOrder);
        $fk             = RumahJakarta::fk($jenisOrder);

        $existing = $kelasHeader::where('id_pesan', $orderId)->first();

        // Order lama yang sudah diproses masih tersimpan di jakarta_pasif -> jangan dibuat ulang
        if (! $existing
            && RumahJakarta::punyaTabelSendiri($jenisOrder)
            && JakartaPasif::where('id_pesan', $orderId)->exists()
        ) {
            $skipExists++;
            continue;
        }

        if ($existing) {
            // Koreksi nama unit (mis. pilih unit tanpa "English" bila no_cab punya 2 nama)
            $src  = $items->first();

            if (! $existing->jenis) {
                $existing->jenis = $items->map(fn ($i) => $this->jenisDariSku($i->item_sku))->filter()->first();
                $existing->save();
            }

            $nama = $this->namaUnitPasif($existing->jenis, $orderId, $src->billing_company, $src->billing_last_name, $existing->nama_unit, $src->billing_first_name);

            if ($nama !== '' && $nama !== '-' && $existing->nama_unit !== $nama) {
                $existing->nama_unit = $nama;
                $existing->save();

                $kelasRealisasi::where($fk, $existing->id)
                    ->update(['nama_unit' => $nama, 'updated_at' => now()]);
            }

            $skipExists++;
            continue;
        }

        $firstItem = $items->first();

        // =================================================
        // CASDANA (OPSIONAL)
        // =================================================
        $casdana = CasdanaTransaction::where(function ($q) use ($orderId) {
            $q->where('invoice_number', 'like', "%{$orderId}%")
              ->orWhere('invoice_number', $orderId);
        })
        ->latest('id')
        ->first();

        $statusCasdana = $casdana
            ? strtoupper(trim($casdana->status ?? ''))
            : 'MANUAL';   // default kalau tidak ada Casdana

        $paymentDate = $casdana->payment_date ?? $firstItem->order_date ?? now();
        $amount      = $casdana->amount ?? $firstItem->order_total ?? 0;

        // =================================================
        // ESTIMASI WAKTU
        // =================================================
        $estimasiPrintPl   = null;
        $estimasiPersiapan = null;

        if ($paymentDate) {
            $payment = Carbon::parse($paymentDate);

            $estimasiPrintPl = $payment->hour < 12
                ? $payment->copy()
                : $payment->copy()->addDay();

            while ($estimasiPrintPl->isSunday() || $this->isHoliday($estimasiPrintPl)) {
                $estimasiPrintPl->addDay();
            }

            $estimasiPersiapan = $this->addBusinessDays($estimasiPrintPl, 2);
        }

        // =================================================
        // PRODUCT CACHE & KATEGORI
        // =================================================
        $productCache = [];
        $kategoriList = [];

        foreach ($items as $item) {
            $sku = strtoupper(trim($item->item_sku ?? ''));
            if (empty($sku)) continue;

            $searchCode = trim(explode('-', $sku)[0]);

            if (!array_key_exists($searchCode, $productCache)) {
                $productCache[$searchCode] = $this->findProductBySku($sku, $item->item_name ?? '');
            }

            $product = $productCache[$searchCode];

            if ($product) {
                $kategori = trim((string) ($product->kategori ?? ''));
            } else {
                $itemName = trim($item->item_name ?? '');
                $kategori = str_ireplace(['JKTP', 'JKT', 'biMBA', 'Unit', 'Reguler'], '', $itemName);
                $kategori = preg_replace('/\s+/', ' ', $kategori);
                $kategori = trim($kategori);

                if (empty($kategori)) {
                    $kategori = trim(preg_replace('/\s+/', ' ', str_ireplace(['JKTP', '-JKTP', 'JKT', '-JKT'], '', $sku)));
                }
            }

            $kategoriLower = strtolower(trim($kategori));

            if (
                $kategoriLower === 'modul bimba' ||
                $kategoriLower === 'modul bimba unit' ||
                str_contains($kategoriLower, 'modul')
            ) {
                $kategoriUmum = 'Modul biMBA';
            } elseif (str_contains($kategoriLower, 'majalah')) {
                $kategoriUmum = 'Majalah Sahabat biMBA';
            } elseif (str_contains($kategoriLower, 'sertifikat')) {
                $kategoriUmum = 'Sertifikat';
            } else {
                $kategoriUmum = $kategori;
            }

            if (!empty(trim($kategoriUmum))) {
                $kategoriList[] = trim($kategoriUmum);
            }
        }

        $kategoriList = collect($kategoriList)->filter()->unique()->values();

        $pesanan = $kategoriList->isEmpty()
            ? 'Media Pembelajaran biMBA AIUEO'
            : $kategoriList->implode(' | ');

        // =================================================
        // NAMA UNIT & ALAMAT
        // =================================================
        $namaUnit = $this->namaUnitPasif(
            $items->map(fn ($i) => $this->jenisDariSku($i->item_sku))->filter()->first(),
            $orderId,
            $firstItem->billing_company,
            $firstItem->billing_last_name,
            $firstItem->item_name ?? ($casdana->customer ?? '-'),
            $firstItem->billing_first_name
        );

        $kirim = trim(implode(', ', array_filter([
            $firstItem->shipping_address_1,
            $firstItem->shipping_address_2,
            $firstItem->shipping_city
        ]))) ?: $namaUnit;

        $ongkir = (int) ($firstItem->ship_total ?? 0);
        $statusKirim = $ongkir > 0 ? 'Dikirim' : 'Diambil';

        // =================================================
        // DATA HEADER
        // =================================================
        $data = [
            'tgl_input'          => now()->format('Y-m-d'),
            'tgl_pesan'          => $firstItem->order_date,
            'kirim'              => $kirim,
            'no_telpon'          => $firstItem->shipping_phone ?? null,
            'alamat_kirim'       => $firstItem->shipping_address_1 ?? null,
            'kab_kota_provinsi'  => $firstItem->shipping_city ?? null,
            'ongkir'             => $ongkir,
            'nama_unit'          => $namaUnit,
            'pesanan'            => $pesanan,
            'harga'              => $items->sum(fn ($item) => ($item->item_price ?? 0) * ($item->item_qty ?? 1)),
            'berat'              => $firstItem->order_weight ?? 0,
            'item_qty'           => $items->sum('item_qty'),
            'total'              => $amount,
            'jenis_bank'         => $casdana->payment_channel ?? $firstItem->payment_method ?? null,
            'status_pembayaran'  => $statusCasdana,   // MANUAL jika tidak ada Casdana
            'status_pesan'       => $firstItem->status,
            'id_pesan'           => $orderId,
            'status'             => 'aktif',
            'jenis'              => $items->map(fn ($i) => $this->jenisDariSku($i->item_sku))->filter()->first(),
            'payment_date'       => $paymentDate,
            'amount'             => $amount,
            'billing_last_name'  => $firstItem->billing_last_name ?? null,
            'billing_company'    => $firstItem->billing_company ?? null,
            'status_kirim'       => $statusKirim,
            'estimasi_print_pl'  => $estimasiPrintPl,
            'estimasi_persiapan' => $estimasiPersiapan,
        ];

        // =================================================
        // CREATE
        // =================================================
        try {
            DB::transaction(function () use ($data, $items, $productCache, &$inserted, &$perRumah, $jenisOrder, $kelasHeader, $kelasItem, $fk) {

                $jakarta = $kelasHeader::create($data);

                foreach ($items as $item) {
                    $sku = strtoupper(trim($item->item_sku ?? ''));
                    if (empty($sku)) continue;

                    $searchCode = trim(explode('-', $sku)[0]);
                    $product    = $productCache[$searchCode] ?? null;

                    $qty   = (int) ($item->item_qty ?? 1);
                    $harga = (float) ($item->item_price ?? 0);

                    $kelasItem::create([
                        $fk                => $jakarta->id,
                        'product_id'       => $product?->id,
                        'sku'              => $sku,
                        'label'            => $product?->label ?? $searchCode,
                        'nama_produk'      => $product?->name ?? $item->item_name,
                        'qty'              => $qty,
                        'harga'            => $harga,
                        'subtotal'         => $qty * $harga,
                    ]);
                }

                $inserted++;
                $perRumah[$jenisOrder] = ($perRumah[$jenisOrder] ?? 0) + 1;
            });

        } catch (\Throwable $e) {
            $errors[] = "Order {$orderId}: " . $e->getMessage();
            Log::error("JKTP create gagal | order_id={$orderId} | " . $e->getMessage());
        }
    }

    // =====================================================
    // HASIL
    // =====================================================
    $rincian = collect($perRumah)
        ->map(fn ($n, $j) => "{$j}: <strong>{$n}</strong>")
        ->implode(' &nbsp;|&nbsp; ');

    $message = "✅ Sync JKTP / DLC / EBT / INV / BSS selesai.<br>"
             . "Total order ditemukan: <strong>{$totalOrder}</strong><br>"
             . "Berhasil masuk: <strong>{$inserted}</strong> ({$rincian})<br>"
             . "Dilewati (sudah ada): <strong>{$skipExists}</strong>";

    if (count($errors) > 0) {
        $message .= "<br><br>❌ Error (" . count($errors) . "):<br>"
                 . "• " . implode('<br>• ', array_slice($errors, 0, 5));

        if (count($errors) > 5) {
            $message .= "<br>• ... dan " . (count($errors) - 5) . " error lainnya";
        }
    }

    Log::info("JKTP Sync selesai. Inserted={$inserted}, SkipExists={$skipExists}");

    return redirect()
        ->route('order.jakarta-pasif')
        ->with('success', $message);
}

// ====================== JAKARTA PASIF – BULK, MODAL, FILTERED IDS ======================

public function bulkActionJakartaPasif(Request $request)
{
    $action  = $request->input('action');
    $perItem = $request->input('per_item');

    if ($action !== 'processed' || empty($perItem)) {
        return redirect()->back()->with('error', 'Data tidak valid.');
    }

    // Wajib pilih jenis: masing-masing jenis punya Rekap Aktual sendiri
    $jenisProses = strtoupper(trim((string) $request->input('jenis')));

    if (! in_array($jenisProses, self::JENIS_PASIF, true)) {
        return redirect()->back()->with('error', 'Pilih filter Rumah (JKTP, DLC, EBT, INV, atau BSS) dulu sebelum memproses ke Rekap Aktual.');
    }

    // Tiap rumah punya tabel sendiri
    $kelasHeader    = RumahJakarta::header($jenisProses);
    $kelasRealisasi = RumahJakarta::realisasi($jenisProses);
    $fk             = RumahJakarta::fk($jenisProses);
    $tabelHeader    = RumahJakarta::tabel($jenisProses);
    $pakaiPicking   = ! RumahJakarta::punyaTabelSendiri($jenisProses); // picking rumah baru: tahap berikutnya

    $updates = json_decode($perItem, true);

    if (empty($updates)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $now   = Carbon::now('Asia/Jakarta');
    $route = $request->input('redirect', 'order.jakarta-pasif');
    $successCount = 0;

    foreach ($updates as $update) {
        $id = $update['id'] ?? null;
        if (!$id) continue;

        $jakarta = $kelasHeader::find($id);
        if (!$jakarta || $jakarta->is_processed) continue;

        // Hanya jenis yang dipilih yang masuk Rekap Aktual
        if (strtoupper((string) $jakarta->jenis) !== $jenisProses) continue;

        $statusKirim  = $update['status_kirim'] ?? $jakarta->status_kirim;
        $jasaKurir    = $update['jasa_kurir'] ?? $jakarta->ekspedisi;
        $serviceKurir = $update['service_kurir'] ?? $jakarta->service_pengiriman;
        $catatan      = $update['catatan'] ?? null;

        // --- Update header (jakarta_pasif / jakarta_dlc / jakarta_ebt / ...) ---
        $setClauses = [
            "is_processed = 1",
            "processed_at = ?",
            "updated_at = ?"
        ];
        $bindings = [$now, $now];

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
            $newNote = "\n\nDi proses bulk pada " . $now->format('d/m/Y H:i') . ": " . trim($catatan);
            $setClauses[] = "catatan = CONCAT(COALESCE(catatan, ''), ?)";
            $bindings[] = $newNote;
        }

        DB::update(
            "UPDATE {$tabelHeader} SET " . implode(', ', $setClauses) . " WHERE id = ?",
            array_merge($bindings, [$id])
        );

        // Refresh
        $jakarta->refresh();

        $allItems = $jakarta->items()->with('product')->get();
        if ($allItems->isEmpty()) {
            $successCount++;
            continue;
        }

        // Grouping kategori
        $groups = [
            'Modul'      => collect(),
            'Majalah'    => collect(),
            'Sertifikat' => collect(),
        ];

        foreach ($allItems as $item) {
            $kategori = trim($item->product?->kategori ?? '');
            $kategoriLower = strtolower($kategori);

            if (str_contains($kategoriLower, 'majalah')) {
                $groups['Majalah']->push($item);
            } elseif (str_contains($kategoriLower, 'sertifikat')) {
                $groups['Sertifikat']->push($item);
            } else {
                $groups['Modul']->push($item);
            }
        }

        foreach ($groups as $kategoriOrder => $items) {
            if ($items->isEmpty()) continue;

            // Skip jika realisasi kategori ini sudah ada
            if ($kelasRealisasi::where($fk, $jakarta->id)
                ->where('kategori_order', $kategoriOrder)
                ->exists()
            ) {
                continue;
            }

            $namaUnit = $this->namaUnitPasif(
                $jakarta->jenis,
                $jakarta->id_pesan,
                $jakarta->billing_company,
                $jakarta->billing_last_name,
                $jakarta->nama_unit
            );

            $skuList    = $items->pluck('sku')->filter()->implode('|');
            $namaStokis = $this->extractVendorFromSku($skuList);

            $namaBarang = $items
                ->groupBy(fn ($item) => trim($item->product?->kategori ?? $item->nama_produk ?? 'Lainnya'))
                ->map(function ($rows, $kategori) {
                    $qty = $rows->groupBy(fn ($i) => strtoupper(trim($i->sku ?? '')))
                        ->sum(fn ($skuRows) => (int) ($skuRows->first()->qty ?? 0));
                    return "{$kategori} ({$qty})";
                })
                ->values()
                ->implode(' | ');

            $productIds     = $items->pluck('product_id')->filter()->unique()->values()->toArray();
            $productIdsJson = !empty($productIds) ? json_encode($productIds) : null;

            $estimasiHari = null;
            if ($jakarta->payment_date && $jakarta->estimasi_persiapan) {
                $estimasiHari = Carbon::parse($jakarta->payment_date)
                    ->diffInDays(Carbon::parse($jakarta->estimasi_persiapan));
            }

            $pengiriman = $jasaKurir
                ?: ($jakarta->ekspedisi ?? ($statusKirim === 'Diambil' ? 'Diambil' : '-'));

            $servicePengiriman = $serviceKurir
                ?: (in_array(strtolower($statusKirim ?? ''), ['diambil', 'ambil']) ? 'Diambil' : null);

            $realisasi = $kelasRealisasi::create([
                $fk                  => $jakarta->id,
                'jenis'              => $jakarta->jenis ?: 'JKTP',
                'no_pl'              => $jakarta->id_pesan,
                'tgl_turun_pl'       => $jakarta->tgl_pesan,
                'nama_unit'          => $namaUnit,
                'pengiriman'         => $pengiriman,
                'service_pengiriman' => $servicePengiriman,
                'nama_barang'        => $namaBarang,
                'kategori_order'     => $kategoriOrder,
                'product_id'         => $productIds[0] ?? null,
                'product_ids'        => $productIdsJson,
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

            if ($pakaiPicking) {
                $this->createPickingPasif($realisasi, $items);
            }
        }

        $successCount++;
    }

    return redirect()
        ->route($route, ['jenis' => $jenisProses])
        ->with('success', "$successCount data {$jenisProses} berhasil diproses & disimpan ke Rekap Aktual {$jenisProses}!"
            . ($pakaiPicking ? '' : '<br><small>Catatan: Picking untuk ' . $jenisProses . ' belum tersambung (tahap berikutnya).</small>'));
}

/**
 * Create Picking Pasif dari RealisasiPasif (berdiri sendiri)
 */
private function createPickingPasif(RealisasiPasif $realisasi, $items = null)
{
    $jakarta = JakartaPasif::find($realisasi->jakarta_pasif_id);

    if (!$jakarta) {
        return null;
    }

    if ($items === null || $items->isEmpty()) {
        $items = $jakarta->items()->with('product')->get();
    }

    $items = $items->unique('sku')->values();

    $picking = PickingPasif::updateOrCreate(
    [
        'realisasi_pasif_id' => $realisasi->id,
    ],
    [
        'jakarta_pasif_id'         => $realisasi->jakarta_pasif_id,
        'no_pl'                    => $realisasi->no_pl,
        'id_pesan'                 => $realisasi->no_pl,
        'kategori_order'           => $realisasi->kategori_order,
        'tgl_order'                => $realisasi->tgl_turun_pl,
        'tgl_picking'              => now()->toDateString(),
        'payment_date'             => $realisasi->tgl_bayar,
        'waktu_estimasi_persiapan' => $jakarta->estimasi_persiapan
            ? Carbon::parse($jakarta->estimasi_persiapan)->toDateString()
            : now()->toDateString(),
        'jam_picking'        => now()->format('H:i:s'),
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
        'total'              => $realisasi->jumlah_bayar ?? 0,
        'berat'              => $realisasi->order_weight ?? 0,
        'total_item'         => $items->count(),
        'total_qty'          => $items->sum('qty'),
        'status'             => 'pending',          // ← ubah dari completed
        'printed_at'         => null,               // ← penting! biarkan null
        'created_by'         => Auth::id(),
        'catatan'            => 'Auto Generate dari Realisasi Pasif',
    ]
);

    // Hapus item lama lalu buat ulang
    $picking->items()->delete();

    foreach ($items as $item) {
        PickingPasifItem::create([
            'picking_pasif_id' => $picking->id,
            'product_id'       => $item->product_id,
            'item_name'        => $item->product?->name
                ?? $item->nama_produk
                ?? $item->label
                ?? '-',
            'item_sku'   => $item->sku,
            'item_qty'   => (int) $item->qty,
            'qty_picked' => 0,
            'cek'        => false,
        ]);
    }

    return $picking;
}

public function getModalDataPasif(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    $jenis = strtoupper(trim((string) $request->input('jenis')));
    $kelasHeader = in_array($jenis, self::JENIS_PASIF, true)
        ? RumahJakarta::header($jenis)
        : JakartaPasif::class;

    $data = $kelasHeader::whereIn('id', $ids)
        ->select([
            'id', 'id_pesan', 'nama_unit', 'status_pembayaran', 'jenis_bank',
            'pesanan', 'status_kirim', 'payment_date', 'is_processed',
            'processed_at', 'ekspedisi', 'service_pengiriman',
        ])
        ->get()
        ->map(function ($item) {
            $vendor = $this->extractVendorFromSku($item->pesanan ?? '');

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
                    ? Carbon::parse($item->payment_date)->format('d/m/Y H:i')
                    : '-',
                'payment_channel'   => $item->jenis_bank ?? '-',
                'status_pembayaran' => $item->status_pembayaran ?? '-',
                'status_kirim'      => $item->status_kirim ?? 'Dikirim',
                'vendor'            => $vendor,
                'jasa_kurir'        => $jasaKurir,
                'service_kurir'     => $service,
                'is_processed'      => (bool) $item->is_processed,
                'processed_at'      => $item->processed_at
                    ? Carbon::parse($item->processed_at)->format('d/m/Y H:i')
                    : null,
            ];
        });

    return response()->json($data);
}

public function getFilteredIdsPasif(Request $request)
{
    $jenisReq    = strtoupper(trim((string) $request->input('jenis')));
    $kelasHeader = in_array($jenisReq, self::JENIS_PASIF, true)
        ? RumahJakarta::header($jenisReq)
        : JakartaPasif::class;

    $query = $kelasHeader::query();

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
    if ($request->filled('jenis') && in_array($request->jenis, self::JENIS_PASIF, true)) {
        $query->where('jenis', $request->jenis);
    }

    $ids = $query
        ->where(function ($q) {
            $q->where('is_processed', 0)->orWhereNull('is_processed');
        })
        ->pluck('id');

    return response()->json([
        'ids'   => $ids->values(),
        'count' => $ids->count(),
    ]);
}
}