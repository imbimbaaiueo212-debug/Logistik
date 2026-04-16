<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    public function index()
    {
        $distributions = Distribution::with('warehouse')->latest()->get();
        return view('distribution.index', compact('distributions'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('distribution.create', compact('warehouses'));
    }

    public function getStock($warehouseId)
    {
        $stocks = Stock::with('product')
            ->where('warehouse_id', $warehouseId)
            ->where('qty', '>', 0)
            ->get();

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $distribution = Distribution::create([
                'warehouse_id' => $request->warehouse_id,
                'destination' => $request->destination,
                'date' => now(),
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {

                if (($item['qty'] ?? 0) <= 0) continue;

                DistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty']
                ]);
            }
        });

        return redirect()->route('distribution.index')
            ->with('success', 'Request distribusi dibuat');
    }

    public function show($id)
    {
        $distribution = Distribution::with('items.product', 'warehouse')->findOrFail($id);
        return view('distribution.show', compact('distribution'));
    }

    public function approve($id)
    {
        $distribution = Distribution::with('items')->findOrFail($id);

        if ($distribution->status != 'pending') {
            return back()->with('error', 'Sudah diproses');
        }

        DB::transaction(function () use ($distribution) {

            foreach ($distribution->items as $item) {

                $stock = Stock::where('warehouse_id', $distribution->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$stock || $stock->qty < $item->qty) {
                    throw new \Exception("Stok tidak cukup");
                }

                // 🔥 SIMPAN BEFORE
                $stockBefore = $stock->qty;

                // 🔥 KURANGI STOK
                $stock->qty -= $item->qty;
                $stock->save();

                // 🔥 AFTER
                $stockAfter = $stock->qty;

                // 🔥 SIMPAN MOVEMENT
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $distribution->warehouse_id,
                    'qty' => $item->qty,
                    'type' => 'out',
                    'reference_id' => $distribution->id,

                    // 🔥 TAMBAHKAN INI
                    'reference_type' => 'Distribution',

                    // 🔥 INI YANG KURANG
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter
                ]);
                            }

            $distribution->update([
                'status' => 'approved',
                'approved_at' => now()
            ]);
        });

        return back()->with('success', 'Distribusi disetujui');
    }

    public function reject($id)
    {
        $distribution = Distribution::findOrFail($id);

        if ($distribution->status != 'pending') {
            return back()->with('error', 'Sudah diproses');
        }

        $distribution->update(['status' => 'rejected']);

        return back()->with('success', 'Distribusi ditolak');
    }

    public function edit($id)
{
    $distribution = Distribution::with('items.product')->findOrFail($id);
    $warehouses = Warehouse::all();

    // ❌ tidak boleh edit kalau sudah approve
    if ($distribution->status != 'pending') {
        return back()->with('error', 'Distribusi sudah diproses, tidak bisa diedit');
    }

    return view('distribution.edit', compact('distribution', 'warehouses'));
}
public function update(Request $request, $id)
{
    $distribution = Distribution::with('items')->findOrFail($id);

    // ❌ tidak boleh edit kalau sudah approve
    if ($distribution->status != 'pending') {
        return back()->with('error', 'Distribusi sudah diproses');
    }

    DB::transaction(function () use ($request, $distribution) {

        // 🔥 UPDATE HEADER
        $distribution->update([
            'warehouse_id' => $request->warehouse_id,
            'destination' => $request->destination,
        ]);

        // 🔥 HAPUS ITEM LAMA
        $distribution->items()->delete();

        // 🔥 SIMPAN ULANG ITEM
        foreach ($request->items as $item) {

            if (($item['qty'] ?? 0) <= 0) continue;

            // 🔥 VALIDASI STOK
            $stock = Stock::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $item['product_id'])
                ->first();

            if (!$stock || $stock->qty < $item['qty']) {
                throw new \Exception("Stok tidak cukup saat update");
            }

            DistributionItem::create([
                'distribution_id' => $distribution->id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty']
            ]);
        }
    });

    return redirect()->route('distribution.index')
        ->with('success', 'Distribusi berhasil diupdate');
}
}