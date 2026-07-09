<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Majalah Sahabat biMBA 2026</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body{
            font-family: Poppins, sans-serif;
        }
    </style>

</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-6xl mx-auto py-10">

    <h1 class="text-4xl font-bold mb-2">
        Majalah Sahabat biMBA Tahun 2026
    </h1>

    <p class="text-gray-500 mb-8">
        Pilih edisi majalah yang ingin dibuka.
    </p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

@for($edisi = 150; $edisi <= 171; $edisi++)

    <a href="{{ route('majalah.2026.show', $edisi) }}">
        <div class="bg-white rounded-3xl shadow-lg p-8 hover:shadow-xl transition">
            <div class="text-5xl mb-3">📰</div>

            <h3 class="text-lg font-semibold">
                Majalah Sahabat biMBA Edisi {{ $edisi }}
            </h3>
        </div>
    </a>

@endfor

</div>

    <div class="mt-10">
        <a href="{{ route('order.jakarta-aktif.majalah') }}"
           class="px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition">
            ← Kembali
        </a>
    </div>

</div>

</body>
</html>