<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QC Outgoing - biMBA Logistik</title>
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
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">QC Outgoing</h2>
                        <p class="text-gray-500 mt-1">Quality Control Barang Keluar</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-5 gap-6">

                    <!-- Jakarta Aktif -->
                    <a href="{{ route('qc-outgoing.jakarta-aktif') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">📦</div>
                            <h3 class="text-2xl font-semibold mb-2">Jakarta Aktif</h3>
                            <p class="text-gray-500 text-sm">QC Outgoing Jakarta Aktif</p>
                        </div>
                    </a>

                    <!-- Jakarta Pasif -->
                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">📦</div>
                            <h3 class="text-2xl font-semibold mb-2">Jakarta Pasif</h3>
                            <p class="text-gray-500 text-sm">QC Outgoing Jakarta Pasif</p>
                        </div>
                    </a>

                    <!-- InterVio (DLC) -->
                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">📦</div>
                            <h3 class="text-2xl font-semibold mb-2">InterVio (DLC)</h3>
                            <p class="text-gray-500 text-sm">QC Outgoing DLC</p>
                        </div>
                    </a>

                    <!-- English biMBA Talk -->
                    <a href="#" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                            <div class="text-5xl mb-4">📦</div>
                            <h3 class="text-2xl font-semibold mb-2">English biMBA Talk</h3>
                            <p class="text-gray-500 text-sm">QC Outgoing EBT</p>
                        </div>
                    </a>

                    <!-- ===== ORDER MANUAL (BARU) ===== -->
                    <a href="{{ route('qc-outgoing.order-manual') }}" class="group">
                        <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full border-2 border-indigo-100 hover:border-indigo-400">
                            <div class="text-5xl mb-4">📋</div>
                            <h3 class="text-2xl font-semibold mb-2">Order Manual</h3>
                            <p class="text-gray-500 text-sm">QC Outgoing dari Picking Manual</p>
                        </div>
                    </a>

                </div>

                <div class="mt-12 flex justify-center">
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