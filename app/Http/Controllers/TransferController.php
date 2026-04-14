<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Services\StockService;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index()
{
    $transfers = Transfer::with(['fromWarehouse', 'toWarehouse'])
        ->latest()
        ->get();

    return view('transfer.index', compact('transfers'));
}
    // HALAMAN FORM
    public function create()
    {
        $warehouses = Warehouse::all();
        return view('transfer.create', compact('warehouses'));
    }

    // 🔥 AJAX ambil stok
    public function getStockByWarehouse($id)
    {
        $stocks = Stock::with('product')
            ->where('warehouse_id', $id)
            ->where('qty', '>', 0)
            ->get();

        return response()->json($stocks);
    }

    // 🔥 SIMPAN TRANSFER
    public function store(Request $request)
{
    if ($request->from_warehouse == $request->to_warehouse) {
        return back()->with('error', 'Gudang tidak boleh sama!');
    }

    DB::beginTransaction();

    try {
        $transfer = Transfer::create([
            'from_warehouse_id' => $request->from_warehouse,
            'to_warehouse_id'   => $request->to_warehouse,
            'date'              => now(),
            'status'            => 'pending'
        ]);

        foreach ($request->items as $item) {

            if (($item['qty'] ?? 0) <= 0) continue;

            TransferItem::create([
                'transfer_id' => $transfer->id,
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty']
            ]);
        }

        DB::commit();

        return redirect()->route('transfer.index')
            ->with('success', 'Request transfer dibuat (Menunggu Approval)');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage());
    }
}

    public function edit($id)
{
    $transfer = Transfer::with('items.product')->findOrFail($id);
    $warehouses = Warehouse::all();

    return view('transfer.edit', compact('transfer', 'warehouses'));
}

public function update(Request $request, $id)
{
    $transfer = Transfer::with('items')->findOrFail($id);

    // 🔥 BALIKKAN STOK LAMA
    foreach ($transfer->items as $item) {

        // tambah ke asal
        $stockFrom = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $transfer->from_warehouse_id)
            ->first();

        if ($stockFrom) {
            $stockFrom->qty += $item->qty;
            $stockFrom->save();
        }

        // kurangi dari tujuan
        $stockTo = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->first();

        if ($stockTo) {
            $stockTo->qty -= $item->qty;
            $stockTo->save();
        }
    }

    // 🔥 HAPUS ITEM LAMA
    $transfer->items()->delete();

    // 🔥 UPDATE HEADER
    $transfer->update([
        'from_warehouse_id' => $request->from_warehouse,
        'to_warehouse_id'   => $request->to_warehouse,
        'date'              => now()
    ]);

    // 🔥 SIMPAN ULANG (LOGIC SAMA DENGAN STORE)
    foreach ($request->items as $item) {

        if (($item['qty'] ?? 0) <= 0) continue;

        $stockFrom = Stock::where('product_id', $item['product_id'])
            ->where('warehouse_id', $request->from_warehouse)
            ->first();

        if (!$stockFrom || $stockFrom->qty < $item['qty']) {
            return back()->with('error', 'Stok tidak cukup!');
        }

        $stockFrom->qty -= $item['qty'];
        $stockFrom->save();

        $stockTo = Stock::firstOrCreate(
            [
                'product_id'   => $item['product_id'],
                'warehouse_id' => $request->to_warehouse
            ],
            ['qty' => 0]
        );

        $stockTo->qty += $item['qty'];
        $stockTo->save();

        TransferItem::create([
            'transfer_id' => $transfer->id,
            'product_id'  => $item['product_id'],
            'qty'         => $item['qty']
        ]);
    }

    return redirect()->route('transfer.index')->with('success', 'Transfer berhasil diupdate');
}

public function destroy($id)
{
    $transfer = Transfer::with('items')->findOrFail($id);

    foreach ($transfer->items as $item) {

        // 🔥 BALIKKAN STOK
        $stockFrom = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $transfer->from_warehouse_id)
            ->first();

        if ($stockFrom) {
            $stockFrom->qty += $item->qty;
            $stockFrom->save();
        }

        $stockTo = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->first();

        if ($stockTo) {
            $stockTo->qty -= $item->qty;
            $stockTo->save();
        }
    }

    $transfer->items()->delete();
    $transfer->delete();

    return redirect()->route('transfer.index')->with('success', 'Transfer dihapus');
}
public function show($id)
{
    $transfer = \App\Models\Transfer::with([
        'items.product',
        'fromWarehouse',
        'toWarehouse'
    ])->findOrFail($id);

    return view('transfer.show', compact('transfer'));
}

public function approve($id, \App\Services\StockService $stockService)
{
    $transfer = Transfer::with('items')->findOrFail($id);

    // ❌ jangan approve dua kali
    if ($transfer->status != 'pending') {
        return back()->with('error', 'Transfer sudah diproses');
    }

    DB::beginTransaction();

    try {

        foreach ($transfer->items as $item) {

            // 🔥 VALIDASI STOK
            $stock = \App\Models\Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->first();

            if (!$stock || $stock->qty < $item->qty) {
                throw new \Exception("Stok tidak cukup untuk produk ID {$item->product_id}");
            }

            // 🔥 PINDAHKAN STOK
            $stockService->transfer(
                $item->product_id,
                $transfer->from_warehouse_id,
                $transfer->to_warehouse_id,
                $item->qty,
                $transfer->id
            );
        }

        // 🔥 UPDATE STATUS
        $transfer->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        DB::commit();

        return back()->with('success', 'Transfer berhasil di-approve');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage());
    }
}
public function reject($id)
{
    $transfer = Transfer::findOrFail($id);

    if ($transfer->status != 'pending') {
        return back()->with('error', 'Sudah diproses');
    }

    $transfer->update([
        'status' => 'rejected'
    ]);

    return back()->with('success', 'Transfer ditolak');
}
}