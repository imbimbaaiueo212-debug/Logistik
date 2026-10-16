<?php

namespace App\Modules\Stok\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Stok\Models\Stock;
use App\Modules\Master\Models\Warehouse;
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

        return view('stok::stock.index', compact('stocks', 'warehouses'));
    }
}