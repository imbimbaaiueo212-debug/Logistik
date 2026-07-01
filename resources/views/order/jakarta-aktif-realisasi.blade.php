<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Realisasi Jakarta Aktif</title>

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

<div class="max-w-5xl mx-auto py-10">

    <h1 class="text-4xl font-bold mb-8">
        Realisasi Jakarta Aktif
    </h1>

    <div class="grid md:grid-cols-2 gap-8">

        <a href="{{ route('order.jakarta-aktif.majalah') }}">

            <div class="bg-white rounded-3xl shadow-lg p-10 hover:shadow-xl">

                <div class="text-6xl mb-4">
                    📚
                </div>

                <h2 class="text-2xl font-bold">
                    Majalah
                </h2>

            </div>

        </a>

        <a href="{{ route('order.jakarta-aktif.modul') }}">

            <div class="bg-white rounded-3xl shadow-lg p-10 hover:shadow-xl">

                <div class="text-6xl mb-4">
                    📖
                </div>

                <h2 class="text-2xl font-bold">
                    Modul
                </h2>

            </div>

        </a>

    </div>

    <div class="mt-10">

        <a href="{{ route('order.jakarta-aktif.menu') }}"
           class="px-6 py-3 bg-gray-600 text-white rounded-xl">

            ← Kembali

        </a>

    </div>

</div>

</body>
</html>