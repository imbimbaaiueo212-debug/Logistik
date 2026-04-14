<?php

namespace App\Http\Controllers;

use App\Models\RejectItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class RejectController extends Controller
{
    // =========================
    // LIST REJECT
    // =========================
    public function index()
    {
        $rejects = RejectItem::with(['product', 'warehouse'])
            ->latest()
            ->get();

        return view('reject.index', compact('rejects'));
    }

    // =========================
    // RETURN KE SUPPLIER
    // =========================
    public function return($id)
    {
        $reject = RejectItem::findOrFail($id);

        DB::transaction(function () use ($reject) {

            // kurangi stok reject
            Stock::where('warehouse_id', 99)
                ->where('product_id', $reject->product_id)
                ->decrement('qty', $reject->qty);

            $reject->update([
                'status' => 'returned'
            ]);
        });

        return back()->with('success', 'Barang diretur ke supplier');
    }

    // =========================
    // SCRAP (BUANG)
    // =========================
    public function scrap($id)
    {
        $reject = RejectItem::findOrFail($id);

        DB::transaction(function () use ($reject) {

            Stock::where('warehouse_id', 99)
                ->where('product_id', $reject->product_id)
                ->decrement('qty', $reject->qty);

            $reject->update([
                'status' => 'scrapped'
            ]);
        });

        return back()->with('success', 'Barang dibuang');
    }

    // =========================
    // REPAIR (BALIK KE STOK)
    // =========================
    public function repair($id)
    {
        $reject = RejectItem::findOrFail($id);

        DB::transaction(function () use ($reject) {

            // keluar dari gudang reject
            Stock::where('warehouse_id', 99)
                ->where('product_id', $reject->product_id)
                ->decrement('qty', $reject->qty);

            // masuk ke gudang asal
            Stock::updateOrCreate(
                [
                    'warehouse_id' => $reject->warehouse_id,
                    'product_id' => $reject->product_id
                ],
                [
                    'qty' => DB::raw("qty + {$reject->qty}")
                ]
            );

            $reject->update([
                'status' => 'repaired'
            ]);
        });

        return back()->with('success', 'Barang diperbaiki & masuk stok');
    }
}