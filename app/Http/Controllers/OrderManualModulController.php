<?php

namespace App\Http\Controllers;

use App\Models\ManualModulOrder;
use App\Models\ManualRealisasi;
use App\Models\ManualPicking;
use App\Models\ManualPickingItem;
use App\Models\UnitNamaMismatch;
use App\Models\BimbashopOrder;
use App\Models\Product;
use App\Models\CasdanaTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderManualModulController extends Controller
{
    /** Hub: OPS2 / DLC / Pasif / Manual */
    public function index()
    {
        return view('order-manual.modul-index');
    }

    /** List Manual Pemesanan Modul */
    public function manual(Request $request)
    {
        $query = ManualModulOrder::query();

        if ($request->filled('order_id')) {
            $query->where('order_id', 'like', '%' . $request->order_id . '%');
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }
        if ($request->filled('product_sku')) {
            $query->where('product_sku', 'like', '%' . $request->product_sku . '%');
        }
        if ($request->filled('grup')) {
            $query->where('grup', $request->grup);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $perPage = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100, 200, 500])
            ? (int) $request->get('per_page', 25)
            : 25;

        $manualOrders = $query
            ->orderBy('order_date', 'asc')
            ->paginate($perPage)
            ->appends($request->query());

        $grups = ManualModulOrder::select('grup')
            ->whereNotNull('grup')
            ->where('grup', '!=', '')
            ->distinct()
            ->orderBy('grup')
            ->pluck('grup');

        $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
            ->get()
            ->keyBy(fn ($m) => trim((string) $m->no_cab))
            ->map(fn ($m) => [
                'nama_excel'  => $m->nama_excel,
                'nama_master' => $m->nama_master,
            ])
            ->all();

        return view('order-manual.modul.manual-index', compact(
            'manualOrders',
            'grups',
            'mismatchMap'
        ));
    }

    public function manualCreate()
    {
        $customers = \App\Models\UserExportBimbaShop::query()
            ->select([
                'ID',
                'user_login',
                'user_email',
                'display_name',
                'first_name',
                'last_name',
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_postcode',
                'billing_state',
                'billing_country',
                'billing_phone',
                'billing_email',
            ])
            ->orderBy('display_name')
            ->get();

        return view('order-manual.modul.manual-create', compact('customers'));
    }

    /**
     * Autocomplete produk untuk form create.
     * SKU yang dikembalikan = products.label (fallback sku, lalu kode).
     */
    public function searchProducts(Request $request)
{
    $q = trim((string) $request->get('q', ''));

    $query = Product::query()->select([
        'id',
        'sku',
        'label',
        'name',
        'kode',
        'jenis',
        'kategori',
        'berat_paket',
        'harga_jual',   // pastikan kolom ini ada
        // 'harga',     // fallback kalau nama kolom berbeda
    ]);

    if ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('label', 'like', "%{$q}%")
              ->orWhere('sku', 'like', "%{$q}%")
              ->orWhere('name', 'like', "%{$q}%")
              ->orWhere('kode', 'like', "%{$q}%");
        });
    }

    $products = $query->orderBy('name')->limit(40)->get();

    $results = $products->map(function (Product $p) {
        $sku = trim((string) ($p->label ?: $p->sku ?: $p->kode ?: ''));

        if ($sku === '') {
            return null;
        }

        return [
            'id'         => $sku,
            'text'       => $sku . ' — ' . ($p->name ?? ''),
            'product_id' => $p->id,
            'sku'        => $sku,
            'name'       => $p->name ?? '',
            'jenis'      => $p->jenis ?? '',
            'kategori'   => $p->kategori ?? '',
            'harga_jual' => (float) ($p->harga_jual ?? $p->harga ?? 0),
            'berat'      => (float) ($p->berat_paket ?? 0),
        ];
    })
    ->filter()
    ->values();

    return response()->json(['results' => $results]);
}

    public function manualStore(Request $request)
    {
        if ($request->filled('order_date_date')) {
            $time = $request->input('order_date_time') ?: '00:00';
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            $request->merge([
                'order_date' => $request->order_date_date . ' ' . $time,
            ]);
        }

        if (!$request->filled('customer_name')) {
            $request->merge([
                'customer_name' => trim(
                    $request->billing_company
                    ?: $request->billing_first_name
                    ?: ''
                ),
            ]);
        }

        $request->validate([
            'customer_name'         => 'required|string|max:150',
            'order_id'              => 'nullable|string|max:100',
            'order_date'            => 'nullable|date',
            'billing_last_name'     => 'nullable|string|max:50',
            'billing_first_name'    => 'nullable|string|max:100',
            'grup'                  => 'nullable|string|max:10',
            'status'                => 'nullable|string|max:50',
            'status_kirim'          => 'nullable|string|max:50',
            'ekspedisi'             => 'nullable|string|max:100',
            'service_pengiriman'    => 'nullable|string|max:50',
            'shipping_address_1'    => 'nullable|string',
            'shipping_address_2'    => 'nullable|string',
            'shipping_city'         => 'nullable|string|max:100',
            'phone'                 => 'nullable|string|max:30',
            'catatan'               => 'nullable|string',
            'order_weight'          => 'nullable|numeric',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'nullable|integer',
            'items.*.product_name'  => 'required|string|max:255',
            'items.*.product_sku'   => 'nullable|string|max:100',
            'items.*.qty'           => 'required|integer|min:1',
        ], [
            'customer_name.required'        => 'Customer / nama unit wajib diisi. Pilih dari dropdown Customer.',
            'items.required'                => 'Minimal harus ada 1 item/SKU.',
            'items.*.product_name.required' => 'Nama produk wajib diisi.',
            'items.*.qty.required'          => 'Qty wajib diisi.',
            'items.*.qty.min'               => 'Qty minimal 1.',
        ]);

        $items = collect($request->input('items', []))
            ->filter(fn ($item) => !empty(trim($item['product_name'] ?? '')) && (int) ($item['qty'] ?? 0) > 0)
            ->values();

        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'Minimal harus ada 1 item yang valid.');
        }

        $statusKirim = $request->status_kirim ?? 'Dikirim';
        $ekspedisi   = $request->ekspedisi;
        $service     = $request->service_pengiriman;

        if ($statusKirim === 'Diambil') {
            $ekspedisi = $ekspedisi ?: 'Diambil Sendiri';
            $service   = $service ?: null;
        }

        $created = 0;

        DB::beginTransaction();
        try {
            $todayPrefix = 'MM-' . date('Ymd') . '-';

            $lastManual = ManualModulOrder::where('manual_id', 'like', $todayPrefix . '%')
                ->lockForUpdate()
                ->orderByDesc('manual_id')
                ->value('manual_id');

            $nextNumber = 1;
            if ($lastManual) {
                $nextNumber = ((int) substr($lastManual, -4)) + 1;
            }

            $manualId = $todayPrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            foreach ($items as $item) {
    $product = $this->resolveProduct($item);

    $productSku  = $product
        ? trim((string) ($product->label ?: $product->sku ?: $product->kode))
        : trim((string) ($item['product_sku'] ?? ''));

    $productName = trim((string) ($item['product_name'] ?? ''))
        ?: ($product->name ?? '');

    if ($productName === '') {
        continue;
    }

    // Ambil harga jual dari form (sudah total = satuan × qty)
    $hargaJual = (float) ($item['harga_jual'] ?? 0);

    $row = [
        'manual_id'          => $manualId,
        'order_id'           => $request->order_id,
        'order_date'         => $request->order_date ?? now(),
        'customer_name'      => $request->customer_name,
        'billing_first_name' => $request->billing_first_name,
        'billing_last_name'  => $request->billing_last_name,
        'product_sku'        => $productSku ?: null,
        'product_name'       => $productName,
        'qty'                => (int) $item['qty'],
        'price'              => $hargaJual,          // ← simpan ke kolom price
        'grup'               => $request->grup,
        'payment_method'     => $request->payment_method ?? 'manual',
        'status'             => $request->status ?? 'pending',
        'status_kirim'       => $statusKirim,
        'ekspedisi'          => $ekspedisi,
        'service_pengiriman' => $service,
        'shipping_address_1' => $request->shipping_address_1,
        'shipping_address_2' => $request->shipping_address_2,
        'shipping_city'      => $request->shipping_city,
        'phone'              => $request->phone ?? $request->shipping_phone,
        'order_weight'       => $request->order_weight,
        'catatan'            => $request->catatan,
        'is_processed'       => false,
        'billing_kelurahan'  => $request->billing_kelurahan,
        'billing_kecamatan'  => $request->billing_kecamatan,
        'shipping_kelurahan' => $request->shipping_kelurahan,
        'shipping_kecamatan' => $request->shipping_kecamatan,
    ];

    // Opsional: kalau ada kolom total, isi juga
    if (Schema::hasColumn('manual_modul_orders', 'total')) {
        $row['total'] = $hargaJual;
    }

    if (Schema::hasColumn('manual_modul_orders', 'product_id') && $product) {
        $row['product_id'] = $product->id;
    }

    ManualModulOrder::create($row);
    $created++;
}

            if ($created === 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Tidak ada item valid yang bisa disimpan.');
            }

            DB::commit();

            return redirect()
                ->route('order-manual-modul.manual')
                ->with('success', "✅ Berhasil menyimpan {$created} item Manual Modul.<br>ID Manual: <strong>{$manualId}</strong>");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Manual Modul Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    private function resolveProduct(array $item): ?Product
    {
        if (!empty($item['product_id'])) {
            $found = Product::find($item['product_id']);
            if ($found) {
                return $found;
            }
        }

        $sku = trim((string) ($item['product_sku'] ?? ''));
        if ($sku === '') {
            return null;
        }

        return Product::query()
            ->where(function ($q) use ($sku) {
                $q->where('label', $sku)
                  ->orWhere('sku', $sku)
                  ->orWhere('kode', $sku);
            })
            ->first();
    }

    public function manualEdit($id)
{
    $order = ManualModulOrder::findOrFail($id);

    // Cegah edit kalau sudah diproses
    if ($order->is_processed) {
        return redirect()
            ->route('order-manual-modul.manual')
            ->with('error', 'Data sudah diproses / dikunci, tidak bisa diedit.');
    }

    $customers = \App\Models\UserExportBimbaShop::query()
        ->select([
            'ID',
            'user_login',
            'user_email',
            'display_name',
            'first_name',
            'last_name',
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_postcode',
            'billing_state',
            'billing_country',
            'billing_phone',
            'billing_email',
        ])
        ->orderBy('display_name')
        ->get();

    return view('order-manual.modul.manual-edit', compact('order', 'customers'));
}

public function manualUpdate(Request $request, $id)
{
    $order = ManualModulOrder::findOrFail($id);

    if ($order->is_processed) {
        return redirect()
            ->route('order-manual-modul.manual')
            ->with('error', 'Data sudah diproses / dikunci, tidak bisa diedit.');
    }

    // Gabungkan order_date_date + order_date_time
    if ($request->filled('order_date_date')) {
        $time = $request->input('order_date_time') ?: '00:00';
        if (strlen($time) === 5) {
            $time .= ':00';
        }
        $request->merge([
            'order_date' => $request->order_date_date . ' ' . $time,
        ]);
    }

    if (!$request->filled('customer_name')) {
        $request->merge([
            'customer_name' => trim(
                $request->billing_company
                ?: $request->billing_first_name
                ?: ''
            ),
        ]);
    }

    $request->validate([
        'customer_name'      => 'required|string|max:150',
        'product_name'       => 'required|string|max:255',
        'qty'                => 'required|integer|min:1',
        'product_sku'        => 'nullable|string|max:100',
        'order_date'         => 'nullable|date',
        'order_id'           => 'nullable|string|max:100',
        'billing_last_name'  => 'nullable|string|max:50',
        'billing_first_name' => 'nullable|string|max:100',
        'status_kirim'       => 'nullable|string|max:50',
        'ekspedisi'          => 'nullable|string|max:100',
        'service_pengiriman' => 'nullable|string|max:50',
        'shipping_address_1' => 'nullable|string',
        'shipping_address_2' => 'nullable|string',
        'shipping_city'      => 'nullable|string|max:100',
        'phone'              => 'nullable|string|max:30',
        'catatan'            => 'nullable|string',
        'notes'              => 'nullable|string',
        'status'             => 'nullable|string|max:50',
        'payment_method'     => 'nullable|string|max:50',
        'order_weight'       => 'nullable|numeric',
        'harga_jual'         => 'nullable|numeric|min:0',
        'product_id'         => 'nullable|integer',
    ]);

    $statusKirim = $request->status_kirim ?? $order->status_kirim ?? 'Dikirim';
    $ekspedisi   = $request->ekspedisi;
    $service     = $request->service_pengiriman;

    if ($statusKirim === 'Diambil') {
        $ekspedisi = $ekspedisi ?: 'Diambil Sendiri';
        $service   = $service ?: null;
    }

    $hargaJual = (float) ($request->harga_jual ?? $order->price ?? 0);

    $data = [
        'customer_name'      => $request->customer_name,
        'product_name'       => $request->product_name,
        'qty'                => (int) $request->qty,
        'product_sku'        => $request->product_sku,
        'order_date'         => $request->order_date ?? $order->order_date,
        'order_id'           => $request->order_id,
        'billing_first_name' => $request->billing_first_name,
        'billing_last_name'  => $request->billing_last_name,
        'status_kirim'       => $statusKirim,
        'ekspedisi'          => $ekspedisi,
        'service_pengiriman' => $service,
        'shipping_address_1' => $request->shipping_address_1,
        'shipping_address_2' => $request->shipping_address_2,
        'shipping_city'      => $request->shipping_city,
        'phone'              => $request->phone ?? $request->shipping_phone,
        'catatan'            => $request->catatan,
        'notes'              => $request->notes,
        'status'             => $request->status ?? $order->status,
        'payment_method'     => $request->payment_method ?? $order->payment_method,
        'order_weight'       => $request->order_weight,
        'price'              => $hargaJual,   // ← ke kolom price
    // Baru
        'billing_kelurahan'  => $request->billing_kelurahan,
        'billing_kecamatan'  => $request->billing_kecamatan,
        'shipping_kelurahan' => $request->shipping_kelurahan,
        'shipping_kecamatan' => $request->shipping_kecamatan,
    ];

    if (Schema::hasColumn('manual_modul_orders', 'total')) {
        $data['total'] = $hargaJual;
    }

    // Resolve product
    if (!empty($data['product_sku']) || $request->filled('product_id')) {
        $product = null;
        if ($request->filled('product_id')) {
            $product = Product::find($request->product_id);
        }
        if (!$product && !empty($data['product_sku'])) {
            $product = Product::query()
                ->where('label', $data['product_sku'])
                ->orWhere('sku', $data['product_sku'])
                ->orWhere('kode', $data['product_sku'])
                ->first();
        }
        if ($product) {
            $data['product_sku'] = trim((string) ($product->label ?: $product->sku ?: $product->kode));
            if (empty($data['product_name'])) {
                $data['product_name'] = $product->name;
            }
            if (Schema::hasColumn('manual_modul_orders', 'product_id')) {
                $data['product_id'] = $product->id;
            }
        }
    }

    $order->update($data);

    return redirect()
        ->route('order-manual-modul.manual')
        ->with('success', '✅ Data Manual Modul berhasil diupdate.');
}

    public function getFilteredIds(Request $request)
    {
        $query = ManualModulOrder::query()->where(function ($q) {
            $q->where('is_processed', 0)->orWhereNull('is_processed');
        });

        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }
        if ($request->filled('order_id')) {
            $query->where('order_id', 'like', '%' . $request->order_id . '%');
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }
        if ($request->filled('product_sku')) {
            $query->where('product_sku', 'like', '%' . $request->product_sku . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('grup')) {
            $query->where('grup', $request->grup);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $ids = $query->pluck('id');

        return response()->json([
            'ids'   => $ids->values(),
            'count' => $ids->count(),
        ]);
    }

    public function getModalData(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([]);
        }

        $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
            ->get()
            ->keyBy(fn ($m) => trim((string) $m->no_cab))
            ->map(fn ($m) => [
                'nama_excel'  => $m->nama_excel,
                'nama_master' => $m->nama_master,
            ])
            ->all();

        $data = ManualModulOrder::whereIn('id', $ids)
            ->get()
            ->map(function ($item) use ($mismatchMap) {
                $jasaKurir = $item->ekspedisi ?: 'Lion Parcel';
                $service   = $item->service_pengiriman ?: 'REGPACK';

                $statusKirim = $item->status_kirim
                    ?: ((($item->ship_total ?? 0) > 0) ? 'Dikirim' : 'Diambil');

                $noCab      = trim($item->billing_last_name ?? '');
                $mismatch   = $mismatchMap[$noCab] ?? null;
                $isMismatch = (bool) $mismatch
                    || str_contains($item->catatan ?? '', 'NAMA_MISMATCH')
                    || str_contains($item->notes ?? '', 'NAMA_MISMATCH');

                $paymentDate = $item->payment_date
                    ? Carbon::parse($item->payment_date)->format('d/m/Y H:i')
                    : null;

                $processedAt = $item->processed_at
                    ? Carbon::parse($item->processed_at)->format('d/m/Y H:i')
                    : null;

                return [
                    'id'                => $item->id,
                    'invoice'           => $item->order_id ?? '-',
                    'to_customer'       => $item->customer_name ?? '-',
                    'pesanan'           => $item->product_name ?? $item->product_sku ?? '-',
                    'payment_date'      => $paymentDate,
                    'payment_channel'   => $item->payment_method ?? 'manual',
                    'status_pembayaran' => 'MANUAL',
                    'status_kirim'      => $statusKirim,
                    'vendor'            => 'Manual / Modul',
                    'jasa_kurir'        => $jasaKurir,
                    'service_kurir'     => $service,
                    'grup'              => $item->grup,
                    'is_processed'      => (bool) $item->is_processed,
                    'processed_at'      => $processedAt,
                    'is_mismatch'       => $isMismatch,
                    'nama_excel'        => $mismatch['nama_excel'] ?? null,
                    'nama_master'       => $mismatch['nama_master'] ?? null,
                ];
            });

        return response()->json($data);
    }

    public function bulkAction(Request $request)
{
    $action  = $request->input('action');
    $perItem = $request->input('per_item');

    if ($action !== 'processed' || empty($perItem)) {
        return back()->with('error', 'Data tidak valid.');
    }

    $updates = json_decode($perItem, true);

    if (empty($updates) || !is_array($updates)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $now = Carbon::now('Asia/Jakarta');
    $successCount = 0;
    $errors = [];

    foreach ($updates as $update) {
        $id = $update['id'] ?? null;
        if (!$id) continue;

        $order = ManualModulOrder::find($id);
        if (!$order || $order->is_processed) continue;

        $statusKirim  = $update['status_kirim'] ?? $order->status_kirim ?? 'Dikirim';
        $jasaKurir    = $update['jasa_kurir'] ?? $order->ekspedisi;
        $serviceKurir = $update['service_kurir'] ?? $order->service_pengiriman;
        $catatanBaru  = $update['catatan'] ?? null;

        if ($statusKirim === 'Diambil') {
            $jasaKurir    = $jasaKurir ?: 'Diambil Sendiri';
            $serviceKurir = $serviceKurir ?: null;
        }

        // Estimasi
        $baseDate = $order->payment_date
            ? Carbon::parse($order->payment_date)
            : ($order->order_date ? Carbon::parse($order->order_date) : $now);

        $estimasiPrintPl = $order->estimasi_print_pl
            ? Carbon::parse($order->estimasi_print_pl)
            : null;

        $estimasiPersiapan = $order->estimasi_persiapan
            ? Carbon::parse($order->estimasi_persiapan)
            : null;

        if (!$estimasiPrintPl) {
            $estimasiPrintPl = $baseDate->hour < 12
                ? $baseDate->copy()
                : $baseDate->copy()->addDay();

            while ($estimasiPrintPl->isSunday()) {
                $estimasiPrintPl->addDay();
            }

            $estimasiPersiapan = $estimasiPrintPl->copy()->addWeekdays(2);
        }

        $catatan = $order->catatan ?? '';
        if ($catatanBaru) {
            $catatan .= ($catatan ? "\n\n" : '')
                . 'Di proses bulk pada ' . $now->format('d/m/Y H:i') . ': ' . trim($catatanBaru);
        }

        // Kategori
        $namaBarang = trim($order->product_name ?? $order->product_sku ?? '');
        $namaLower  = strtolower($namaBarang);
        $skuUpper   = strtoupper(trim($order->product_sku ?? ''));

        if (str_contains($namaLower, 'majalah') || preg_match('/\bM\d{2,4}\b/i', $namaBarang)) {
            $kategoriOrder = 'Majalah';
        } elseif (str_contains($namaLower, 'sertifikat') || str_contains($skuUpper, 'STA') || str_contains($skuUpper, 'STPB')) {
            $kategoriOrder = 'Sertifikat';
        } else {
            $kategoriOrder = 'Modul';
        }

        try {
            DB::transaction(function () use (
                $order, $now, $statusKirim, $jasaKurir, $serviceKurir,
                $estimasiPrintPl, $estimasiPersiapan, $catatan, $kategoriOrder
            ) {
                // 1. Update order
                $order->update([
                    'status_kirim'       => $statusKirim,
                    'ekspedisi'          => $jasaKurir,
                    'service_pengiriman' => $serviceKurir,
                    'is_processed'       => true,
                    'processed_at'       => $now,
                    'status'             => 'processing',
                    'estimasi_print_pl'  => $estimasiPrintPl,
                    'estimasi_persiapan' => $estimasiPersiapan,
                    'catatan'            => $catatan ?: $order->catatan,
                ]);

                // 2. Generate No PL
                $datePart = $now->format('ymd');
                $lastPl = DB::table('manual_modul_realisasis')
                    ->where('no_pl', 'like', "PL-MM-{$datePart}-%")
                    ->orderByDesc('no_pl')
                    ->value('no_pl');

                $nextSeq = $lastPl ? ((int) substr($lastPl, -4)) + 1 : 1;
                $noPl = 'PL-MM-' . $datePart . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                // 3. Rekap Aktual Modul
                $realisasiId = DB::table('manual_modul_realisasis')->insertGetId([
                    'manual_modul_order_id' => $order->id,
                    'no_pl'                 => $noPl,
                    'tgl_turun_pl'          => $now->toDateString(),
                    'nama_unit'             => $order->customer_name,
                    'billing_last_name'     => $order->billing_last_name,
                    'billing_company'       => $order->customer_name,
                    'kategori_order'        => $kategoriOrder,
                    'nama_barang'           => $order->product_name,
                    'rekap_number'          => null,
                    'picking_printed_at'    => null,
                    'printed_at'            => null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);

                // 4. Picking Modul
                $modulPickingId = DB::table('manual_modul_pickings')->insertGetId([
                    'manual_modul_realisasi_id' => $realisasiId,
                    'status'                    => 'pending',
                    'printed_at'                => null,
                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]);

                DB::table('manual_modul_picking_items')->insert([
                    'manual_modul_picking_id' => $modulPickingId,
                    'item_sku'                => $order->product_sku,
                    'item_name'               => $order->product_name,
                    'qty'                     => $order->qty ?? 1,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ]);

                // =====================================================
                // 5. Insert ke manual_realisasi + manual_pickings
                //    (tanpa try-catch supaya error muncul)
                // =====================================================

                // 5a. manual_realisasi
                $manualRealisasiData = [
                    'no_pl'             => $noPl,
                    'tgl_turun_pl'      => $now->toDateString(),
                    'nama_unit'         => $order->customer_name,
                    'billing_last_name' => $order->billing_last_name,
                    'billing_company'   => $order->customer_name,
                    'kategori_order'    => $kategoriOrder,
                    'nama_barang'       => $order->product_name,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                // Kolom yang sering ada / mungkin wajib
                $extra = [
                    'manual_order_id'           => $order->id,
                    'id_pesan'                  => $order->order_id,
                    'no_ps'                     => $order->order_id,
                    'grup'                      => $order->grup,
                    'pengiriman'                => $jasaKurir,
                    'service_pengiriman'        => $serviceKurir,
                    'tgl_order'                 => $order->order_date ? Carbon::parse($order->order_date)->toDateString() : $now->toDateString(),
                    'waktu_estimasi_persiapan'  => $estimasiPersiapan,
                    'tgl_estimasi'              => $estimasiPersiapan,
                    'payment_date'              => $order->payment_date,
                    'tgl_bayar'                 => $order->payment_date,
                    'rekap_number'              => null,
                    'picking_printed_at'        => null,
                    'printed_at'                => null,
                ];

                foreach ($extra as $col => $val) {
                    if (Schema::hasColumn('manual_realisasi', $col)) {
                        $manualRealisasiData[$col] = $val;
                    }
                }

                $manualRealisasiId = DB::table('manual_realisasi')->insertGetId($manualRealisasiData);

                // 5b. manual_pickings
                $manualPickingData = [
                    'manual_realisasi_id'      => $manualRealisasiId,
                    'no_pl'                    => $noPl,
                    'nama_unit'                => $order->customer_name,
                    'billing_last_name'        => $order->billing_last_name,
                    'billing_company'          => $order->customer_name,
                    'kategori_order'           => $kategoriOrder,
                    'pesanan'                  => $order->product_name,
                    'status'                   => 'pending',
                    'status_persiapan'         => 'Belum',
                    'printed_at'               => null,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                    'created_by'               => Auth::id(),
                ];

                $extraPicking = [
                    'manual_order_id'           => $order->id,
                    'id_pesan'                  => $order->order_id,
                    'no_ps'                     => $order->order_id,
                    'grup'                      => $order->grup,
                    'payment_date'              => $order->payment_date,
                    'tgl_order'                 => $order->order_date ? Carbon::parse($order->order_date)->toDateString() : $now->toDateString(),
                    'waktu_estimasi_persiapan'  => $estimasiPersiapan,
                    'ekspedisi'                 => $jasaKurir,
                    'service_pengiriman'        => $serviceKurir,
                ];

                foreach ($extraPicking as $col => $val) {
                    if (Schema::hasColumn('manual_pickings', $col)) {
                        $manualPickingData[$col] = $val;
                    }
                }

                $manualPickingId = DB::table('manual_pickings')->insertGetId($manualPickingData);

                // 5c. Item
                $itemData = [
                    'manual_picking_id' => $manualPickingId,
                    'item_sku'          => $order->product_sku,
                    'item_name'         => $order->product_name,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                if (Schema::hasColumn('manual_picking_items', 'qty')) {
                    $itemData['qty'] = $order->qty ?? 1;
                }
                if (Schema::hasColumn('manual_picking_items', 'item_qty')) {
                    $itemData['item_qty'] = $order->qty ?? 1;
                }

                DB::table('manual_picking_items')->insert($itemData);
            });

            $successCount++;
        } catch (\Throwable $e) {
            $errors[] = "ID {$id}: " . $e->getMessage();
            Log::error('Bulk Modul gagal id ' . $id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    $msg = "✅ Berhasil memproses <strong>{$successCount}</strong> data modul.";

    if (!empty($errors)) {
        $msg .= '<br><br><span class="text-danger">Gagal: ' . implode('<br>', $errors) . '</span>';
    }

    return redirect()
        ->route('order-manual-modul.manual')
        ->with($successCount > 0 ? 'success' : 'error', $msg);
}

    /**
     * Sync Manual Modul dari Bimba Shop + Casdana
     */
    public function syncManualModulFromBimbashopCasdana()
    {
        $updated          = 0;
        $skippedNoCasdana = 0;
        $skippedNoMatch   = 0;

        $manualOrders = ManualModulOrder::query()
            ->where(function ($q) {
                $q->whereNull('payment_date')
                  ->orWhereNull('estimasi_print_pl')
                  ->orWhereNull('estimasi_persiapan')
                  ->orWhereNull('order_date')
                  ->orWhereNull('status_bayar')
                  ->orWhereNull('status_bimbashop');
            })
            ->get();

        foreach ($manualOrders as $manual) {

            $orderId  = trim($manual->order_id ?? '');
            $noCab    = trim($manual->billing_last_name ?? '');
            $namaUnit = trim($manual->customer_name ?? '');

            if ($orderId === '' && $noCab === '') {
                $skippedNoMatch++;
                continue;
            }

            $bimbashopQuery = BimbashopOrder::query();

            if ($orderId !== '') {
                $bimbashopQuery->where('order_id', $orderId);
            } else {
                $bimbashopQuery->where(function ($q) use ($noCab) {
                    $q->where('billing_last_name', $noCab)
                      ->orWhereRaw("TRIM(LEADING '0' FROM billing_last_name) = ?", [ltrim($noCab, '0') ?: '0']);
                });

                if ($namaUnit !== '') {
                    $bimbashopQuery->where(function ($q) use ($namaUnit) {
                        $q->where('billing_company', 'like', '%' . $namaUnit . '%')
                          ->orWhere('billing_first_name', 'like', '%' . $namaUnit . '%');
                    });
                }
            }

            $bimbashopItem = $bimbashopQuery->first();

            if (!$bimbashopItem) {
                $skippedNoMatch++;
                continue;
            }

            $realOrderId = $bimbashopItem->order_id;
            $statusBimbashop = strtolower(trim($bimbashopItem->status ?? ''));

            $casdana = CasdanaTransaction::where(function ($q) use ($realOrderId) {
                    $q->where('invoice_number', $realOrderId)
                      ->orWhere('invoice_number', 'ID' . $realOrderId)
                      ->orWhere('invoice_number', 'like', '%' . $realOrderId . '%');
                })
                ->latest('id')
                ->first();

            $paymentDate = null;
            $isPaid      = false;
            $statusBayar = null;

            if ($casdana) {
                $statusBayar = strtoupper(trim($casdana->status ?? ''));
                if (in_array($statusBayar, ['SUCCESS', 'SETTLED', 'PAID'])) {
                    $paymentDate = $casdana->payment_date;
                    $isPaid = true;
                }
            } else {
                $skippedNoCasdana++;
            }

            $baseDate = $isPaid && $paymentDate
                ? Carbon::parse($paymentDate)
                : ($bimbashopItem->order_date
                    ? Carbon::parse($bimbashopItem->order_date)
                    : ($manual->order_date ? Carbon::parse($manual->order_date) : now()));

            $estimasiPrintPl = $baseDate->copy();
            if ($baseDate->hour >= 12) {
                $estimasiPrintPl->addDay();
            }
            while ($estimasiPrintPl->isSunday()) {
                $estimasiPrintPl->addDay();
            }
            $estimasiPersiapan = $estimasiPrintPl->copy()->addWeekdays(2);

            $dataUpdate = [];

            if ($manual->order_id != $realOrderId) {
                $dataUpdate['order_id'] = $realOrderId;
            }

            if (empty($manual->order_date) && $bimbashopItem->order_date) {
                $dataUpdate['order_date'] = $bimbashopItem->order_date;
            }

            if (empty($manual->payment_date) && $isPaid && $paymentDate) {
                $dataUpdate['payment_date'] = $paymentDate;
                $dataUpdate['status'] = 'completed';
            }

            if ($statusBayar && empty($manual->status_bayar)) {
                $dataUpdate['status_bayar'] = $statusBayar;
            }

            if ($statusBimbashop && empty($manual->status_bimbashop)) {
                $dataUpdate['status_bimbashop'] = $statusBimbashop;
            }

            if (empty($manual->estimasi_print_pl)) {
                $dataUpdate['estimasi_print_pl'] = $estimasiPrintPl;
            }
            if (empty($manual->estimasi_persiapan)) {
                $dataUpdate['estimasi_persiapan'] = $estimasiPersiapan;
            }

            $bimbashopQty = (int) ($bimbashopItem->item_qty ?? 0);
            if ($bimbashopQty > 0 && (empty($manual->qty) || (int) $manual->qty <= 1)) {
                $dataUpdate['qty'] = $bimbashopQty;
            }

            if (empty($manual->shipping_address_1) && !empty($bimbashopItem->shipping_address_1)) {
                $dataUpdate['shipping_address_1'] = $bimbashopItem->shipping_address_1;
            }
            if (empty($manual->shipping_city) && !empty($bimbashopItem->shipping_city)) {
                $dataUpdate['shipping_city'] = $bimbashopItem->shipping_city;
            }

            if (empty($manual->product_name) && !empty($bimbashopItem->item_name)) {
                $dataUpdate['product_name'] = $bimbashopItem->item_name;
            }
            if (empty($manual->product_sku) && !empty($bimbashopItem->item_sku)) {
                $dataUpdate['product_sku'] = $bimbashopItem->item_sku;
            }

            if (!empty($dataUpdate)) {
                $dataUpdate['catatan'] = trim(($manual->catatan ?? '') . "\n[SYNC BIMBA] " . now()->format('d/m/Y H:i'));
                $manual->update($dataUpdate);
                $updated++;
            }
        }

        return [
            'updated'            => $updated,
            'skipped_no_casdana' => $skippedNoCasdana,
            'skipped_no_match'   => $skippedNoMatch,
        ];
    }

    public function runSyncModul()
    {
        try {
            $result = $this->syncManualModulFromBimbashopCasdana();

            $msg = "✅ Sync selesai (hanya update data Manual yang sudah ada).<br>"
                 . "Berhasil di-update: <strong>{$result['updated']}</strong><br>"
                 . "Tidak ketemu Casdana: <strong>{$result['skipped_no_casdana']}</strong><br>"
                 . "Tidak ketemu di Bimba Shop: <strong>{$result['skipped_no_match']}</strong>";

            return redirect()
                ->route('order-manual-modul.manual')
                ->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Sync Modul gagal: ' . $e->getMessage());
            return redirect()
                ->route('order-manual-modul.manual')
                ->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }

    public function realisasi(Request $request)
{
    $query = DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
        ->leftJoin('manual_modul_pickings as p', 'p.manual_modul_realisasi_id', '=', 'r.id')
        ->select([
            'r.id',
            'r.manual_modul_order_id',
            'r.no_pl',
            'r.tgl_turun_pl',
            'r.nama_unit',
            'r.billing_last_name',
            'r.billing_company',
            'r.kategori_order',
            'r.nama_barang',
            'r.rekap_number',
            'r.picking_printed_at',
            'r.printed_at',
            'r.created_at',
            'o.order_id',
            'o.product_sku',
            'o.qty',
            'o.grup',
            'o.status_kirim',
            'o.ekspedisi',
            'o.service_pengiriman',
            'o.catatan',
            'p.id as picking_id',
            'p.status as picking_status',
            'p.printed_at as picking_printed_at_p',
        ]);

    if ($request->filled('kategori') && $request->kategori !== 'Semua') {
        $query->where('r.kategori_order', $request->kategori);
    }
    if ($request->filled('nama_unit')) {
        $query->where('r.nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('no_pl')) {
        $query->where('r.no_pl', 'like', '%' . $request->no_pl . '%');
    }
    if ($request->filled('start_date')) {
        $query->whereDate('r.tgl_turun_pl', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('r.tgl_turun_pl', '<=', $request->end_date);
    }

    $allData = $query
        ->orderBy('r.tgl_turun_pl')
        ->orderBy('r.id')
        ->get();

    // Assign rekap_number per tanggal
    foreach ($allData->groupBy(fn ($item) => Carbon::parse($item->tgl_turun_pl)->toDateString()) as $tanggal => $rows) {
        $rekapNumber = $this->generateRekapNumberModul($tanggal);

        DB::table('manual_modul_realisasis')
            ->whereIn('id', $rows->pluck('id'))
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $rekapNumber,
                'updated_at'   => now(),
            ]);

        foreach ($rows as $row) {
            if (empty($row->rekap_number)) {
                $row->rekap_number = $rekapNumber;
            }
        }
    }

    $groupedData = $allData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    return view('order-manual.modul.realisasi-index', [
        'data'        => $allData,
        'groupedData' => $groupedData,
    ]);
}

private function generateRekapNumberModul($tanggal)
{
    $date = Carbon::parse($tanggal)->format('ymd');

    $existing = DB::table('manual_modul_realisasis')
        ->whereDate('tgl_turun_pl', $tanggal)
        ->whereNotNull('rekap_number')
        ->orderBy('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing;
    }

    return 'RAMM-' . $date . '-' . str_pad(1, 4, '0', STR_PAD_LEFT);
}

/** Print Picking List Manual Modul */
public function printPickingList($id)
{
    $main = DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_pickings as p', 'p.manual_modul_realisasi_id', '=', 'r.id')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
        ->where('r.id', $id)
        ->select(
            'r.*',
            'p.id as picking_id',
            'p.status as picking_status',
            'p.printed_at as picking_printed_at_p',
            'o.product_sku',
            'o.qty',
            'o.status_kirim',
            'o.ekspedisi',
            'o.service_pengiriman'
        )
        ->first();

    if (!$main) {
        return back()->with('error', 'Data realisasi tidak ditemukan.');
    }

    if (!$main->picking_id) {
        return back()->with('error', 'Picking belum dibuat untuk data ini.');
    }

    // Tandai sudah dicetak
    if (is_null($main->picking_printed_at)) {
        DB::table('manual_modul_realisasis')->where('id', $id)->update([
            'picking_printed_at' => now(),
            'updated_at'         => now(),
        ]);
    }

    DB::table('manual_modul_pickings')->where('id', $main->picking_id)->update([
        'printed_at' => now(),
        'status'     => 'completed',
        'updated_at' => now(),
    ]);

    $items = DB::table('manual_modul_picking_items')
        ->where('manual_modul_picking_id', $main->picking_id)
        ->orderBy('item_sku')
        ->get();

    return view('order-manual.modul.picking-list', [
        'item'              => $main,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
    ]);
}

private function getRealisasiByIds(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));
    if (empty($ids)) {
        return collect();
    }

    return DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
        ->whereIn('r.id', $ids)
        ->select([
            'r.*',
            'o.order_id',
            'o.product_sku',
            'o.qty',
            'o.grup',
            'o.status_kirim',
            'o.ekspedisi',
            'o.service_pengiriman',
            'o.payment_date',
            'o.order_date',
            'o.catatan as order_catatan',
            'o.shipping_address_1',
            'o.shipping_city',
            'o.phone',
        ])
        ->orderBy('r.tgl_turun_pl')
        ->orderBy('r.no_pl')
        ->get();
}

public function printPrising(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    if ($request->boolean('mark_printed')) {
        DB::table('manual_modul_realisasis')
            ->whereIn('id', $data->pluck('id'))
            ->whereNull('printed_at')
            ->update(['printed_at' => now(), 'updated_at' => now()]);
    }

    return view('order-manual.modul.print-ra', [
        'data'    => $data,
        'title'   => 'RA Prising - Manual Modul',
        'tipe'    => 'prising',
    ]);
}

public function printPemesanan(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Picking - Manual Modul',
        'tipe'  => 'pemesanan',
    ]);
}

public function printQc(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA QC - Manual Modul',
        'tipe'  => 'qc',
    ]);
}

public function printPacking(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Packing - Manual Modul',
        'tipe'  => 'packing',
    ]);
}

public function printEkspedisi(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Ekspedisi - Manual Modul',
        'tipe'  => 'ekspedisi',
    ]);
}
}