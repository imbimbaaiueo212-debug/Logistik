<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Import - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')
<div class="flex h-screen">

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Data Import</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- biMBA Shop Card -->
            <a href="{{ route('import.bimbashop') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                    <div class="text-5xl mb-4">🏪</div>
                    <h3 class="text-2xl font-semibold mb-2">biMBA Shop</h3>
                    <p class="text-gray-600">Import data produk, stok, harga, dan kategori dari biMBA Shop.</p>
                </div>
            </a>

            <!-- Kas Dana Card (sudah diperbaiki) -->
            <a href="{{ route('import.casdana') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                    <div class="text-5xl mb-4">💰</div>
                    <h3 class="text-2xl font-semibold mb-2">Kasdana</h3>
                    <p class="text-gray-600">Import data transaksi kasdana, pemasukan, dan pengeluaran.</p>
                </div>
            </a>

            <a href="{{ route('ops2.index') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏬</div>
                            <h3 class="text-2xl font-semibold mb-2">Unit Operasional 2 (OPS2)</h3>
                            <p class="text-gray-500 text-sm mt-1">KORWIL · PINWIL · JABODETABEK</p>
                        </div>
                    </a>

            <a href="{{ route('import.manual') }}" class="group">
                <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                    <div class="text-5xl mb-4">📝</div>
                    <h3 class="text-2xl font-semibold mb-2">Manual Pemesanan</h3>
                    <p class="text-gray-600">
                        Input data pemesanan secara manual, termasuk produk, jumlah, dan harga.
                    </p>
                </div>
            </a>
        </div>

       <div class="p-4 border-t bg-gray-50">
    <a href="{{ route('home') }}" 
       class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-6 py-3 rounded-2xl font-medium transition-all w-fit mx-auto">
        ← Kembali
    </a>
</div>
    </div>
</div>

</body>
</html>