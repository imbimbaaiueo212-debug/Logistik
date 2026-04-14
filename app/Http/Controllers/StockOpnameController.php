<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with('warehouse')->latest();

        if ($request->search) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $data = $query->paginate(10);

        return view('stock_opname.index', compact('data'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('stock_opname.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        DB::transaction(function () use ($request) {

            // 🔒 LOCK warehouse
            $warehouse = Warehouse::lockForUpdate()->findOrFail($request->warehouse_id);

            if ($warehouse->is_freeze) {
                throw new \Exception('Gudang sedang opname aktif');
            }

            // 🔥 FREEZE
            $warehouse->update(['is_freeze' => true]);

            $so = StockOpname::create([
                'code' => 'SO-' . now()->format('YmdHis'),
                'warehouse_id' => $request->warehouse_id,
                'status' => StockOpname::STATUS_DRAFT,
                'snapshot_at' => now(),
                'created_by' => auth()->id(),
            ]);

            Stock::where('warehouse_id', $request->warehouse_id)
                ->chunk(100, function ($stocks) use ($so) {

                    foreach ($stocks as $stock) {
                        StockOpnameItem::create([
                            'stock_opname_id' => $so->id,
                            'product_id' => $stock->product_id,
                            'system_qty' => $stock->qty,
                            'physical_qty' => null,
                        ]);
                    }
                });
        });

        return redirect()->route('stock-opname.index')
            ->with('success', 'Stock Opname berhasil dibuat');
    }

    public function show($id)
    {
        $so = StockOpname::with('items.product')->findOrFail($id);
        return view('stock_opname.show', compact('so'));
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'physical_qty' => 'required|integer|min:0'
        ]);

        $item = StockOpnameItem::findOrFail($id);

        if (!$item->opname->isDraft()) {
            throw new \Exception('Tidak bisa edit');
        }

        $item->update([
            'physical_qty' => $request->physical_qty
        ]);

        return back()->with('success', 'Updated');
    }

    public function submit($id)
    {
        $so = StockOpname::with('items')->findOrFail($id);

        foreach ($so->items as $item) {
            if (is_null($item->physical_qty)) {
                return back()->with('error', 'Masih ada item belum dihitung');
            }
        }

        $so->submit();

        return back()->with('success', 'Disubmit');
    }

    public function approve($id)
    {
        $so = StockOpname::findOrFail($id);

        $so->approve();

        return back()->with('success', 'Approved');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $so = StockOpname::with('warehouse')->findOrFail($id);

            if ($so->warehouse->is_freeze) {
                $so->warehouse->update(['is_freeze' => false]);
            }

            $so->delete();
        });

        return redirect()->route('stock-opname.index')
            ->with('success', 'Deleted');
    }

    public function ajaxUpdateItem(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stock_opname_items,id',
            'physical_qty' => 'required|integer|min:0'
        ]);

        $item = StockOpnameItem::findOrFail($request->id);

        if (!$item->opname->isDraft()) {
            return response()->json(['error' => 'Locked'], 400);
        }

        $item->update([
            'physical_qty' => $request->physical_qty
        ]);

        return response()->json(['success' => true]);
    }
}