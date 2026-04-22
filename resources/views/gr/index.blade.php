@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Stok Masuk</h2>

        <a href="{{ route('gr.create') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
            + Tambah Stok Masuk
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center">ID</th>
                    <th class="p-3 text-center">PO</th>
                    <th class="p-3 text-center">Gudang</th>
                    <th class="p-3 text-center">Tanggal</th>
                    <th class="p-3 text-center">Produk</th>
                    <th class="p-3 text-center">Total</th>
                    <th class="p-3 text-center">OK</th>
                    <th class="p-3 text-center">Reject</th>
                    <th class="p-3 text-center">QC</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($receipts as $r)
                <tr class="border-b">

                    <td class="p-3 text-center">#{{ $r->id }}</td>
                    <td class="p-3 text-center">PO #{{ $r->purchase_order_id }}</td>
                    <td class="p-3 text-center">{{ $r->warehouse->name ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $r->date->format('d-m-Y') }}</td>

                    {{-- PRODUK --}}
                    <td class="p-3 text-sm">
                        @foreach($r->items as $item)
                            <div>• {{ $item->product->name }}</div>
                        @endforeach
                    </td>

                    {{-- TOTAL --}}
                    <td class="text-center">
                        {{ $r->items->sum('qty_received') }}
                    </td>

                    {{-- OK --}}
                    <td class="text-center text-green-600 font-bold">
                        {{ $r->items->sum('qty_ok') }}
                    </td>

                    {{-- REJECT --}}
                    <td class="text-center text-red-600 font-bold">
                        {{ $r->items->sum('qty_reject') }}
                    </td>

                    {{-- STATUS QC --}}
                    <td class="text-center">
                        @if($r->items->every(fn($i) => $i->isQcDone()))
                            <span class="bg-green-200 text-green-800 px-2 py-1 rounded text-xs">
                                QC Done
                            </span>
                        @else
                            <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs">
                                Belum QC
                            </span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center space-x-1">

                        <a href="{{ route('gr.edit', $r->id) }}" 
                           class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                            Edit
                        </a>

                        {{-- 🔥 TOMBOL QC --}}
                        @if($r->items->contains(fn($i) => !$i->isQcDone()))
                        <a href="{{ route('gr.qc.page', $r->id) }}"
                           class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                            QC
                        </a>
                        @endif

                        <form action="{{ route('gr.destroy', $r->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">
                                Hapus
                            </button>
                        </form>

                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection