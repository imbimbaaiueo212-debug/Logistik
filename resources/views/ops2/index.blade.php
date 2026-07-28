<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>biMBA Operasional 2 (OPS2)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    {{-- HEADER --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('database-user.index') }}"
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ← Kembali
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">
            biMBA Operasional 2 (OPS2)
        </h1>
        <p class="text-gray-600 mt-1">
            Pilih wilayah pesanan majalah
        </p>
    </div>

    {{-- 3 PILIHAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- KORWIL --}}
        <a href="{{ route('pesanan-majalah.index') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <div class="text-5xl mb-4">🏬</div>
                <h3 class="text-2xl font-semibold mb-2 group-hover:text-blue-700 transition">
                    KORWIL
                </h3>
                <p class="text-gray-500 text-sm">
                    Pesanan Majalah
                </p>
            </div>
        </a>

        {{-- PINWIL --}}
        <a href="{{ route('pesanan-majalah-kotamadya.index') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <div class="text-5xl mb-4">🏬</div>
                <h3 class="text-2xl font-semibold mb-2 group-hover:text-blue-700 transition">
                    PINWIL
                </h3>
                <p class="text-gray-500 text-sm">
                    Pesanan Majalah Kotamadya
                </p>
            </div>
        </a>

        {{-- JABODETABEK --}}
        <a href="{{ route('pesanan-majalah-puw1.index') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <div class="text-5xl mb-4">🏬</div>
                <h3 class="text-2xl font-semibold mb-2 group-hover:text-blue-700 transition">
                    JABODETABEK
                </h3>
                <p class="text-gray-500 text-sm">
                    Pesanan Majalah PUW1
                </p>
            </div>
        </a>

    </div>

</div>

</body>
</html>