<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Majalah Sahabat biMBA Edisi {{ $edisi }} - Diproses</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body{
            font-family:Poppins,sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-6xl mx-auto py-10">

    <h1 class="text-4xl font-bold mb-2">
        Majalah Sahabat biMBA Edisi {{ $edisi }}
    </h1>

    <p class="text-gray-500 mb-8">
        Pilih kategori yang ingin diproses.
    </p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">

        <!-- 1.1 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'jkt']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">🏢</div>
                <h3 class="text-lg font-semibold">
                    Stokis Jakarta
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    SKU JKT (biMBA Shop + Casdana)
                </p>
            </div>
        </a>

        <!-- 1.2 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'logistik']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">🚚</div>
                <h3 class="text-lg font-semibold">
                    Stokis Logistik
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    SKU LG
                </p>
            </div>
        </a>

        <!-- 1.3 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'aktif']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">✅</div>
                <h3 class="text-lg font-semibold">
                    Stokis Aktif
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    COD + biMBA Shop
                </p>
            </div>
        </a>

        <!-- 1.4 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'pasif']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">📦</div>
                <h3 class="text-lg font-semibold">
                    Stokis Pasif
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    COD + biMBA Shop
                </p>
            </div>
        </a>

        <!-- 1.5 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'pua']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">🏬</div>
                <h3 class="text-lg font-semibold">
                    PUA
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    Khusus COD
                </p>
            </div>
        </a>

        <!-- 1.6 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'dlc']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">📊</div>
                <h3 class="text-lg font-semibold">
                    Stokis Jakarta Pasif
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    DLC + Spare 3%
                </p>
            </div>
        </a>

        <!-- 1.7 -->
        <a href="{{ route('majalah.2026.kategori', ['edisi' => $edisi, 'kategori' => 'ops2']) }}">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">⚙️</div>
                <h3 class="text-lg font-semibold">
                    OPS II
                </h3>
                <p class="text-sm text-gray-500 mt-2">
                    Operasional II
                </p>
            </div>
        </a>

    </div>

    <div class="mt-10">
        <a href="{{ route('majalah.2026.show', $edisi) }}"
           class="px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition">
            ← Kembali
        </a>
    </div>

</div>

</body>
</html>