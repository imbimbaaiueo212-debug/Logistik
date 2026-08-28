<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Pasif - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50">
@include('partials.top-nav')

<div class="flex h-screen">
    <div class="flex-1 p-6 md:p-8 overflow-auto">

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Unit Pasif</h2>
            <p class="text-slate-500 mt-1 text-sm md:text-base">Pilih jenis data yang ingin dikelola</p>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5 max-w-7xl">

            <!-- Unit Pasif -->
            <a href="{{ route('import.pasif.list') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-blue-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-blue-100 transition-colors">
                        📘
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-blue-700 transition-colors">
                        Unit Pasif
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Data pemesanan majalah Unit Pasif (import Excel &amp; sync ke Manual).
                    </p>
                </div>
            </a>

            <!-- Spare Pasif 3% -->
            <a href="{{ route('import.pasif.spare') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-amber-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-amber-100 transition-colors">
                        📦
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-amber-700 transition-colors">
                        Spare Pasif 3%
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Total (DLC + Pasif + Bacaan) × 3% dibulatkan. Digunakan untuk hitung lembar print (200/lembar).
                    </p>
                </div>
            </a>

            <!-- Bacaan Unit -->
            <a href="{{ route('import.pasif.bacaan') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-emerald-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-emerald-100 transition-colors">
                        📖
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-emerald-700 transition-colors">
                        Bacaan Unit
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Data Bacaan Unit (masih dalam pengembangan).
                    </p>
                </div>
            </a>

            <!-- Import / Rekap -->
            <a href="{{ route('import.pasif.rekap') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-violet-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-violet-100 transition-colors">
                        📊
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-violet-700 transition-colors">
                        Import
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Hasil import lengkap — Bacaan Unit + Qty Majalah.
                    </p>
                </div>
            </a>

            <!-- Create Manual -->
            <a href="{{ route('import.pasif.manual.index') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-rose-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-rose-100 transition-colors">
                        ✍️
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-rose-700 transition-colors">
                        Create Manual
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Input manual pesanan majalah pasif Majalah.
                    </p>
                </div>
            </a>

            <!-- Report Angka Cetak -->
            <a href="{{ route('import.report-angka-cetak') }}" class="group block h-full">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:border-indigo-300 transition-all duration-300 h-full flex flex-col p-6">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-2xl mb-4 group-hover:bg-indigo-100 transition-colors">
                        📈
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-indigo-700 transition-colors">
                        Report Angka Cetak
                    </h3>
                    <p class="text-slate-500 text-sm leading-relaxed flex-1">
                        Ringkasan qty cetak majalah per edisi (mirip Excel).
                    </p>
                </div>
            </a>

        </div>

        <!-- Back Link -->
        <div class="mt-10">
            <a href="{{ route('order-manual.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-blue-600 transition-colors">
                <span class="text-lg leading-none">←</span>
                Kembali
            </a>
        </div>

    </div>
</div>
</body>
</html>