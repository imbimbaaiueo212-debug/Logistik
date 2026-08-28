<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Angka Cetak Majalah</title>
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
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">
                    Report Angka Cetak Majalah
                </h2>
                <p class="text-slate-500 mt-1 text-sm">
                    Ringkasan qty pemesanan berdasarkan edisi (mirip struktur Excel)
                </p>
            </div>

            <!-- Filter Edisi -->
            <form method="GET" class="flex items-center gap-3">
                <select name="edisi" onchange="this.form.submit()"
                        class="rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Pilih Edisi —</option>
                    @foreach($edisiList as $edisi)
                        <option value="{{ $edisi }}" @selected($selectedEdisi === $edisi)>
                            {{ $edisi }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(!$report)
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-16 text-center">
                <div class="text-slate-400">
                    Pilih edisi terlebih dahulu untuk melihat report.
                </div>
            </div>
        @else
            <!-- ==================== SUMMARY CARDS ==================== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-xs font-medium text-blue-600 mb-1">Total Unit Aktif</div>
                    <div class="text-2xl font-bold text-blue-800">
                        {{ number_format($report['total_aktif']) }}
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-xs font-medium text-emerald-600 mb-1">Total Unit Pasif</div>
                    <div class="text-2xl font-bold text-emerald-800">
                        {{ number_format($report['total_pasif']) }}
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-xs font-medium text-violet-600 mb-1">Grand Total</div>
                    <div class="text-2xl font-bold text-violet-800">
                        {{ number_format($report['grand_total']) }}
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <div class="text-xs font-medium text-amber-600 mb-1">Edisi</div>
                    <div class="text-2xl font-bold text-amber-800">
                        {{ $report['edisi'] }}
                    </div>
                </div>
            </div>

            <!-- ==================== TABEL UTAMA (mirip Excel) ==================== -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80">
                    <h3 class="font-semibold text-slate-800">
                        Order Majalah Sahabat biMBA — Edisi {{ $report['edisi'] }}
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                                <th class="text-left py-3 px-5 font-medium w-24">Kode</th>
                                <th class="text-left py-3 px-5 font-medium">Kategori Pemesanan</th>
                                <th class="text-right py-3 px-5 font-medium w-36">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">

                            <!-- ===== UNIT AKTIF ===== -->
                            <tr class="bg-blue-50/60">
                                <td colspan="3" class="py-2.5 px-5 font-semibold text-blue-800 text-xs uppercase tracking-wider">
                                    Unit Aktif
                                </td>
                            </tr>

                            @foreach($report['rows_aktif'] as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-5 font-medium text-slate-700">{{ $row['kode'] }}</td>
                                <td class="py-2.5 px-5 text-slate-700">{{ $row['label'] }}</td>
                                <td class="py-2.5 px-5 text-right font-semibold {{ $row['qty'] > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ number_format($row['qty']) }}
                                </td>
                            </tr>
                            @endforeach

                            <tr class="bg-blue-50/40 font-semibold">
                                <td class="py-3 px-5" colspan="2">Total Pemesanan Unit Aktif</td>
                                <td class="py-3 px-5 text-right text-blue-700">
                                    {{ number_format($report['total_aktif']) }}
                                </td>
                            </tr>

                            <!-- ===== UNIT PASIF ===== -->
                            <tr class="bg-emerald-50/60">
                                <td colspan="3" class="py-2.5 px-5 font-semibold text-emerald-800 text-xs uppercase tracking-wider">
                                    Unit Pasif
                                </td>
                            </tr>

                            @foreach($report['rows_pasif'] as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-5 font-medium text-slate-700">{{ $row['kode'] }}</td>
                                <td class="py-2.5 px-5 text-slate-700">
                                    {{ $row['label'] }}
                                    @if($row['qty'] == 0 && in_array($row['kode'], ['P1', 'P3']))
                                        <span class="text-xs text-slate-400">(belum ada data)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-5 text-right font-semibold {{ $row['qty'] > 0 ? 'text-slate-800' : 'text-slate-400' }}">
                                    {{ number_format($row['qty']) }}
                                </td>
                            </tr>
                            @endforeach

                            <tr class="bg-emerald-50/40 font-semibold">
                                <td class="py-3 px-5" colspan="2">Total Pemesanan Unit Pasif</td>
                                <td class="py-3 px-5 text-right text-emerald-700">
                                    {{ number_format($report['total_pasif']) }}
                                </td>
                            </tr>

                            <!-- ===== GRAND TOTAL ===== -->
                            <tr class="bg-violet-50 font-bold">
                                <td class="py-4 px-5" colspan="2">GRAND TOTAL PEMESANAN</td>
                                <td class="py-4 px-5 text-right text-violet-800 text-lg">
                                    {{ number_format($report['grand_total']) }}
                                </td>
                            </tr>

                            <tr class="bg-slate-100">
                                <td class="py-3 px-5 font-semibold" colspan="2">ORDER CETAK MAJALAH</td>
                                <td class="py-3 px-5 text-right font-bold text-slate-800">
                                    {{ number_format($report['grand_total']) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Back -->
        <div class="mt-10">
            <a href="{{ route('import.pasif.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-blue-600 transition-colors">
                <span class="text-lg leading-none">←</span>
                Kembali ke Unit Pasif
            </a>
        </div>

    </div>
</div>
</body>
</html>