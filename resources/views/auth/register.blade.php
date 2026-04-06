<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Bimba Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-700">Bimba Logistik</h1>
            <p class="text-gray-600 mt-2">Buat Akun Baru</p>
        </div>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2">Nama Lengkap</label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500"
                       placeholder="Masukkan nama lengkap" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500"
                       placeholder="Masukkan email" required>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" 
                       name="password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500"
                       placeholder="Buat password minimal 6 karakter" required>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" 
                       name="password_confirmation" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500"
                       placeholder="Ulangi password" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3.5 rounded-xl transition duration-200">
                Daftar Akun
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>