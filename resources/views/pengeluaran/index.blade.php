<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        .nominal { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Pengeluaran</h1>
                <p class="text-gray-600">Rekap gabungan biMBA Shop, Kasdana &amp; Manual</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('import.casdana') }}"
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    Lihat Data Kasdana
                </a>
                <a href="{{ route('import.bimbashop') }}"
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    Lihat Data biMBA Shop
                </a>
            </div>
        </div>

        {{-- ===== Filter periode ===== --}}
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                           class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                           class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700">
                    Terapkan
                </button>
            </form>
        </div>

        {{-- ===== Kartu ringkasan ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">biMBA Shop</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 nominal">Rp {{ number_format($bimbashopTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $bimbashopCount }} order completed</p>
            </div>

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kasdana</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 nominal">Rp {{ number_format($casdanaTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $casdanaCount }} transaksi settled/paid</p>
            </div>

            <div class="bg-white rounded-3xl shadow p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Manual (Rekap)</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 nominal">Rp {{ number_format($manualTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $manualCount }} rekap</p>
            </div>

            <div class="bg-blue-600 rounded-3xl shadow p-5">
                <p class="text-xs font-semibold text-blue-100 uppercase tracking-wide">Total Pengeluaran</p>
                <p class="mt-2 text-2xl font-bold text-white nominal">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-blue-100 mt-1">
                    {{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}
                </p>
            </div>

        </div>

        {{-- ===== Rekap Manual (bukan detail per order, hanya per rekap_number) ===== --}}
        <div class="bg-white rounded-3xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">Rekap Manual</h2>
                <p class="text-xs text-gray-500">Diambil dari nomor rekap (manual_realisasi), bukan detail per order</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th>No. Rekap</th>
                            <th>Tanggal Bayar</th>
                            <th class="text-center">Jumlah Order</th>
                            <th class="text-right">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($manualRekap as $rekap)
                            <tr>
                                <td class="font-medium text-gray-800">{{ $rekap->rekap_number }}</td>
                                <td>{{ $rekap->tgl_bayar ? \Carbon\Carbon::parse($rekap->tgl_bayar)->format('d M Y') : '-' }}</td>
                                <td class="text-center">{{ $rekap->jumlah_order }}</td>
                                <td class="text-right nominal">Rp {{ number_format($rekap->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-400 py-6">
                                    Belum ada rekap manual pada periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>