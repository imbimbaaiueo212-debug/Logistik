<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database - biMBA Logistik</title>
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
                
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">DATABASE</h2>
                        <p class="text-gray-500 mt-1">Pusat Pengelolaan Data User & Kemitraan</p>
                    </div>
                    
                    <a href="{{ route('user.export') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-medium flex items-center gap-2">
                        📤 Export User Baru
                    </a>
                </div>

                <!-- Grid Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-6">

                    <!-- Card 1 -->
                    <a href="{{ route('user.export') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">👥</div>
                            <h3 class="text-2xl font-semibold mb-2">User biMBA Shop</h3>
                        </div>
                    </a>

                    <!-- Card 2 -->
                    <a href="{{ route('unit-kemitraan.index') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏢</div>
                            <h3 class="text-2xl font-semibold mb-2">Unit Kemitraan</h3>
                        </div>
                    </a>

                    <!-- Card 3 -->
                    <!-- <a href="{{ route('stokis.index') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏬</div>
                            <h3 class="text-2xl font-semibold mb-2">Stokis Mitra</h3>
                        </div>
                    </a> -->

                    <a href="{{ route('pesanan-majalah.index') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏬</div>
                            <h3 class="text-2xl font-semibold mb-2">Korwil</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏬</div>
                            <h3 class="text-2xl font-semibold mb-2">Pinwil</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🏬</div>
                            <h3 class="text-2xl font-semibold mb-2">PUW 1</h3>
                        </div>
                    </a>

                    <!-- Card 4 -->
                    <a href="{{ route('unit-kemitraan-user.index') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">🔗</div>
                            <h3 class="text-2xl font-semibold mb-2">Unit + User Matching</h3>
                            <p class="text-gray-500 text-sm">Export Gabungan (Matching)</p>
                        </div>
                    </a>

                </div>

                <!-- Tombol Kembali -->
                <div class="mt-12 flex justify-center">
                    <a href="{{ route('home') }}" 
                       class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-8 py-3 rounded-2xl font-medium transition-all">
                        Menu Utama
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>