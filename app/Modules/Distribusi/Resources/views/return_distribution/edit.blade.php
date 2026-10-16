@extends('layouts.app')

@section('title', 'Edit Return Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<h2 class="text-xl font-bold mb-4">Edit Return</h2>

<form method="POST" action="{{ route('return-distribution.update', $return->id) }}">
@csrf
@method('PUT')

<!-- Distribusi -->
<select name="distribution_id" class="border p-2 rounded w-full mb-3">
    @foreach($distributions as $d)
        <option value="{{ $d->id }}"
            {{ $return->distribution_id == $d->id ? 'selected' : '' }}>
            Distribusi #{{ $d->id }}
        </option>
    @endforeach
</select>

<!-- Gudang -->
<select name="warehouse_id" class="border p-2 rounded w-full mb-4">
    @foreach($warehouses as $w)
        <option value="{{ $w->id }}"
            {{ $return->warehouse_id == $w->id ? 'selected' : '' }}>
            {{ $w->name }}
        </option>
    @endforeach
</select>

<table class="w-full border">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2">Produk</th>
            <th class="p-2">Qty</th>
            <th class="p-2">Reason</th>
        </tr>
    </thead>

    <tbody>
        @foreach($return->items as $i => $item)
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

            <td>
                <input type="text"
                    name="items[{{ $i }}][reason]"
                    value="{{ $item->reason }}"
                    class="border p-1">
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