<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with([
            'product',
            'warehouse',
            'user'
        ]);

        // 🔍 FILTER PRODUK
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // 🔍 FILTER GUDANG
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // 🔍 FILTER TYPE
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 🔍 FILTER TANGGAL
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 🔥 ORDER + PAGINATION
        $movements = $query->orderBy('created_at', 'desc')->paginate(20);

        // 🔥 FORMAT DATA UNTUK VIEW (BIAR GA RIBET DI BLADE)
        $movements->getCollection()->transform(function ($m) {

            // Tentukan masuk / keluar dari type
            if (in_array($m->type, ['gr', 'transfer_in'])) {
                $m->qty_masuk = $m->qty;
                $m->qty_keluar = 0;
            } elseif (in_array($m->type, ['transfer_out'])) {
                $m->qty_masuk = 0;
                $m->qty_keluar = $m->qty;
            } else {
                $m->qty_masuk = 0;
                $m->qty_keluar = 0;
            }

            return $m;
        });

        // dropdown
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('stock_movements.index', compact(
            'movements',
            'products',
            'warehouses'
        ));
    }

    /**
     * DETAIL (AUDIT)
     */
    public function show($id)
    {
        $movement = StockMovement::with([
            'product',
            'warehouse',
            'user'
        ])->findOrFail($id);

        // 🔥 FORMAT DETAIL
        if (in_array($movement->type, ['gr', 'transfer_in'])) {
            $movement->qty_masuk = $movement->qty;
            $movement->qty_keluar = 0;
        } elseif ($movement->type === 'transfer_out') {
            $movement->qty_masuk = 0;
            $movement->qty_keluar = $movement->qty;
        }

        return view('stock_movements.show', compact('movement'));
    }
}