@extends('layouts.app')

@section('title', 'Stock Movement')

@section('content')

<div class="space-y-6">

    {{-- 🔍 FILTER PANEL --}}
    <div class="bg-white rounded-2xl shadow p-6">

        <h3 class="text-lg font-semibold mb-4">🔍 Filter Stock Movement</h3>

        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                {{-- PRODUK --}}
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Produk</label>
                    <select name="product_id" class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
                        <option value="">Semua Produk</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- GUDANG --}}
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Gudang</label>
                    <select name="warehouse_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TYPE --}}
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Type</label>
                    <select name="type" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Semua</option>
                        <option value="IN" {{ request('type')=='IN'?'selected':'' }}>IN</option>
                        <option value="OUT" {{ request('type')=='OUT'?'selected':'' }}>OUT</option>
                        <option value="TRANSFER_IN" {{ request('type')=='TRANSFER_IN'?'selected':'' }}>TRANSFER IN</option>
                        <option value="TRANSFER_OUT" {{ request('type')=='TRANSFER_OUT'?'selected':'' }}>TRANSFER OUT</option>
                        <option value="ADJUSTMENT" {{ request('type')=='ADJUSTMENT'?'selected':'' }}>ADJUSTMENT</option>
                        <option value="RETURN" {{ request('type')=='RETURN'?'selected':'' }}>RETURN</option>
                    </select>
                </div>

                {{-- DARI --}}
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Dari</label>
                    <input type="date" name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- SAMPAI --}}
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full border rounded-lg px-3 py-2">
                </div>

            </div>

            {{-- BUTTON --}}
            <div class="flex justify-between items-center mt-4">

                {{-- INFO FILTER --}}
                <div class="text-sm text-gray-500">
                    @if(request()->hasAny(['product_id','warehouse_id','type','date_from','date_to']))
                        <span class="text-blue-600 font-medium">Filter aktif</span>
                    @else
                        <span>Tampilkan semua data</span>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        🔍 Filter
                    </button>

                    <a href="{{ route('stock-movements.index') }}" 
                       class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Reset
                    </a>
                </div>

            </div>
        </form>

    </div>

    {{-- 📊 TABLE --}}
    <div class="bg-white rounded-2xl shadow p-6">

        <h3 class="text-lg font-semibold mb-4">📊 Data Stock Movement</h3>

        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-center">Tanggal</th>
                        <th class="p-3 text-center">Produk</th>
                        <th class="p-3 text-center">Gudang</th>
                        <th class="p-3 text-center">Type</th>
                        <th class="p-3 text-center">Qty</th>
                        <th class="p-3 text-center">Before</th>
                        <th class="p-3 text-center">After</th>
                        <th class="p-3 text-center">Referensi</th>
                        <th class="p-3 text-center">User</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $m)
                    <tr class="border-t text-center hover:bg-gray-50">

                        {{-- TANGGAL --}}
                        <td class="p-3">
                            {{ $m->created_at->format('d/m/Y') }}<br>
                            <span class="text-xs text-gray-500">
                                {{ $m->created_at->format('H:i') }}
                            </span>
                        </td>

                        {{-- PRODUK --}}
                        <td class="p-3 font-semibold">
                            {{ $m->product->name ?? '-' }}
                        </td>

                        {{-- GUDANG --}}
                        <td class="p-3">
                            {{ $m->warehouse->name ?? '-' }}
                        </td>

                        {{-- TYPE --}}
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded bg-gray-200">
                                {{ \App\Enums\StockMovementType::label($m->type) }}
                            </span>
                        </td>

                        {{-- QTY --}}
                        <td class="p-3 font-bold {{ $m->qty > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}
                        </td>

                        {{-- BEFORE --}}
                        <td class="p-3 text-gray-500">
                            {{ $m->stock_before }}
                        </td>

                        {{-- AFTER --}}
                        <td class="p-3 font-semibold">
                            {{ $m->stock_after }}
                        </td>

                        {{-- REFERENSI --}}
                        <td class="p-3">
                            {{ $m->reference_type ?? '-' }} <br>
                            <span class="text-xs text-gray-500">#{{ $m->reference_id }}</span>
                        </td>

                        {{-- USER --}}
                        <td class="p-3">
                            {{ $m->user->name ?? '-' }}
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-500">
                            🚫 Tidak ada data stock movement
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 flex justify-end">
            {{ $movements->withQueryString()->links() }}
        </div>

    </div>

</div>

@endsection