@extends('layouts.app')

@section('title', 'Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Distribusi</h2>
        <a href="{{ route('distribution.create') }}"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            + Buat Distribusi
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Gudang</th>
                <th class="p-2">Tujuan</th>
                <th class="p-2">Tanggal</th>
                <th class="p-2">Item</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($distributions as $d)
            <tr class="border-t text-center">
                <td>{{ $d->warehouse->name ?? '-' }}</td>
                <td>{{ $d->destination }}</td>
                <td>{{ $d->date }}</td>
                <td>{{ $d->items->pluck('product.name')->filter()->join(', ') }}</td>
                <td>{{ $d->items->sum('qty') }}</td>
                <td>
                    <span class="px-2 py-1 rounded text-white
                        {{ $d->status == 'pending' ? 'bg-yellow-500' : '' }}
                        {{ $d->status == 'approved' ? 'bg-green-500' : '' }}
                        {{ $d->status == 'rejected' ? 'bg-red-500' : '' }}">
                        {{ $d->status }}
                    </span>
                </td>
                <td class="flex gap-2 justify-center">

                    <a href="{{ route('distribution.edit', $d->id) }}"
                       class="bg-blue-500 text-white px-2 py-1 rounded text-sm">
                        Edit
                    </a>

                    @if($d->status == 'pending')
                    <form method="POST" action="{{ route('distribution.approve', $d->id) }}">
                        @csrf
                        <button class="bg-green-500 text-white px-2 py-1 rounded text-sm">
                            Approve
                        </button>
                    </form>

                    <form method="POST" action="{{ route('distribution.reject', $d->id) }}">
                        @csrf
                        <button class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                            Reject
                        </button>
                    </form>
                    @endif

                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center p-4">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection