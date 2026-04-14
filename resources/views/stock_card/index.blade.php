@extends('layouts.app')

@section('title', 'Kartu Stok')

@section('content')

<div class="space-y-6">

    {{-- 🔍 FILTER --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Kartu Stok</h3>

        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <select name="product_id" class="border rounded-lg px-3 py-2">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                <select name="warehouse_id" class="border rounded-lg px-3 py-2">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-3 py-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-3 py-2">

            </div>

            <div class="mt-3 flex justify-end gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('stock-card.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    {{-- 📊 TABLE --}}
    <div class="bg-white rounded-2xl shadow p-6">

        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-center">Tanggal</th>
                        <th class="p-3 text-center">Produk</th>
                        <th class="p-3 text-center">Gudang</th>
                        <th class="p-3 text-center">Type</th>
                        <th class="p-3 text-center">Masuk</th>
                        <th class="p-3 text-center">Keluar</th>
                        <th class="p-3 text-center">Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $m)
                    <tr class="border-t text-center">

                        <td class="p-3">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="p-3 font-semibold">
                            {{ $m->product->name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $m->warehouse->name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ \App\Enums\StockMovementType::label($m->type) }}
                        </td>

                        {{-- MASUK --}}
                        <td class="text-green-600 font-bold">
                            {{ $m->qty_masuk ?: '-' }}
                        </td>

                        {{-- KELUAR --}}
                        <td class="text-red-600 font-bold">
                            {{ $m->qty_keluar ?: '-' }}
                        </td>
                        {{-- SALDO --}}
                        <td class="p-3 font-bold">
                            {{ $m->running_balance }}
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500">
                            Tidak ada data
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection