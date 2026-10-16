@extends('layouts.app')

@section('title', 'Edit Harga Product')

@section('content')

<div class="bg-white rounded-2xl shadow p-6 max-w-lg mx-auto">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Edit Harga Product</h3>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
        <p class="text-sm text-gray-600 mb-1">Supplier</p>
        <p class="font-medium text-lg">
            {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
        </p>
        
        <p class="text-sm text-gray-600 mt-4 mb-1">Produk</p>
        <p class="font-medium text-lg">
            {{ $product->name ?? $product->nama_produk ?? 'Produk #' . $product->id }}
        </p>
    </div>

    <form action="{{ route('supplier-product.update', [$supplier->id, $product->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Harga (Rp)
            </label>
            <input type="number" 
                   name="price" 
                   value="{{ old('price', $product->pivot->price) }}"
                   step="0.01" 
                   min="0"
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:outline-none focus:border-blue-500"
                   required>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 rounded-lg transition">
                Simpan Perubahan Harga
            </button>
            
            <a href="{{ route('supplier-product.index') }}" 
               class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-3 rounded-lg text-center transition">
                Kembali
            </a>
        </div>
    </form>

</div>

@endsection