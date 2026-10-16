@extends('layouts.app')

@section('title', 'Return Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Return Distribusi</h2>

        <a href="{{ route('return-distribution.create') }}"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            + Buat Return
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Distribusi</th>
                <th class="p-2">Stokis</th>
                <th class="p-2">Tujuan</th>
                <th class="p-2">Tanggal</th>
                <th class="p-2">Keterangan</th>
                <th class="p-2">Item</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($returns as $r)
            <tr class="border-t text-center">
                <td>#{{ $r->distribution_id }}</td>
                <td>{{ $r->distribution->destination ?? '-' }}</td>
                <td>{{ $r->warehouse->name ?? '-' }}</td>
                <td>{{ $r->date }}</td>
                <td>
                    @if($r->items->count())
                        {{ $r->items->pluck('reason')->filter()->join(', ') ?: '-' }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $r->items->pluck('product.name')->filter()->join(', ') }}</td>
                <td>{{ $r->items->sum('qty') }}</td>
                <td>
                    <span class="px-2 py-1 rounded text-white
                        {{ $r->status == 'pending' ? 'bg-yellow-500' : '' }}
                        {{ $r->status == 'approved' ? 'bg-green-500' : '' }}">
                        {{ $r->status }}
                    </span>
                </td>
                <td class="flex gap-2 justify-center">

                    <a href="{{ route('return-distribution.edit', $r->id) }}"
                       class="bg-blue-500 text-white px-2 py-1 rounded text-sm">
                        Edit
                    </a>

                    @if($r->status == 'pending')
                    <form method="POST" action="{{ route('return-distribution.approve', $r->id) }}">
                        @csrf
                        <button class="bg-green-500 text-white px-2 py-1 rounded text-sm">
                            Approve
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