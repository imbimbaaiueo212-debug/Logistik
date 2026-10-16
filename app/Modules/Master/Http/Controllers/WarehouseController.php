<?php

namespace App\Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Master\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::latest()->get();
        return view('master::warehouse.index', compact('warehouses'));
    }

    public function create()
    {
        return view('master::warehouse.create');
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
        return view('master::warehouse.edit', compact('warehouse'));
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