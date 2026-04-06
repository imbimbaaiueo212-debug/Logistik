@extends('layouts.app')

@section('title', 'Stok Gudang')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Stok per Gudang</h2>
    </div>

    <!-- FILTER GUDANG -->
    <form method="GET" class="mb-4">
        <select name="warehouse_id" class="border p-2 rounded" onchange="this.form.submit()">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $w)
                <option value="{{ $w->id }}" 
                    {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                    {{ $w->name }}
                </option>
            @endforeach
        </select>
    </form>

    <!-- TABLE -->
    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Gudang</th>
                <th class="p-2">Produk</th>
                <th class="p-2">Kode</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Label</th>
            </tr>
        </thead>

        <tbody>
            @forelse($stocks as $s)
            <tr class="border-t text-center">
                <td class="p-2">{{ $s->warehouse->name }}</td>
                <td class="p-2">{{ $s->product->name }}</td>
                <td class="p-2">{{ $s->product->sku ?? '-' }}</td>
                <td class="p-2 font-bold">{{ $s->qty }}</td>
                <td class="p-2 text-blue-600 font-semibold">
                    {{ $s->label }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">
                    Belum ada data stok
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection