<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Manual Sertifikat - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="flex h-screen">
    <div class="flex-1 p-8 overflow-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Order Manual Sertifikat</h2>
        <p class="text-gray-500 mb-8">Kelola data pemesanan sertifikat secara manual</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <a href="#" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                    <div class="text-5xl mb-4">🏬</div>
                    <h3 class="text-2xl font-semibold mb-2">Coming Soon</h3>
                    <p class="text-gray-500 text-sm mt-1">OPS2 - Sertifikat</p>
                </div>
            </a>

            <a href="#" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                    <div class="text-5xl mb-4">🏬</div>
                    <h3 class="text-2xl font-semibold mb-2">Coming Soon</h3>
                    <p class="text-gray-600">
                        Sertifikat DLC.
                    </p>
                </div>
            </a>

            <a href="#" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                    <div class="text-5xl mb-4">🏬</div>
                    <h3 class="text-2xl font-semibold mb-2">Coming Soon</h3>
                    <p class="text-gray-600">
                        Sertifikat Pasif.
                    </p>
                </div>
            </a>

            <a href="#" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                    <div class="text-5xl mb-4">📝</div>
                    <h3 class="text-2xl font-semibold mb-2">Manual Pemesanan Sertifikat</h3>
                    <p class="text-gray-600">
                        Input data pemesanan sertifikat secara manual, termasuk produk dan jumlah.
                    </p>
                </div>
            </a>

        </div>

        <div class="mt-10 flex justify-center">
            <a href="{{ route('home') }}"
               class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-teal-600 text-gray-700 hover:text-teal-700 px-8 py-3 rounded-2xl font-medium transition-all">
                ← Kembali
            </a>
        </div>
    </div>
</div>

</body>
</html>