<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::latest()->get();
        return view('warehouse.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouse.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        Warehouse::create($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan');
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('warehouse.edit', compact('warehouse'));
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->update($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil diupdate');
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil dihapus');
    }
}