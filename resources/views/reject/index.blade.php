@extends('layouts.app')

@section('title', 'Reject Management')

@section('content')

<h2 class="text-xl font-bold mb-4">Reject Management</h2>

@if(session('success'))
    <div class="bg-green-200 p-2 mb-3">
        {{ session('success') }}
    </div>
@endif

<table class="w-full border">
    <thead>
        <tr class="bg-gray-200">
            <th class="p-2">Produk</th>
            <th class="p-2">Gudang</th>
            <th class="p-2">Qty</th>
            <th class="p-2">Reason</th>
            <th class="p-2">Status</th>
            <th class="p-2">Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rejects as $r)
        <tr class="border-t">
            <td class="p-2">{{ $r->product->name }}</td>
            <td class="p-2">{{ $r->warehouse->name }}</td>
            <td class="p-2">{{ $r->qty }}</td>
            <td class="p-2">{{ $r->reason }}</td>
            <td class="p-2">{{ $r->status }}</td>
            <td class="p-2">

                @if($r->status == 'pending')

                    <form action="{{ route('reject.return', $r->id) }}" method="POST" class="inline">
                        @csrf
                        <button class="bg-blue-500 text-white px-2 py-1">Return</button>
                    </form>

                    <form action="{{ route('reject.scrap', $r->id) }}" method="POST" class="inline">
                        @csrf
                        <button class="bg-red-500 text-white px-2 py-1">Scrap</button>
                    </form>

                    <form action="{{ route('reject.repair', $r->id) }}" method="POST" class="inline">
                        @csrf
                        <button class="bg-green-500 text-white px-2 py-1">Repair</button>
                    </form>

                @endif

            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection