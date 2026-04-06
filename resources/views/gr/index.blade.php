@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Stok Masuk</h2>

        <a href="{{ route('gr.create') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
            + Tambah Stok Masuk
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-xl overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center">ID</th>
                    <th class="p-3 text-center">PO</th>
                    <th class="p-3 text-center">Gudang</th>
                    <th class="p-3 text-center">Tanggal</th>
                    <th class="p-3 text-center">Produk</th>          {{-- Kolom baru --}}
                    <th class="p-3 text-center">Total Qty</th>
                    <th class="p-3 text-center">Total Nilai</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($receipts as $r)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 text-center font-medium">#{{ $r->id }}</td>
                    <td class="p-3 text-center">PO #{{ $r->purchase_order_id }}</td>
                    <td class="p-3 text-center">{{ $r->warehouse->name ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $r->date->format('d M Y') ?? '-' }}</td>

                    <!-- Kolom Produk -->
                    <td class="p-3">
                        @if($r->items->isNotEmpty())
                            <div class="text-sm text-center">
                                @foreach($r->items as $item)
                                    <div>
                                        • {{ $item->product->name ?? 'Produk tidak ditemukan' }}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>

                    <td class="p-3 text-center font-medium">
                        {{ $r->items->sum('qty') }}
                    </td>

                    <td class="p-3 text-center font-medium">
                        Rp {{ number_format($r->items->sum(fn($item) => $item->qty * $item->price), 0, ',', '.') }}
                    </td>

                    <td class="p-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('gr.edit', $r->id) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                                Edit
                            </a>

                            <form action="{{ route('gr.destroy', $r->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($receipts->isEmpty())
        <div class="text-center py-10 text-gray-500">
            Belum ada data Stok Masuk
        </div>
    @endif

</div>

@endsection