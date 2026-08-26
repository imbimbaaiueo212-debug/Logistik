<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Unit Pasif - biMBA AIUEO Logistik</title>
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

        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Import Unit Pasif</h2>
            <p class="text-gray-500 mb-8">Upload file Excel rekap pemesanan majalah Unit Pasif</p>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('import.pasif.store') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white rounded-2xl shadow p-8 space-y-6">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Edisi <span class="text-red-500">*</span></label>
                        <input type="text" name="edisi" value="{{ old('edisi', 'M159') }}" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="M159">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No PS</label>
                        <input type="text" name="no_ps" value="{{ old('no_ps') }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Opsional">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                        <input type="text" name="periode" value="{{ old('periode', 'Juni 2026') }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Juni 2026">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <input type="text" name="bulan" value="{{ old('bulan', 'Juni') }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <input type="text" name="tahun" value="{{ old('tahun', '2026') }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        File Excel <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-2">
                        Format: NO | CABANG | biMBA-AIUEO UNIT | MAJALAH | Bacaan Unit | NO TELP | ALAMAT
                    </p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium transition">
                        Import Sekarang
                    </button>
                    <a href="{{ route('import.pasif.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-xl font-medium transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
</body>
</html>