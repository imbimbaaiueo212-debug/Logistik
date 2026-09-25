<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Bimba Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f1e3d 0%, #1e3a8a 45%, #3b82f6 100%);
        }
        .role-card input:checked + label {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 0 0 1px #2563eb;
        }
        .role-card input:checked + label .role-dot {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .role-card input:checked + label .role-dot::after {
            content: '';
            display: block;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: white;
            margin: 3px;
        }
        .module-chip input:checked + label {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #1e3a8a;
        }
        .module-chip input:checked + label svg {
            opacity: 1;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 9999px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-10 px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden">

        {{-- Header --}}
        <div class="px-8 pt-8 pb-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.5 2.7-6 6-6s6 2.5 6 6"/><path d="M17 8h4M19 6v4"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Tambah Akun User</h1>
                    <p class="text-sm text-gray-500">Bimba Logistik &middot; khusus admin</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mx-8 mt-6 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="px-8 py-6">
            @csrf

            {{-- ============ DATA AKUN ============ --}}
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">Data Akun</p>

            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nama lengkap" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="nama@email.com" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1.5">Password</label>
                    <input type="password" name="password"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Minimal 6 karakter" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ulangi password" required>
                </div>
            </div>

            {{-- ============ ROLE ============ --}}
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3 mt-6">Role</p>

            @php
                $roles = [
                    'admin'      => ['Admin', 'Akses penuh ke seluruh modul'],
                    'customer'   => ['Customer', 'Hanya 1 modul yang ditentukan admin'],
                    'pic'        => ['PIC', 'Hanya 1 modul yang ditentukan admin'],
                    'pic_khusus' => ['PIC Khusus', 'Boleh diberi beberapa modul'],
                ];
            @endphp

            <div class="grid sm:grid-cols-2 gap-3 mb-2">
                @foreach($roles as $val => [$label, $desc])
                    <div class="role-card">
                        <input type="radio" name="role" id="role_{{ $val }}" value="{{ $val }}"
                               class="hidden peer"
                               {{ old('role')==$val ? 'checked' : '' }} required>
                        <label for="role_{{ $val }}"
                               class="flex items-start gap-3 border border-gray-200 rounded-xl p-3.5 cursor-pointer transition-colors hover:border-gray-300">
                            <span class="role-dot w-4 h-4 rounded-full border-2 border-gray-300 flex-shrink-0 mt-0.5"></span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-800">{{ $label }}</span>
                                <span class="block text-xs text-gray-500 mt-0.5">{{ $desc }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('role')<p class="text-red-500 text-xs mt-1 mb-2">{{ $message }}</p>@enderror

            {{-- ============ AKSES MODUL ============ --}}
            <div id="moduleWrap" class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Akses Modul</p>
                    <p class="text-xs text-gray-400" id="moduleHint">Pilih role terlebih dahulu</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-2 border border-gray-200 rounded-xl p-3 max-h-56 overflow-y-auto bg-gray-50">
                    @foreach($modules as $m)
                        <div class="module-chip">
                            <input type="checkbox" name="modules[]" value="{{ $m->id }}"
                                   id="mod_{{ $m->id }}" class="hidden"
                                   {{ collect(old('modules'))->contains($m->id) ? 'checked' : '' }}>
                            <label for="mod_{{ $m->id }}"
                                   class="flex items-center gap-2 border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-600 cursor-pointer transition-colors hover:border-gray-300">
                                <svg class="w-3.5 h-3.5 text-blue-600 opacity-0 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
                                <span class="truncate">{{ $m->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('modules')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full mt-7 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition duration-200 shadow-sm shadow-blue-200">
                Buat Akun
            </button>
        </form>

        <div class="text-center pb-6">
            <a href="{{ route('users.index') }}" class="text-sm text-blue-600 hover:underline font-medium inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
                Kembali ke Daftar User
            </a>
        </div>
    </div>

    <script>
        const roleInputs  = document.querySelectorAll('input[name="role"]');
        const moduleWrap  = document.getElementById('moduleWrap');
        const moduleHint  = document.getElementById('moduleHint');
        const moduleInputs = () => moduleWrap.querySelectorAll('input[name="modules[]"]');

        function updateModuleField() {
            const checked = document.querySelector('input[name="role"]:checked');
            if (!checked) {
                moduleWrap.style.display = 'block';
                moduleHint.textContent = 'Pilih role terlebih dahulu';
                return;
            }

            const role = checked.value;

            if (role === 'admin') {
                moduleWrap.style.display = 'none';
                moduleInputs().forEach(el => el.checked = false);
                return;
            }

            moduleWrap.style.display = 'block';
            const single = ['customer', 'pic'].includes(role);

            moduleInputs().forEach(el => { el.type = single ? 'radio' : 'checkbox'; });

            moduleHint.textContent = single
                ? 'Pilih 1 akses modul'
                : 'Boleh pilih lebih dari 1 akses modul';
        }

        roleInputs.forEach(el => el.addEventListener('change', updateModuleField));
        updateModuleField();
    </script>
</body>
</html>