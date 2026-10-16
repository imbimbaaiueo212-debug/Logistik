@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-lg">

    <h2 class="text-xl font-semibold mb-6">Tambah User</h2>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <!-- Nama -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Nama</label>
            <input type="text" name="name" 
                   value="{{ old('name') }}"
                   class="w-full border p-2 rounded-lg"
                   required>

            @error('name')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" 
                   value="{{ old('email') }}"
                   class="w-full border p-2 rounded-lg"
                   required>

            @error('email')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password</label>
            <input type="password" name="password"
                   class="w-full border p-2 rounded-lg"
                   required>

            @error('password')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                   class="w-full border p-2 rounded-lg"
                   required>
        </div>

        <!-- Role -->
        <div class="mb-6">
            <label class="block mb-1 font-medium">Role</label>
            <select name="role" class="w-full border p-2 rounded-lg">
                <option value="admin">Admin</option>
                <option value="gudang">Gudang</option>
                <option value="manager">Manager</option>
            </select>
        </div>

        <!-- Button -->
        <div class="flex gap-3">
            <a href="{{ route('users.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-lg w-1/2 text-center">
                Kembali
            </a>

            <button type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded-lg w-1/2">
                Simpan
            </button>
        </div>

    </form>

</div>

@endsection