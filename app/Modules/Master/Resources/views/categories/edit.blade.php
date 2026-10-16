@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')

<div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">Edit Kategori</h2>

    <form action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm mb-1">Nama Kategori</label>
            <input type="text" name="nama"
                   value="{{ old('nama', $category->nama) }}"
                   class="w-full border px-4 py-2 rounded-lg"
                   required>

            @error('nama')
                <small class="text-red-500">{{ $message }}</small>
            @enderror
        </div>

        <div class="flex gap-3">
            <button class="bg-yellow-500 text-white px-4 py-2 rounded-lg">
                Update
            </button>

            <a href="{{ route('categories.index') }}"
               class="bg-gray-300 px-4 py-2 rounded-lg">
                Kembali
            </a>
        </div>
    </form>
</div>

@endsection