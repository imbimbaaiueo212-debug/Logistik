<?php

namespace App\Modules\Order\Http\Controllers\Concerns;

use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Order\Models\RealisasiAktif;
use App\Modules\Picking\Models\Picking;          // Tambahkan
use App\Modules\Picking\Models\PickingItem;      // Tambahkan
use App\Modules\Master\Models\Product;
use Illuminate\Support\Facades\Auth;   // Tambahkan
use Carbon\Carbon;

trait AktifPickingHelpers
{


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
}
