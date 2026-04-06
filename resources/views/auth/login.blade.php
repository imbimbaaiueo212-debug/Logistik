<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bimba Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-blue-700 tracking-tight">Bimba Logistik</h1>
            <p class="text-gray-600 mt-2 text-lg">Sistem Multi Warehouse & Inventory</p>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <!-- Email -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200 transition-all"
                       placeholder="Masukkan email anda" 
                       required>
                @error('email')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" 
                       name="password" 
                       class="w-full px-5 py-4 border border-gray-300 rounded-2xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-200 transition-all"
                       placeholder="Masukkan password" 
                       required>
                @error('password')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Lupa Password -->
            <div class="flex items-center justify-between mb-8">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-gray-300">
                    <span class="ml-3 text-sm text-gray-600">Ingat saya</span>
                </label>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">Lupa password?</a>
            </div>

            <!-- Tombol Login -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-4 rounded-2xl text-lg transition-all duration-200 shadow-md">
                MASUK
            </button>
        </form>

        <!-- Informasi Registrasi -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            Belum punya akun? <span class="font-medium text-gray-600">Hubungi Administrator</span>
        </div>

    </div>
</body>
</html>