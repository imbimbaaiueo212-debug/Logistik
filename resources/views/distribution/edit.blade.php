@extends('layouts.app')

@section('title', 'Edit Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<h2 class="text-xl font-bold mb-4">Edit Distribusi</h2>

<form method="POST" action="{{ route('distribution.update', $distribution->id) }}">
@csrf
@method('PUT')

<!-- Gudang -->
<select name="warehouse_id" class="border p-2 rounded w-full mb-3">
    @foreach($warehouses as $w)
        <option value="{{ $w->id }}"
            {{ $distribution->warehouse_id == $w->id ? 'selected' : '' }}>
            {{ $w->name }}
        </option>
    @endforeach
</select>

<!-- Tujuan -->
<input type="text" name="destination"
    value="{{ $distribution->destination }}"
    class="border p-2 rounded w-full mb-4">

<table class="w-full border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2">Produk</th>
            <th class="p-2">Qty</th>
        </tr>
    </thead>

    <tbody>
        @foreach($distribution->items as $i => $item)
        <tr class="border-t text-center">
            <td>{{ $item->product->name }}</td>
            <td>
                <input type="number"
                    name="items[{{ $i }}][qty]"
                    value="{{ $item->qty }}"
                    class="border p-1 w-20 text-center">

                <input type="hidden"
                    name="items[{{ $i }}][product_id]"
                    value="{{ $item->product_id }}">
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<button class="bg-blue-500 text-white px-4 py-2 rounded mt-4">
    Update
</button>

</form>

</div>

@endsection