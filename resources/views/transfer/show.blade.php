@extends('layouts.app')

@section('title', 'Detail Transfer')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4">Detail Transfer #{{ $transfer->id }}</h2>

    <p><b>Dari:</b> {{ $transfer->fromWarehouse->name }}</p>
    <p><b>Ke:</b> {{ $transfer->toWarehouse->name }}</p>
    <p><b>Tanggal:</b> {{ $transfer->date }}</p>

    <table class="w-full border mt-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Produk</th>
                <th class="p-2">Qty</th>
            </tr>
        </thead>

        <tbody>
            @foreach($transfer->items as $item)
            <tr class="border-t text-center">
                <td class="p-2">{{ $item->product->name }}</td>
                <td class="p-2">{{ $item->qty }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('transfer.index') }}"
       class="bg-gray-500 text-white px-4 py-2 mt-4 inline-block rounded">
        ← Kembali
    </a>

</div>

@endsection