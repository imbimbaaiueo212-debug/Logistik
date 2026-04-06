@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')

<div class="bg-white rounded-2xl shadow p-6 max-w-xl">

    <h3 class="text-xl font-semibold mb-6">Edit Supplier</h3>

    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Nama</label>
            <input type="text" name="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier ?? '') }}" class= "w-full border rounded-lg p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">SKU</label>
            <input type="text" name="kode_supplier" value="{{ old('kode_supplier', $supplier->kode_supplier ?? '') }}" class= "w-full border rounded-lg p-2" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Email</label>
            <input type="email" name="email" value="{{ $supplier->email }}" class="w-full border rounded-lg p-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Phone</label>
            <input type="text" name="phone" value="{{ $supplier->phone }}" class="w-full border rounded-lg p-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1">Alamat</label>
            <textarea name="address" class="w-full border rounded-lg p-2">{{ $supplier->address }}</textarea>
        </div>

        <div class="flex gap-2">
            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                Update
            </button>

            <a href="{{ route('suppliers.index') }}" 
               class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>

    </form>

</div>

@endsection