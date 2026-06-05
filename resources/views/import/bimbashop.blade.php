<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import biMBA Shop - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

<div class="flex h-screen">
    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Import Data biMBA Shop</h2>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-5 py-4 rounded-2xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow p-10">
            <form action="{{ route('import.bimbashop.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="border-2 border-dashed border-gray-300 rounded-3xl p-12 text-center hover:border-blue-400 transition-all">
                    <input type="file" name="import_file" accept=".xlsx,.xls,.csv" id="file" class="hidden">
                    <label for="file" class="cursor-pointer block">
                        <div class="text-7xl mb-4">📁</div>
                        <p class="text-xl font-semibold text-gray-700">Pilih File Excel / CSV</p>
                        <p class="text-gray-500 mt-2">Maksimal 10 MB</p>
                    </label>
                </div>

                <button type="submit" 
                        class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-5 rounded-2xl text-lg transition-all">
                    📤 Upload & Proses Import
                </button>
            </form>
            <div class="p-4 border-t bg-gray-50">
            <a href="{{ route('import.index') }}" 
               class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 hover:text-blue-700 text-gray-700 py-3.5 rounded-2xl font-medium transition-all">
                ← Kembali ke Data Import
            </a>
        </div>
        </div>
    </div>
</div>

</body>
</html>