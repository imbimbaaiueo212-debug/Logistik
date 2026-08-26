<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Pasif - biMBA AIUEO Logistik</title>
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

        <h2 class="text-3xl font-bold text-gray-800 mb-2">Unit Pasif</h2>
        <p class="text-gray-500 mb-8">Pilih jenis data yang ingin dikelola</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl">

            <!-- Unit Pasif (Majalah) -->
            <a href="{{ route('import.pasif.list') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full border border-transparent hover:border-blue-200">
                    <div class="text-5xl mb-4">📘</div>
                    <h3 class="text-2xl font-semibold mb-2 group-hover:text-blue-700 transition">Unit Pasif</h3>
                    <p class="text-gray-600 text-sm">
                        Data pemesanan majalah Unit Pasif (import Excel & sync ke Manual).
                    </p>
                </div>
            </a>

            <!-- Bacaan Unit -->
            <a href="{{ route('import.pasif.bacaan') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full border border-transparent hover:border-emerald-200">
                    <div class="text-5xl mb-4">📖</div>
                    <h3 class="text-2xl font-semibold mb-2 group-hover:text-emerald-700 transition">Bacaan Unit</h3>
                    <p class="text-gray-600 text-sm">
                        Data Bacaan Unit (masih dalam pengembangan).
                    </p>
                </div>
            </a>

            <!-- Slot ke-3 (nanti) -->
           <!-- Rekap Total -->
            <a href="{{ route('import.pasif.rekap') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full border border-transparent hover:border-purple-200">
                    <div class="text-5xl mb-4">📊</div>
                    <h3 class="text-2xl font-semibold mb-2 group-hover:text-purple-700 transition">Import</h3>
                    <p class="text-gray-600 text-sm">
                        Hasil import lengkap — Bacaan Unit + Qty Majalah.
                    </p>
                </div>
            </a>

        </div>

        <div class="mt-10">
            <a href="{{ route('order-manual.index') }}"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">
                ← Kembali
            </a>
        </div>

    </div>
</div>
</body>
</html>