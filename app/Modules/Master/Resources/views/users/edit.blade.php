@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow max-w-lg">

    <h2 class="text-xl font-semibold mb-6">Edit User</h2>

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Nama -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Nama</label>
            <input type="text" name="name" 
                   value="{{ old('name', $user->name) }}"
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
                   value="{{ old('email', $user->email) }}"
                   class="w-full border p-2 rounded-lg"
                   required>

            @error('email')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Role</label>
            <select name="role" id="roleSelect" class="w-full border p-2 rounded-lg" required>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin (Full Akses)</option>
                <option value="gudang" {{ old('role', $user->role) == 'gudang' ? 'selected' : '' }}>User Gudang</option>
                <option value="customer" {{ old('role', $user->role) == 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="pic" {{ old('role', $user->role) == 'pic' ? 'selected' : '' }}>PIC</option>
                <option value="pic_khusus" {{ old('role', $user->role) == 'pic_khusus' ? 'selected' : '' }}>PIC Khusus</option>
            </select>
            @error('role')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Akses Modul -->
        <div class="mb-6" id="moduleWrap">
            <label class="block mb-1 font-medium">Akses Modul</label>
            <div class="border p-3 rounded-lg max-h-48 overflow-y-auto">
                @foreach($modules as $m)
                    <label class="flex items-center gap-2 mb-1">
                        <input type="checkbox" name="modules[]" value="{{ $m->id }}"
                               {{ $user->modules->contains($m->id) ? 'checked' : '' }}>
                        {{ $m->name }}
                    </label>
                @endforeach
            </div>
            @error('modules')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1" id="moduleHint">Customer/PIC hanya boleh pilih 1 akses.</p>
        </div>

        <!-- Info Password -->
        <div class="mb-6 text-sm text-gray-500">
            Kosongkan password jika tidak ingin mengubah
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label class="block mb-1 font-medium">Password Baru</label>
            <input type="password" name="password"
                   class="w-full border p-2 rounded-lg">

            @error('password')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm -->
        <div class="mb-6">
            <label class="block mb-1 font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                   class="w-full border p-2 rounded-lg">
        </div>

        <!-- Button -->
        <div class="flex gap-3">
            <a href="{{ route('users.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-lg w-1/2 text-center">
                Kembali
            </a>

            <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg w-1/2">
                Update
            </button>
        </div>

    </form>

</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const moduleWrap = document.getElementById('moduleWrap');
    const moduleHint = document.getElementById('moduleHint');

    function updateModuleField() {
        const role = roleSelect.value;
        const inputs = moduleWrap.querySelectorAll('input[name="modules[]"]');

        // Admin & Gudang: akses otomatis, tidak perlu dipilih manual
        if (role === 'admin' || role === 'gudang') {
            moduleWrap.style.display = 'none';
            return;
        }

        moduleWrap.style.display = 'block';
        const single = ['customer', 'pic'].includes(role);

        inputs.forEach(el => { el.type = single ? 'radio' : 'checkbox'; });

        moduleHint.textContent = single
            ? 'Role ini hanya boleh diberi 1 akses modul.'
            : 'PIC Khusus boleh diberi lebih dari 1 akses modul.';
    }

    roleSelect.addEventListener('change', updateModuleField);
    updateModuleField();
</script>

@endsection