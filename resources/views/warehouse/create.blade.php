@extends('layouts.app')

@section('title', 'Tambah Gudang')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-xl">

    <h2 class="text-xl font-bold mb-4">Tambah Gudang</h2>

    <form action="{{ route('warehouses.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Gudang</label>
            <input type="text" name="name" class="border p-2 w-full rounded" required>
        </div>

        <div class="mb-3">
            <label>Kode Gudang</label>
            <input type="text" name="code" class="border p-2 w-full rounded">
        </div>

        <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="location" class="border p-2 w-full rounded">
        </div>

        <div class="flex gap-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded">
                Simpan
            </button>

            <a href="{{ route('warehouses.index') }}"
               class="bg-gray-400 text-white px-4 py-2 rounded">
                Kembali
            </a>
        </div>

    </form>

</div>

@endsection