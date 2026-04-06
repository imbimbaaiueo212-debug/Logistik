@extends('layouts.app')

@section('title', 'Supplier Product')

@section('content')

<div class="bg-white rounded-2xl shadow p-6">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between mb-6">
        <h3 class="text-xl font-semibold">Supplier - Product Relation</h3>
    </div>

    <!-- Form Tambah Relasi -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-8">
        <h4 class="font-medium mb-4">Tambah Produk ke Supplier</h4>
        
        <form action="{{ route('supplier-product.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                            required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <select name="product_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                            required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name ?? $product->nama_produk ?? 'Produk #' . $product->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                    <input type="number" 
                           name="price" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                           required>
                </div>

                <div class="md:col-span-1 flex items-end">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg w-full">
                        Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Daftar Relasi Supplier & Produk -->
    <h4 class="text-lg font-medium mb-4">Daftar Relasi Supplier dan Produk</h4>

    @forelse ($suppliers as $supplier)
        <div class="mb-8">
            <div class="bg-gray-100 px-5 py-3 rounded-t-xl font-medium">
                {{ $supplier->kode_supplier }} — {{ $supplier->nama_supplier }}
            </div>

            @if ($supplier->products->isNotEmpty())
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left border-b">No</th>
                            <th class="p-3 text-left border-b">Nama Produk</th>
                            <th class="p-3 text-right border-b">Harga</th>
                            <th class="p-3 text-center border-b w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplier->products as $index => $product)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3">{{ $index + 1 }}</td>
                                <td class="p-3">
                                    {{ $product->name ?? $product->nama_produk ?? 'Produk #' . $product->id }}
                                </td>
                                <td class="p-3 text-right font-medium">
                                    Rp {{ number_format($product->pivot->price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="p-3 flex gap-2 justify-center">
                                    <!-- Edit Harga -->
                                    <a href="{{ route('supplier-product.edit', [$supplier->id, $product->id]) }}" 
                                       class="bg-yellow-400 hover:bg-yellow-500 px-4 py-1 rounded text-white text-sm">
                                        Edit Harga
                                    </a>

                                    <!-- Hapus -->
                                    <form action="{{ route('supplier-product.destroy', [$supplier->id, $product->id]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus produk ini dari supplier?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-600 px-4 py-1 rounded text-white text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="border border-dashed border-gray-300 rounded-b-xl p-8 text-center text-gray-500">
                    Belum ada produk yang ditambahkan untuk supplier ini.
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
            Belum ada data supplier.
        </div>
    @endforelse

</div>

@endsection