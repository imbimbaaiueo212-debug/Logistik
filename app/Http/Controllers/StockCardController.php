<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;

class StockCardController extends Controller
{
   public function index(Request $request)
{
    $products = Product::all();
    $warehouses = Warehouse::all();

    $query = StockMovement::with(['product', 'warehouse'])
        ->orderBy('created_at', 'asc');

    if ($request->product_id) {
        $query->where('product_id', $request->product_id);
    }

    if ($request->warehouse_id) {
        $query->where('warehouse_id', $request->warehouse_id);
    }

    if ($request->date_from) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->date_to) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    $movements = $query->get();

    // 🔥 TIDAK ADA LAGI RUNNING BALANCE MANUAL

    foreach ($movements as $m) {

    if (in_array($m->type, ['IN', 'TRANSFER_IN'])) {
        $m->qty_masuk = abs($m->qty);
        $m->qty_keluar = 0;
    } else {
        $m->qty_masuk = 0;
        $m->qty_keluar = abs($m->qty);
    }

    $m->running_balance = $m->stock_after;
}

    return view('stock_card.index', compact(
        'movements',
        'products',
        'warehouses'
    ));
}
}