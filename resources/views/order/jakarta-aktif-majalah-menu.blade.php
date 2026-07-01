<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Majalah - Realisasi</title>

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

    <h1 class="text-4xl font-bold mb-8">
        Realisasi → Majalah
    </h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

        <!-- Jakarta Aktif -->
        <a href="#">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">🏢</div>
                <h3 class="text-xl font-semibold">Unit Aktif</h3>
            </div>
        </a>

        <!-- Jakarta Pasif -->
        <a href="#">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">🏢</div>
                <h3 class="text-xl font-semibold">Unit Pasif</h3>
            </div>
        </a>

        <!-- DLC -->
        <a href="#">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">📦</div>
                <h3 class="text-xl font-semibold">DLC | InterVio</h3>
            </div>
        </a>

        <!-- PUW -->
        <a href="#">
            <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-5xl mb-3">📘</div>
                <h3 class="text-xl font-semibold">PUW</h3>
            </div>
        </a>

    </div>

    <div class="mt-10">
        <a href="{{ route('order.jakarta-aktif.realisasi') }}"
           class="px-6 py-3 bg-gray-600 text-white rounded-xl">
            ← Kembali
        </a>
    </div>

</div>

</body>
</html>