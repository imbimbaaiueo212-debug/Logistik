<?php

namespace App\Http\Controllers;

use App\Models\ManualSertifikatOrder;
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

class OrderManualSertifikatController extends Controller
{
    public function index()
    {
        return view('order-manual.sertifikat-index');
    }

    public function manual(Request $request)
    {
        $query = ManualSertifikatOrder::query();

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

        $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
            ->get()
            ->keyBy(fn ($m) => trim((string) $m->no_cab))
            ->map(fn ($m) => [
                'nama_excel'  => $m->nama_excel,
                'nama_master' => $m->nama_master,
            ])
            ->all();

        return view('order-manual.sertifikat.manual-index', compact('manualOrders', 'mismatchMap'));
    }

    public function manualCreate()
    {
        $customers = \App\Models\UserExportBimbaShop::query()
            ->select([
                'ID', 'user_login', 'user_email', 'display_name',
                'first_name', 'last_name',
                'billing_first_name', 'billing_last_name', 'billing_company',
                'billing_address_1', 'billing_address_2', 'billing_city',
                'billing_postcode', 'billing_state', 'billing_country',
                'billing_phone', 'billing_email',
            ])
            ->orderBy('display_name')
            ->get();

        return view('order-manual.sertifikat.manual-create', compact('customers'));
    }

    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $query = Product::query()->select([
            'id', 'sku', 'label', 'name', 'kode', 'jenis', 'kategori', 'berat_paket', 'harga_jual',
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
            if ($sku === '') return null;

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
        })->filter()->values();

        return response()->json(['results' => $results]);
    }

    public function manualStore(Request $request)
    {
        if ($request->filled('order_date_date')) {
            $time = $request->input('order_date_time') ?: '00:00';
            if (strlen($time) === 5) $time .= ':00';
            $request->merge(['order_date' => $request->order_date_date . ' ' . $time]);
        }

        if (!$request->filled('customer_name')) {
            $request->merge([
                'customer_name' => trim($request->billing_company ?: $request->billing_first_name ?: ''),
            ]);
        }

        $request->validate([
            'customer_name'        => 'required|string|max:150',
            'order_id'             => 'nullable|string|max:100',
            'order_date'           => 'nullable|date',
            'billing_last_name'    => 'nullable|string|max:50',
            'billing_first_name'   => 'nullable|string|max:100',
            'status_kirim'         => 'nullable|string|max:50',
            'ekspedisi'            => 'nullable|string|max:100',
            'service_pengiriman'   => 'nullable|string|max:50',
            'shipping_address_1'   => 'nullable|string',
            'shipping_address_2'   => 'nullable|string',
            'shipping_city'        => 'nullable|string|max:100',
            'phone'                => 'nullable|string|max:30',
            'catatan'              => 'nullable|string',
            'order_weight'         => 'nullable|numeric',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_sku'  => 'nullable|string|max:100',
            'items.*.qty'          => 'required|integer|min:1',
        ], [
            'customer_name.required'        => 'Customer / nama unit wajib diisi.',
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
            $todayPrefix = 'MS-' . date('Ymd') . '-';

            $lastManual = ManualSertifikatOrder::where('manual_id', 'like', $todayPrefix . '%')
                ->lockForUpdate()
                ->orderByDesc('manual_id')
                ->value('manual_id');

            $nextNumber = $lastManual ? ((int) substr($lastManual, -4)) + 1 : 1;
            $manualId   = $todayPrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            foreach ($items as $item) {
                $product = $this->resolveProduct($item);

                $productSku = $product
                    ? trim((string) ($product->label ?: $product->sku ?: $product->kode))
                    : trim((string) ($item['product_sku'] ?? ''));

                $productName = trim((string) ($item['product_name'] ?? '')) ?: ($product->name ?? '');
                if ($productName === '') continue;

                $hargaJual = (float) ($item['harga_jual'] ?? 0);

                $row = [
                    'manual_id'           => $manualId,
                    'order_id'            => $request->order_id,
                    'order_date'          => $request->order_date ?? now(),
                    'customer_name'       => $request->customer_name,
                    'billing_first_name'  => $request->billing_first_name,
                    'billing_last_name'   => $request->billing_last_name,
                    'product_sku'         => $productSku ?: null,
                    'product_name'        => $productName,
                    'qty'                 => (int) $item['qty'],
                    'price'               => $hargaJual,
                    'payment_method'      => $request->payment_method ?? 'manual',
                    'status'              => $request->status ?? 'pending',
                    'status_kirim'        => $statusKirim,
                    'ekspedisi'           => $ekspedisi,
                    'service_pengiriman'  => $service,
                    'shipping_address_1'  => $request->shipping_address_1,
                    'shipping_address_2'  => $request->shipping_address_2,
                    'shipping_city'       => $request->shipping_city,
                    'billing_kelurahan'   => $request->billing_kelurahan,
                    'billing_kecamatan'   => $request->billing_kecamatan,
                    'shipping_kelurahan'  => $request->shipping_kelurahan,
                    'shipping_kecamatan'  => $request->shipping_kecamatan,
                    'phone'               => $request->phone ?? $request->shipping_phone,
                    'order_weight'        => $request->order_weight,
                    'catatan'             => $request->catatan,
                    'is_processed'        => false,
                ];

                if (Schema::hasColumn('manual_sertifikat_orders', 'total')) {
                    $row['total'] = $hargaJual;
                }
                if (Schema::hasColumn('manual_sertifikat_orders', 'product_id') && $product) {
                    $row['product_id'] = $product->id;
                }

                ManualSertifikatOrder::create($row);
                $created++;
            }

            if ($created === 0) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Tidak ada item valid yang bisa disimpan.');
            }

            DB::commit();

            return redirect()
                ->route('order-manual-sertifikat.manual')
                ->with('success', "✅ Berhasil menyimpan {$created} item Manual Sertifikat.<br>ID Manual: <strong>{$manualId}</strong>");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Manual Sertifikat Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    private function resolveProduct(array $item): ?Product
    {
        if (!empty($item['product_id'])) {
            $found = Product::find($item['product_id']);
            if ($found) return $found;
        }

        $sku = trim((string) ($item['product_sku'] ?? ''));
        if ($sku === '') return null;

        return Product::query()
            ->where(function ($q) use ($sku) {
                $q->where('label', $sku)->orWhere('sku', $sku)->orWhere('kode', $sku);
            })
            ->first();
    }

    public function manualEdit($id)
    {
        $order = ManualSertifikatOrder::findOrFail($id);

        if ($order->is_processed) {
            return redirect()
                ->route('order-manual-sertifikat.manual')
                ->with('error', 'Data sudah diproses / dikunci, tidak bisa diedit.');
        }

        $customers = \App\Models\UserExportBimbaShop::query()
            ->select([
                'ID', 'user_login', 'user_email', 'display_name',
                'first_name', 'last_name',
                'billing_first_name', 'billing_last_name', 'billing_company',
                'billing_address_1', 'billing_address_2', 'billing_city',
                'billing_postcode', 'billing_state', 'billing_country',
                'billing_phone', 'billing_email',
            ])
            ->orderBy('display_name')
            ->get();

        return view('order-manual.sertifikat.manual-edit', compact('order', 'customers'));
    }

    public function manualUpdate(Request $request, $id)
    {
        $order = ManualSertifikatOrder::findOrFail($id);

        if ($order->is_processed) {
            return redirect()
                ->route('order-manual-sertifikat.manual')
                ->with('error', 'Data sudah diproses / dikunci, tidak bisa diedit.');
        }

        if ($request->filled('order_date_date')) {
            $time = $request->input('order_date_time') ?: '00:00';
            if (strlen($time) === 5) $time .= ':00';
            $request->merge(['order_date' => $request->order_date_date . ' ' . $time]);
        }

        if (!$request->filled('customer_name')) {
            $request->merge([
                'customer_name' => trim($request->billing_company ?: $request->billing_first_name ?: ''),
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
            'customer_name'       => $request->customer_name,
            'product_name'        => $request->product_name,
            'qty'                 => (int) $request->qty,
            'product_sku'         => $request->product_sku,
            'order_date'          => $request->order_date ?? $order->order_date,
            'order_id'            => $request->order_id,
            'billing_first_name'  => $request->billing_first_name,
            'billing_last_name'   => $request->billing_last_name,
            'status_kirim'        => $statusKirim,
            'ekspedisi'           => $ekspedisi,
            'service_pengiriman'  => $service,
            'shipping_address_1'  => $request->shipping_address_1,
            'shipping_address_2'  => $request->shipping_address_2,
            'shipping_city'       => $request->shipping_city,
            'billing_kelurahan'   => $request->billing_kelurahan,
            'billing_kecamatan'   => $request->billing_kecamatan,
            'shipping_kelurahan'  => $request->shipping_kelurahan,
            'shipping_kecamatan'  => $request->shipping_kecamatan,
            'phone'               => $request->phone ?? $request->shipping_phone,
            'catatan'             => $request->catatan,
            'notes'               => $request->notes,
            'status'              => $request->status ?? $order->status,
            'payment_method'      => $request->payment_method ?? $order->payment_method,
            'order_weight'        => $request->order_weight,
            'price'               => $hargaJual,
        ];

        if (Schema::hasColumn('manual_sertifikat_orders', 'total')) {
            $data['total'] = $hargaJual;
        }

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
                if (empty($data['product_name'])) $data['product_name'] = $product->name;
                if (Schema::hasColumn('manual_sertifikat_orders', 'product_id')) {
                    $data['product_id'] = $product->id;
                }
            }
        }

        $order->update($data);

        return redirect()
            ->route('order-manual-sertifikat.manual')
            ->with('success', '✅ Data Manual Sertifikat berhasil diupdate.');
    }

    public function getFilteredIds(Request $request)
    {
        $query = ManualSertifikatOrder::query()->where(function ($q) {
            $q->where('is_processed', 0)->orWhereNull('is_processed');
        });

        if ($request->filled('start_date')) $query->whereDate('order_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('order_date', '<=', $request->end_date);
        if ($request->filled('order_id'))   $query->where('order_id', 'like', '%' . $request->order_id . '%');
        if ($request->filled('customer_name')) $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        if ($request->filled('product_name'))  $query->where('product_name', 'like', '%' . $request->product_name . '%');
        if ($request->filled('product_sku'))   $query->where('product_sku', 'like', '%' . $request->product_sku . '%');
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        $ids = $query->pluck('id');

        return response()->json(['ids' => $ids->values(), 'count' => $ids->count()]);
    }

    public function getModalData(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json([]);

        $data = ManualSertifikatOrder::whereIn('id', $ids)->get()->map(function ($item) {
            $jasaKurir   = $item->ekspedisi ?: 'Lion Parcel';
            $service     = $item->service_pengiriman ?: 'REGPACK';
            $statusKirim = $item->status_kirim ?: ((($item->ship_total ?? 0) > 0) ? 'Dikirim' : 'Diambil');

            return [
                'id'                => $item->id,
                'invoice'           => $item->order_id ?? '-',
                'to_customer'       => $item->customer_name ?? '-',
                'pesanan'           => $item->product_name ?? $item->product_sku ?? '-',
                'payment_date'      => $item->payment_date ? Carbon::parse($item->payment_date)->format('d/m/Y H:i') : null,
                'payment_channel'   => $item->payment_method ?? 'manual',
                'status_pembayaran' => 'MANUAL',
                'status_kirim'      => $statusKirim,
                'jasa_kurir'        => $jasaKurir,
                'service_kurir'     => $service,
                'is_processed'      => (bool) $item->is_processed,
                'processed_at'      => $item->processed_at ? Carbon::parse($item->processed_at)->format('d/m/Y H:i') : null,
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

        $order = ManualSertifikatOrder::find($id);
        if (!$order || $order->is_processed) continue;

        $statusKirim  = $update['status_kirim'] ?? $order->status_kirim ?? 'Dikirim';
        $jasaKurir    = $update['jasa_kurir'] ?? $order->ekspedisi;
        $serviceKurir = $update['service_kurir'] ?? $order->service_pengiriman;
        $catatanBaru  = $update['catatan'] ?? null;

        if ($statusKirim === 'Diambil') {
            $jasaKurir    = $jasaKurir ?: 'Diambil Sendiri';
            $serviceKurir = $serviceKurir ?: null;
        }

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
            while ($estimasiPrintPl->isSunday()) $estimasiPrintPl->addDay();
            $estimasiPersiapan = $estimasiPrintPl->copy()->addWeekdays(2);
        }

        $catatan = $order->catatan ?? '';
        if ($catatanBaru) {
            $catatan .= ($catatan ? "\n\n" : '')
                . 'Di proses bulk pada ' . $now->format('d/m/Y H:i') . ': ' . trim($catatanBaru);
        }

        try {
            DB::transaction(function () use (
                $order, $now, $statusKirim, $jasaKurir, $serviceKurir,
                $estimasiPrintPl, $estimasiPersiapan, $catatan
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
                $lastPl = DB::table('manual_sertifikat_realisasis')
                    ->where('no_pl', 'like', "PL-MS-{$datePart}-%")
                    ->orderByDesc('no_pl')
                    ->value('no_pl');

                $nextSeq = $lastPl ? ((int) substr($lastPl, -4)) + 1 : 1;
                $noPl = 'PL-MS-' . $datePart . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                // 3. Rekap Aktual Sertifikat
                $realisasiId = DB::table('manual_sertifikat_realisasis')->insertGetId([
                    'manual_sertifikat_order_id' => $order->id,
                    'no_pl'                      => $noPl,
                    'tgl_turun_pl'               => $now->toDateString(),
                    'nama_unit'                  => $order->customer_name,
                    'billing_last_name'          => $order->billing_last_name,
                    'billing_company'            => $order->customer_name,
                    'kategori_order'             => 'Sertifikat',
                    'nama_barang'                => $order->product_name,
                    'rekap_number'               => null,
                    'picking_printed_at'         => null,
                    'printed_at'                 => null,
                    'created_at'                 => $now,
                    'updated_at'                 => $now,
                ]);

                // 4. Picking Sertifikat
                $pickingId = DB::table('manual_sertifikat_pickings')->insertGetId([
                    'manual_sertifikat_realisasi_id' => $realisasiId,
                    'status'                         => 'pending',
                    'printed_at'                     => null,
                    'created_at'                     => $now,
                    'updated_at'                     => $now,
                ]);

                DB::table('manual_sertifikat_picking_items')->insert([
                    'manual_sertifikat_picking_id' => $pickingId,
                    'item_sku'                     => $order->product_sku,
                    'item_name'                    => $order->product_name,
                    'qty'                          => $order->qty ?? 1,
                    'created_at'                   => $now,
                    'updated_at'                   => $now,
                ]);

                // =====================================================
                // 5. Insert JUGA ke manual_realisasi + manual_pickings
                //    (tabel global, sama seperti modul Modul)
                // =====================================================

                // 5a. manual_realisasi
                $manualRealisasiData = [
                    'no_pl'             => $noPl,
                    'tgl_turun_pl'      => $now->toDateString(),
                    'nama_unit'         => $order->customer_name,
                    'billing_last_name' => $order->billing_last_name,
                    'billing_company'   => $order->customer_name,
                    'kategori_order'    => 'Sertifikat',
                    'nama_barang'       => $order->product_name,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                $extra = [
                    'manual_order_id'           => $order->id,
                    'id_pesan'                  => $order->order_id,
                    'no_ps'                     => $order->order_id,
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
                    'kategori_order'           => 'Sertifikat',
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
            Log::error('Bulk Sertifikat gagal id ' . $id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    $msg = "✅ Berhasil memproses <strong>{$successCount}</strong> data sertifikat.";
    if (!empty($errors)) {
        $msg .= '<br><br><span class="text-danger">Gagal: ' . implode('<br>', $errors) . '</span>';
    }

    return redirect()
        ->route('order-manual-sertifikat.manual')
        ->with($successCount > 0 ? 'success' : 'error', $msg);
}

    public function runSync()
    {
        // Placeholder — bisa diisi sync Bimba+Casdana nanti seperti modul
        return redirect()
            ->route('order-manual-sertifikat.manual')
            ->with('success', 'Sync Sertifikat belum diaktifkan.');
    }

    public function realisasi(Request $request)
{
    $query = DB::table('manual_sertifikat_realisasis as r')
        ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
        ->leftJoin('manual_sertifikat_pickings as p', 'p.manual_sertifikat_realisasi_id', '=', 'r.id')
        ->select([
            'r.*',
            'o.order_id', 'o.product_sku', 'o.qty',
            'o.status_kirim', 'o.ekspedisi', 'o.service_pengiriman', 'o.catatan',
            'p.id as picking_id', 'p.status as picking_status',
        ]);

    // ➕ TAMBAHKAN INI
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

    $allData = $query->orderBy('r.tgl_turun_pl')->orderBy('r.id')->get();

    return view('order-manual.sertifikat.realisasi-index', [
        'data'        => $allData,
        'groupedData' => $allData->groupBy(fn ($i) => Carbon::parse($i->tgl_turun_pl)->toDateString()),
    ]);
}

           // ========================================
    // PRINT & PICKING LIST (sama seperti Modul)
    // ========================================

    public function pickingList($id)
    {
        $main = DB::table('manual_sertifikat_realisasis as r')
            ->leftJoin('manual_sertifikat_pickings as p', 'p.manual_sertifikat_realisasi_id', '=', 'r.id')
            ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
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
            DB::table('manual_sertifikat_realisasis')->where('id', $id)->update([
                'picking_printed_at' => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('manual_sertifikat_pickings')->where('id', $main->picking_id)->update([
            'printed_at' => now(),
            'status'     => 'completed',
            'updated_at' => now(),
        ]);

        $items = DB::table('manual_sertifikat_picking_items')
            ->where('manual_sertifikat_picking_id', $main->picking_id)
            ->orderBy('item_sku')
            ->get();

        return view('order-manual.sertifikat.picking-list', [
            'item'              => $main,
            'data'              => $items,
            'no_pl'             => $main->no_pl,
            'tgl_order'         => $main->tgl_turun_pl,
            'billing_last_name' => $main->billing_last_name,
            'billing_company'   => $main->billing_company,
            'kategori_order'    => $main->kategori_order ?? 'SERTIFIKAT',
        ]);
    }

    private function getRealisasiByIds(Request $request)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));
        if (empty($ids)) {
            return collect();
        }

        return DB::table('manual_sertifikat_realisasis as r')
            ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
            ->whereIn('r.id', $ids)
            ->select([
                'r.*',
                'o.order_id',
                'o.product_sku',
                'o.qty',
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
            DB::table('manual_sertifikat_realisasis')
                ->whereIn('id', $data->pluck('id'))
                ->whereNull('printed_at')
                ->update(['printed_at' => now(), 'updated_at' => now()]);
        }

        return view('order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Prising - Manual Sertifikat',
            'tipe'  => 'prising',
        ]);
    }

    public function printPemesanan(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Picking - Manual Sertifikat',
            'tipe'  => 'pemesanan',
        ]);
    }

    public function printQc(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA QC - Manual Sertifikat',
            'tipe'  => 'qc',
        ]);
    }

    public function printPacking(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Packing - Manual Sertifikat',
            'tipe'  => 'packing',
        ]);
    }

    public function printEkspedisi(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Ekspedisi - Manual Sertifikat',
            'tipe'  => 'ekspedisi',
        ]);
    }
}