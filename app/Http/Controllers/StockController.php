<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $stocks = Stock::with(['product', 'warehouse'])
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->get();

        $warehouses = Warehouse::all();

        return view('stock.index', compact('stocks', 'warehouses'));
    }
}