@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')

<div class="bg-white rounded-2xl shadow p-6 max-w-xl">

    <h3 class="text-xl font-semibold mb-6">Tambah Supplier</h3>

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">Nama</label>
            <input type="text" name="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier ?? '') }}" class="w-full border rounded-lg p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">SKU</label>
            <input type="text" name="kode_supplier" value="{{ old('kode_supplier', $supplier->kode_supplier ?? '') }}" class="w-full border rounded-lg p-2" required>
        </div>
        
        <div class="mb-4">
            <label class="block mb-1">Email</label>
            <input type="email" name="email" class="w-full border rounded-lg p-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Phone</label>
            <input type="text" name="phone" class="w-full border rounded-lg p-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Alamat</label>
            <textarea name="address" class="w-full border rounded-lg p-2"></textarea>
        </div>

        <div class="flex gap-2">
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Simpan
            </button>

            <a href="{{ route('suppliers.index') }}" 
               class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>

    </form>

</div>

@endsection