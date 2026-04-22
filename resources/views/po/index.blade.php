@extends('layouts.app')

@section('title', 'Purchase Order')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between mb-6">
        <h3 class="text-xl font-semibold">Purchase Order</h3>

        <a href="{{ route('po.create') }}" 
           class="bg-blue-500 text-white px-4 py-2 rounded-lg">
            + Buat PO
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Supplier</th>
                <th class="p-3">Tanggal</th>
                <th class="p-3">Items</th>
                <th class="p-3">Qty</th>
                <th class="p-3">Total</th>
                <th class="p-3">Status</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pos as $po)
            <tr class="border-t text-center">
                <td class="p-3">#{{ $po->id }}</td>
                <td class="p-3">{{ $po->supplier->name ?? '-' }}</td>
                <td class="p-3">
                   {{ $po->created_at?->format('d/m/Y') }}
                </td>
                <td class="p-3">
                    {{ $po->items->pluck('product.name')->filter()->join(', ') }}
                </td>
                <td class="p-3">
                    {{ $po->items->sum('qty') }}
                </td>
                <td class="p-3">Rp {{ number_format($po->total) }}</td>
                <td>
                    <span class="
                    @if($po->status == 'Draft') bg-gray-400
                    @elseif($po->status == 'Partial') bg-yellow-500
                    @elseif($po->status == 'Completed') bg-green-500
                    @else bg-red-500
                    @endif
                    text-white px-3 py-1 rounded">
                    {{ $po->status }}
                </span>
                </td>
                <td class="p-3">
                    <a href="{{ route('po.edit', $po) }}" 
                       class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </a>
                    <a href="{{ route('po.show', $po->id) }}" 
                        class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        Detail
                    </a>
                    
                    <form action="{{ route('po.destroy', $po) }}" method="POST" 
                          class="inline" onsubmit="return confirm('Yakin hapus PO ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center p-4">
                    Belum ada data PO
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection