<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class CheckWarehouseFreeze
{
    public function handle(Request $request, Closure $next)
    {
        // ambil warehouse_id dari request
        $warehouseId = $request->warehouse_id ?? $request->input('warehouse_id');

        // ❗ fleksibel: support route param juga
        if (!$warehouseId && $request->route('warehouse_id')) {
            $warehouseId = $request->route('warehouse_id');
        }

        if ($warehouseId) {

            $warehouse = Warehouse::find($warehouseId);

            if ($warehouse && $warehouse->is_freeze) {
                return response()->json([
                    'message' => 'Gudang sedang opname, transaksi dikunci'
                ], 403);
            }
        }

        return $next($request);
    }
}