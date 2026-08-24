<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking - biMBA Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.top-nav')

    <div class="flex h-screen overflow-hidden pt-0">
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Picking List</h2>
                    <a href="{{ route('picking.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                        + Buat Picking Baru
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
                <!-- Jakarta Aktif -->
                <a href="{{ route('picking.jakarta.aktif') }}" class="group">
                    <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                        <div class="text-5xl mb-4">📦</div>
                        <h3 class="text-2xl font-semibold mb-2">Jakarta Aktif</h3>
                        <p class="text-gray-500 text-sm">Picking List Jakarta Aktif</p>
                    </div>
                </a>

                <a href="{{ route('picking.jakarta.pasif') }}" class="group">
                    <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                        <div class="text-5xl mb-4">📦</div>
                        <h3 class="text-2xl font-semibold mb-2">Jakarta Pasif</h3>
                        <p class="text-gray-500 text-sm">Picking List Jakarta Pasif</p>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                        <div class="text-5xl mb-4">📦</div>
                        <h3 class="text-2xl font-semibold mb-2">InterVio (DLC)</h3>
                    </div>
                </a>

                <a href="#" class="group">
                    <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                        <div class="text-5xl mb-4">📦</div>
                        <h3 class="text-2xl font-semibold mb-2">English biMBA Talk</h3>
                    </div>
                </a>

                <!-- Order Manual -->
                <a href="{{ route('picking.order-manual') }}" class="group">
                    <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                        <div class="text-5xl mb-4">📝</div>
                        <h3 class="text-2xl font-semibold mb-2">Order Manual</h3>
                    </div>
                </a>

            </div>

                <!-- Tombol Kembali -->
                <div class="mt-10 flex justify-center">
                    <a href="{{ route('home') }}" 
                       class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-8 py-3 rounded-2xl font-medium transition-all">
                        ← Kembali ke Menu Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>