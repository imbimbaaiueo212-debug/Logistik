@extends('layouts.app')

@section('title', 'Edit Transfer')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Edit Transfer</h2>
        
        <!-- Tombol Kembali -->
        <a href="{{ route('transfer.index') }}" 
           class="px-5 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition flex items-center gap-2">
            ← Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('transfer.update', $transfer->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">Gudang Asal</label>
                <select name="from_warehouse" class="border p-3 w-full rounded-xl focus:outline-none focus:border-blue-500">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" 
                            {{ $transfer->from_warehouse_id == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Gudang Tujuan</label>
                <select name="to_warehouse" class="border p-3 w-full rounded-xl focus:outline-none focus:border-blue-500">
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" 
                            {{ $transfer->to_warehouse_id == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <table class="w-full border rounded-xl overflow-hidden mb-6">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-4 text-left">Produk</th>
                    <th class="p-4 text-left">Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($transfer->items as $i => $item)
                <tr>
                    <td class="p-4">
                        {{ $item->product->name ?? 'Produk tidak ditemukan' }}
                        <input type="hidden" 
                               name="items[{{ $i }}][product_id]" 
                               value="{{ $item->product_id }}">
                    </td>
                    <td class="p-4">
                        <input type="number" 
                               name="items[{{ $i }}][qty]" 
                               value="{{ $item->qty }}"
                               class="border p-3 w-32 rounded-xl focus:outline-none focus:border-blue-500"
                               min="1">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tombol Aksi -->
        <div class="flex gap-3">          
            <button type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-medium transition">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

@endsection