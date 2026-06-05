@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')

<div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">Tambah Kategori</h2>

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-sm mb-1">Nama Kategori</label>
            <input type="text" name="nama"
                   value="{{ old('nama') }}"
                   class="w-full border px-4 py-2 rounded-lg"
                   required>

            @error('nama')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div class="flex gap-3">
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                Simpan
            </button>

            <a href="{{ route('categories.index') }}"
               class="bg-gray-300 px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>
    </form>
</div>

@endsection