<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturnDistribution;
use App\Models\ReturnDistributionItem;
use App\Models\Distribution;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ReturnDistributionController extends Controller
{
    public function index()
    {
        $returns = ReturnDistribution::with('warehouse')->latest()->get();
        return view('return_distribution.index', compact('returns'));
    }

    public function create()
    {
        $distributions = Distribution::where('status', 'approved')->get();
        return view('return_distribution.create', compact('distributions'));
    }

    public function getItems($distributionId)
    {
        $items = Distribution::with('items.product')
            ->findOrFail($distributionId)
            ->items;

        return response()->json($items);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $return = ReturnDistribution::create([
                'distribution_id' => $request->distribution_id,
                'warehouse_id' => $request->warehouse_id,
                'date' => now(),
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {

                if (($item['qty'] ?? 0) <= 0) continue;

                ReturnDistributionItem::create([
                    'return_distribution_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'reason' => $item['reason'] ?? null
                ]);
            }
        });

        return redirect()->route('return-distribution.index')
            ->with('success', 'Return dibuat');
    }

   public function approve($id)
{
    $return = ReturnDistribution::with('items')->findOrFail($id);

    if ($return->status != 'pending') {
        return back()->with('error', 'Sudah diproses');
    }

    DB::transaction(function () use ($return) {

        // 🔥 AMBIL / BUAT GUDANG REJECT (ANTI ERROR)
        $rejectWarehouse = Warehouse::whereRaw('LOWER(code) = ?', ['reject'])->first();

        if (!$rejectWarehouse) {
            $rejectWarehouse = Warehouse::create([
                'name' => 'Gudang Reject',
                'code' => 'REJECT',
                'city' => 'Jakarta'
            ]);
        }

        foreach ($return->items as $item) {

            // =========================
            // 🔥 CEK REJECT ATAU TIDAK
            // =========================
            $isReject = !empty($item->reason);

            $warehouseId = $isReject
                ? $rejectWarehouse->id
                : $return->warehouse_id;

            // =========================
            // 🔥 STOCK UPDATE
            // =========================
            $stock = Stock::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $item->product_id
                ],
                ['qty' => 0]
            );

            $before = $stock->qty;

            $stock->qty += $item->qty;
            $stock->save();

            $after = $stock->qty;

            // =========================
            // 🔥 STOCK MOVEMENT
            // =========================
            StockMovement::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $warehouseId,
                'qty' => $item->qty,
                'type' => 'IN',
                'reference_id' => $return->id,
                'reference_type' => 'return_distribution',
                'stock_before' => $before,
                'stock_after' => $after
            ]);
        }

        $return->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);
    });

    return back()->with('success', 'Return disetujui');
}

    public function edit($id)
{
    $return = ReturnDistribution::with('items.product')->findOrFail($id);

    if ($return->status != 'pending') {
        return back()->with('error', 'Tidak bisa edit');
    }

    $distributions = Distribution::all();
    $warehouses = Warehouse::all();

    return view('return_distribution.edit', compact('return','distributions','warehouses'));
}

    public function update(Request $request, $id)
    {
        $return = ReturnDistribution::with('items')->findOrFail($id);

        if ($return->status != 'pending') {
            return back()->with('error', 'Sudah diproses');
        }

        DB::transaction(function () use ($request, $return) {

            $return->update([
                'distribution_id' => $request->distribution_id,
                'warehouse_id' => $request->warehouse_id
            ]);

            $return->items()->delete();

            foreach ($request->items as $item) {

                if (($item['qty'] ?? 0) <= 0) continue;

                ReturnDistributionItem::create([
                    'return_distribution_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'reason' => $item['reason'] ?? null
                ]);
            }
        });

        return redirect()->route('return-distribution.index')
            ->with('success', 'Return diupdate');
    }
}