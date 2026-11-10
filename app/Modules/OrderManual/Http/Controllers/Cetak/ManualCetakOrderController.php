<?php

namespace App\Modules\OrderManual\Http\Controllers\Cetak;

use App\Http\Controllers\Controller;
use App\Modules\Kalender\Support\UnitManualOptions;
use App\Modules\Master\Models\Product;
use App\Modules\Master\Models\UserExportBimbaShop;
use App\Modules\OrderManual\Models\ManualCetakOrder;
use App\Modules\OrderManual\Services\CetakShopMatcher;
use App\Modules\OrderManual\Support\KodeStokisSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Create Manual Kalender / Majalah (tampilan seperti Create Manual Modul).
 * Satu controller untuk dua jenis; jenis dibaca dari route (default 'jenis').
 */
class ManualCetakOrderController extends Controller
{
    private const PREFIX = ['kalender' => 'MK-', 'majalah' => 'MJ-'];

    private function jenis(Request $request): string
    {
        $jenis = (string) $request->route('jenis');
        abort_unless(in_array($jenis, ManualCetakOrder::JENIS, true), 404);

        return $jenis;
    }

    // ------------------------------------------------------------------ daftar

    public function manual(Request $request)
    {
        $jenis = $this->jenis($request);

        // Cocokkan dulu dengan biMBA Shop + Kasdana: ada keduanya -> processing, belum -> on-hold
        try {
            app(CetakShopMatcher::class)->sync($jenis);
        } catch (\Throwable $e) {
            Log::warning("Sinkron Create Manual {$jenis} ke biMBA Shop gagal: " . $e->getMessage());
        }

        $query = ManualCetakOrder::where('jenis', $jenis);

        if ($request->filled('order_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_id', 'like', '%' . $request->order_id . '%')
                  ->orWhere('bimba_order_id', 'like', '%' . $request->order_id . '%')
                  ->orWhere('manual_id', 'like', '%' . $request->order_id . '%');
            });
        }
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        if ($request->filled('status_unit')) {
            $query->where('status_unit', $request->status_unit);
        }

        $orders = $query->orderByDesc('order_date')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('ordermanual::order-manual.cetak.manual-index', compact('orders', 'jenis'));
    }

    // ------------------------------------------------------------------ create

    public function manualCreate(Request $request)
    {
        $jenis  = $this->jenis($request);
        $prefix = self::PREFIX[$jenis];

        $customers = UserExportBimbaShop::query()
            ->select([
                'ID', 'user_login', 'user_email', 'display_name', 'first_name', 'last_name',
                'billing_first_name', 'billing_last_name', 'billing_company',
                'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode',
                'billing_state', 'billing_country', 'billing_phone', 'billing_email',
            ])
            ->orderBy('display_name')
            ->get();

        // Nama unit/mitra/stokis (upper) -> status aktif/pasif & no cab, untuk mengisi Status Unit otomatis
        $statusMap = [];
        foreach (UnitManualOptions::daftar() as $daftar) {
            foreach ($daftar as $u) {
                if ($u['status'] !== '') {
                    $statusMap[mb_strtoupper($u['nama'])] = ['status' => $u['status'], 'no_cab' => $u['no_cab']];
                }
            }
        }

        return view('ordermanual::order-manual.cetak.manual-create', compact('jenis', 'prefix', 'customers', 'statusMap'));
    }

    /** Autocomplete produk sesuai jenis (Kalender / Majalah). */
    public function searchProducts(Request $request)
    {
        $jenis = $this->jenis($request);
        $q     = trim((string) $request->get('q', ''));

        $query = Product::query()->select([
            'id', 'sku', 'label', 'name', 'kode', 'jenis', 'kategori', 'berat_paket', 'harga_jual',
        ])->where(function ($w) use ($jenis) {
            $w->where('kategori', 'like', "%{$jenis}%")
              ->orWhere('name', 'like', "%{$jenis}%")
              ->orWhere('jenis', 'like', "%{$jenis}%");
            if ($jenis === 'kalender') {
                $w->orWhere('label', 'like', 'KLD%')->orWhere('sku', 'like', 'KLD%');
            }
        });

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('label', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%")
                  ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        $results = $query->orderBy('name')->limit(40)->get()->map(function (Product $p) {
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
                'harga_jual' => (float) ($p->harga_jual ?? 0),
                'berat'      => (float) ($p->berat_paket ?? 0),
            ];
        })->filter()->values();

        return response()->json(['results' => $results]);
    }

    // ------------------------------------------------------------------ store

    public function manualStore(Request $request)
    {
        $jenis = $this->jenis($request);

        if ($request->filled('order_date_date')) {
            $time = $request->input('order_date_time') ?: '00:00';
            if (strlen($time) === 5) {
                $time .= ':00';
            }
            $request->merge(['order_date' => $request->order_date_date . ' ' . $time]);
        }

        if (! $request->filled('customer_name')) {
            $request->merge([
                'customer_name' => trim($request->billing_company ?: $request->billing_first_name ?: ''),
            ]);
        }

        $request->validate([
            'customer_name'        => 'required|string|max:150',
            'status_unit'          => 'required|in:aktif,pasif',
            'order_id'             => 'nullable|string|max:100',
            'order_date'           => 'nullable|date',
            'billing_last_name'    => 'nullable|string|max:50',
            'billing_first_name'   => 'nullable|string|max:100',
            'status_kirim'         => 'nullable|in:Dikirim,Diambil',
            'ekspedisi'            => 'nullable|string|max:100',
            'service_pengiriman'   => 'nullable|string|max:50',
            'phone'                => 'nullable|string|max:40',
            'catatan'              => 'nullable|string',
            'order_weight'         => 'nullable|numeric',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_sku'  => 'nullable|string|max:100',
            'items.*.qty'          => 'required|integer|min:1',
        ], [
            'customer_name.required'        => 'Customer / nama unit wajib diisi. Pilih dari dropdown Customer.',
            'status_unit.required'          => 'Status unit (Aktif / Pasif) wajib dipilih.',
            'items.required'                => 'Minimal harus ada 1 item/SKU.',
            'items.*.product_name.required' => 'Nama produk wajib diisi.',
            'items.*.qty.required'          => 'Qty wajib diisi.',
            'items.*.qty.min'               => 'Qty minimal 1.',
        ]);

        $items = collect($request->input('items', []))
            ->filter(fn ($i) => ! empty(trim($i['product_name'] ?? '')) && (int) ($i['qty'] ?? 0) > 0)
            ->values();

        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'Minimal harus ada 1 item yang valid.');
        }

        $statusKirim = $request->status_kirim ?: 'Dikirim';
        $ekspedisi   = $request->ekspedisi;
        $service     = $request->service_pengiriman;
        if ($statusKirim === 'Diambil') {
            $ekspedisi = $ekspedisi ?: 'Diambil Sendiri';
        }

        $noCab = trim((string) ($request->no_cab ?: $request->billing_last_name));

        $dibuat = 0;

        DB::beginTransaction();
        try {
            $awal = self::PREFIX[$jenis] . date('Ymd') . '-';

            $terakhir = ManualCetakOrder::where('manual_id', 'like', $awal . '%')
                ->lockForUpdate()->orderByDesc('manual_id')->value('manual_id');

            $manualId = $awal . str_pad(($terakhir ? ((int) substr($terakhir, -4)) + 1 : 1), 4, '0', STR_PAD_LEFT);

            foreach ($items as $item) {
                $product = null;
                if (! empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                }

                $sku = $product
                    ? trim((string) ($product->label ?: $product->sku ?: $product->kode))
                    : trim((string) ($item['product_sku'] ?? ''));

                $nama = trim((string) ($item['product_name'] ?? '')) ?: ($product->name ?? '');
                if ($nama === '') {
                    continue;
                }

                $harga = (float) ($item['harga_jual'] ?? 0);   // sudah total = satuan x qty (sama seperti Modul)

                ManualCetakOrder::create([
                    'manual_id'          => $manualId,
                    'jenis'              => $jenis,
                    'status_unit'        => $request->status_unit,
                    'no_cab'             => $noCab ?: null,
                    'edisi'              => KodeStokisSku::edisi($sku),
                    'kode_stokis'        => KodeStokisSku::kode($sku),
                    'order_id'           => $request->order_id,
                    'order_date'         => $request->order_date ?? now(),
                    'customer_name'      => $request->customer_name,
                    'billing_first_name' => $request->billing_first_name,
                    'billing_last_name'  => $request->billing_last_name,
                    'billing_company'    => $request->billing_company,
                    'billing_address_1'  => $request->billing_address_1,
                    'billing_address_2'  => $request->billing_address_2,
                    'billing_city'       => $request->billing_city,
                    'billing_postcode'   => $request->billing_postcode,
                    'billing_state'      => $request->billing_state,
                    'billing_kelurahan'  => $request->billing_kelurahan,
                    'billing_kecamatan'  => $request->billing_kecamatan,
                    'email'              => $request->email,
                    'phone'              => $request->phone,
                    'shipping_first_name' => $request->shipping_first_name,
                    'shipping_last_name' => $request->shipping_last_name,
                    'shipping_company'   => $request->shipping_company,
                    'shipping_address_1' => $request->shipping_address_1,
                    'shipping_address_2' => $request->shipping_address_2,
                    'shipping_city'      => $request->shipping_city,
                    'shipping_postcode'  => $request->shipping_postcode,
                    'shipping_state'     => $request->shipping_state,
                    'shipping_kelurahan' => $request->shipping_kelurahan,
                    'shipping_kecamatan' => $request->shipping_kecamatan,
                    'shipping_phone'     => $request->shipping_phone,
                    'product_id'         => $product?->id,
                    'product_sku'        => $sku ?: null,
                    'product_name'       => $nama,
                    'qty'                => (int) $item['qty'],
                    'price'              => $harga,
                    'total'              => $harga,
                    'order_weight'       => (int) ($request->order_weight ?? 0),
                    'payment_method'     => $request->payment_method ?: 'manual',
                    'status'             => 'on-hold',
                    'status_kirim'       => $statusKirim,
                    'ekspedisi'          => $ekspedisi,
                    'service_pengiriman' => $service,
                    'catatan'            => $request->catatan,
                    'created_by'         => Auth::id(),
                ]);
                $dibuat++;
            }

            if ($dibuat === 0) {
                DB::rollBack();

                return back()->withInput()->with('error', 'Tidak ada item valid yang bisa disimpan.');
            }

            DB::commit();

            // Bila sudah ada di biMBA Shop dan Kasdana, langsung processing
            $status = 'on-hold';
            try {
                app(CetakShopMatcher::class)->sync($jenis);
                $status = ManualCetakOrder::where('manual_id', $manualId)->value('status') ?: 'on-hold';
            } catch (\Throwable $e) {
                Log::warning("Sinkron Create Manual {$jenis} ke biMBA Shop gagal: " . $e->getMessage());
            }

            return redirect()
                ->route("order-manual-{$jenis}.manual")
                ->with('success', "Pesanan {$jenis} {$manualId} berhasil disimpan ({$dibuat} item), status: {$status}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Create Manual {$jenis} gagal: " . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ hapus

    /** Hapus seluruh item satu pesanan (manual_id), hanya bila belum diproses. */
    public function manualDestroy(Request $request, string $manualId)
    {
        $jenis = $this->jenis($request);

        $baris = ManualCetakOrder::where('jenis', $jenis)->where('manual_id', $manualId);

        if ((clone $baris)->where(fn ($q) => $q->where('is_processed', true)->orWhereNotNull('synced_penjualan_at'))->exists()) {
            return back()->with('error', "Pesanan {$manualId} sudah diproses / masuk Penjualan, tidak bisa dihapus.");
        }

        $baris->delete();

        return back()->with('success', "Pesanan {$manualId} dihapus.");
    }
}
