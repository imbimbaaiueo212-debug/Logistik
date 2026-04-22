@extends('layouts.app')

@section('title', 'Quotation')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Quotation</h2>

        <a href="{{ route('quotation.create') }}"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            + Buat Quotation
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">No</th>
                <th class="p-2">Supplier</th>
                <th class="p-2">Tanggal</th>
                <th class="p-2">Items</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Total</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($quotations as $q)
            <tr class="border-t text-center">
                <td>#{{ $q->id }}</td>
                <td>
                    {{ $q->suppliers->pluck('supplier.name')->join(', ') }}
                </td>
                <td>{{ \Carbon\Carbon::parse($q->date)->format('d-m-Y') }}</td>
                <td>
                    {{ $q->items->pluck('product.name')->filter()->join(', ') }}
                </td>
                <td>{{ $q->items->sum('qty') }}</td>
                <td>Rp {{ number_format($q->total, 0, ',', '.') }}</td>
                <td>
                    <span class="px-2 py-1 rounded text-white
                        {{ $q->status == 'draft' ? 'bg-gray-500' : '' }}
                        {{ $q->status == 'sent' ? 'bg-blue-500' : '' }}
                        {{ $q->status == 'approved' ? 'bg-green-500' : '' }}
                         {{ $q->status == 'created PO' ? 'bg-red-500' : '' }}">
                        {{ $q->status }}
                    </span>
                </td>
                <td class="flex gap-2 justify-center flex-wrap">

    {{-- DETAIL --}}
    <a href="{{ route('quotation.show', $q->id) }}"
       class="bg-gray-500 text-white px-2 py-1 rounded text-sm">
        Detail
    </a>

    {{-- EDIT --}}
    <a href="{{ route('quotation.edit', $q->id) }}"
       class="bg-blue-500 text-white px-2 py-1 rounded text-sm">
        Edit
    </a>

    {{-- DRAFT → KIRIM --}}
    @if($q->status == 'draft')
        <form method="POST" action="{{ route('quotation.send', $q->id) }}">
            @csrf
            <button class="bg-green-500 text-white px-2 py-1 rounded text-sm">
                Kirim
            </button>
        </form>
    @endif

    {{-- SENT → APPROVE / REJECT --}}
    @if($q->status == 'sent')
        <form method="POST" action="{{ route('quotation.approve', $q->id) }}">
            @csrf
            <button class="bg-emerald-600 text-white px-2 py-1 rounded text-sm">
                Approve
            </button>
        </form>

        <form method="POST" action="{{ route('quotation.reject', $q->id) }}">
            @csrf
            <button class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                Reject
            </button>
        </form>
    @endif

    {{-- APPROVED → CONVERT PO 🔥 --}}
    @if($q->status == 'approved')
        <form method="POST"
              action="{{ route('quotation.convert.po', [$q->id, $q->suppliers->first()->supplier_id]) }}">
            @csrf
            <button class="bg-purple-600 text-white px-2 py-1 rounded text-sm">
                Convert PO
            </button>
        </form>
    @endif

</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center p-4">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection