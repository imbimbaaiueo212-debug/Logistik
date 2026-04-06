@extends('layouts.app')

@section('title', 'Detail PO')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4">Purchase Order #{{ $po->id }}</h2>

    <p><b>Supplier:</b> {{ $po->supplier->nama_supplier }}</p>
    <p><b>Tanggal:</b> {{ $po->date }}</p>

    <table class="w-full border mt-4">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Product</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Subtotal</th>
                <th class="p-2">Qty PO</th>
                <th class="p-2">Diterima</th>
                <th class="p-2">Sisa</th>
            </tr>
        </thead>

        <tbody>
            @foreach($po->items as $item)
            <tr class="border-t">
                <td class="p-2">{{ $item->product->name }}</td>
                <td class="p-2">{{ $item->qty }}</td>
                <td class="p-2">Rp {{ number_format($item->price) }}</td>
                <td class="p-2">Rp {{ number_format($item->subtotal) }}</td>
                <td class="p-2">{{ $item->qty }}</td>
                <td class="p-2">{{ $item->received_qty }}</td>
                <td class="p-2">{{ $item->sisa }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 class="text-right mt-4 text-lg font-bold">
        Total: Rp {{ number_format($po->total) }}
    </h3>
    <a href="{{ url()->previous() }}" 
   class="bg-gray-500 text-white px-4 py-2 rounded-lg">
    ← Kembali
</a>

</div>

@endsection