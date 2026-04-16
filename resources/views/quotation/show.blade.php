@extends('layouts.app')

@section('title', 'Detail Quotation')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

   <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">
        Quotation #{{ $quotation->id }}
    </h2>

    <a href="{{ route('quotation.index') }}"
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        ← Back
    </a>
</div>

    <div class="mb-4">
        <p><strong>Customer:</strong> {{ $quotation->customer_name }}</p>
        <p><strong>Tanggal:</strong> {{ $quotation->date }}</p>
        <p><strong>Status:</strong> {{ $quotation->status }}</p>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Produk</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @foreach($quotation->items as $i)
            <tr class="border-t text-center">
                <td>{{ $i->product->name }}</td>
                <td>{{ $i->qty }}</td>
                <td>{{ number_format($i->supplierItem->price ?? 0) }}</td>
                <td>
                    {{ number_format($i->qty * ($i->supplierItem->price ?? 0)) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4 text-right font-bold">
        Total: Rp {{ number_format($quotation->total, 0, ',', '.') }}
    </div>

</div>

@endsection