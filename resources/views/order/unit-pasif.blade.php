<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        .sidebar-active {
            background-color: #1e40af;
            color: white;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.top-nav')

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="flex h-screen overflow-hidden pt-0">

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Data Order Unit Stokis Pasif</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    
                    <!-- Card 3 -->
                    <a href="{{ route('order.jakarta-aktif.menu') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Jakarta Aktif</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Jakarta Pasif</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Logistik</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Semarang</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Surabaya</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Inventaris</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">InterVio (DLC)</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">English biMBA Talk (EBT)</h3>
                        </div>
                    </a>

                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-2xl font-semibold mb-2">Soccer School (biMBA SS)</h3>
                        </div>
                    </a>

                </div>

                <!-- Tombol Kembali -->
                <div class="mt-10 flex justify-center">
                    <a href="{{ route('order.index') }}" 
                       class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-8 py-3 rounded-2xl font-medium transition-all">
                        ← Kembali ke Home
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>