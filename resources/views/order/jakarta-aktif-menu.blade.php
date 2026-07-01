<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jakarta Aktif</title>

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
        Jakarta Aktif
    </h1>

    <div class="grid md:grid-cols-2 gap-8">

       <a href="{{ route('order.jakarta-aktif.realisasi') }}">

            <div class="bg-white rounded-3xl shadow-lg p-10 hover:shadow-xl transition">

                <div class="text-6xl mb-4">
                    📋
                </div>

                <h2 class="text-2xl font-bold">
                    Realisasi
                </h2>

                <p class="text-gray-500 mt-2">
                    Data realisasi order Jakarta Aktif
                </p>

            </div>

        </a>

        <a href="{{ route('order.jakarta-aktif') }}">

            <div class="bg-white rounded-3xl shadow-lg p-10 hover:shadow-xl transition">

                <div class="text-6xl mb-4">
                    📊
                </div>

                <h2 class="text-2xl font-bold">
                    Rekap Aktual
                </h2>

                <p class="text-gray-500 mt-2">
                    Monitoring proses picking & persiapan
                </p>

            </div>

        </a>

    </div>

    <div class="mt-10">

        <a href="{{ route('order.unit-pasif') }}"
   class="px-6 py-3 rounded-xl bg-gray-600 text-white">
    ← Kembali
</a>

    </div>

</div>

</body>
</html>