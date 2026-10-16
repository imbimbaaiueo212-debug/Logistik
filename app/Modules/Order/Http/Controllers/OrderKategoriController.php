<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Master\Models\Product;
use Illuminate\Http\Request;

class OrderKategoriController extends Controller
{



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

    return view('order::order.jakarta-aktif-index', compact('data'));
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

    return view('order::order.jakarta-aktif-index', compact('data'));
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

    return view('order::order.jakarta-aktif-index', compact('data'));
}
}
