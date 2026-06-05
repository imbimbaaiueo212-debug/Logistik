<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .sidebar-active { background-color: #000e8e; color: white; }
    </style>
</head>
<body class="bg-gray-50">

<div class="flex h-screen">

    <!-- Sidebar -->
    <div class="w-72 bg-white shadow-xl border-r border-gray-200 flex flex-col">
        <div class="p-6 border-b">
            <h1 class="text-2xl font-bold text-[#000e8e]">
                <span style="color: #000e8e;">bi</span><span style="color: #f44040;">M</span><span style="color: #000e8e;">BA</span> ORDER
            </h1>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <nav class="space-y-1">
                <a href="#" class="sidebar-active flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium">
                    📋 Dashboard Order
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-gray-100 text-sm font-medium">
                    ➕ Buat Order Baru
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-gray-100 text-sm font-medium">
                    📦 Daftar Order
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-gray-100 text-sm font-medium">
                    🏬 Order per Unit
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-gray-100 text-sm font-medium">
                    📊 Rekap Order
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-gray-100 text-sm font-medium">
                    📋 Validasi Order
                </a>
            </nav>
        </div>

        <div class="p-4 border-t">
            <a href="{{ route('home') }}" 
               class="flex items-center justify-center gap-2 text-gray-600 hover:text-gray-800 py-3">
                ← Kembali ke Home
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
        <div class="p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">Dashboard Order</h2>
            
            <div class="bg-white rounded-3xl shadow p-8">
                <p class="text-gray-600">Halaman Order Management akan ditampilkan di sini.</p>
                <p class="text-sm text-gray-500 mt-4">Silakan tambahkan tabel order, form buat order baru, dll sesuai kebutuhan.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>