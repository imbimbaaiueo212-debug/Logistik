@extends('layouts.app')

@section('title', 'Edit Stok Masuk')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <form action="{{ route('gr.update', $gr->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="w-full border">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty Lama</th>
                    <th>Qty Baru</th>
                </tr>
            </thead>

            <tbody>
                @foreach($gr->items as $index => $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>
                        <input type="number" 
                               name="items[{{ $index }}][qty]" 
                               value="{{ $item->qty }}"
                               class="border p-1">

                        <input type="hidden" 
                               name="items[{{ $index }}][id]" 
                               value="{{ $item->id }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button class="bg-blue-500 text-white px-4 py-2 mt-4">
            Update
        </button>

    </form>

</div>

@endsection