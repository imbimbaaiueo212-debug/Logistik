<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jakarta Printed - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        .badge { padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jakarta Printed</h1>
                <p class="text-gray-600">Data yang sudah diproses & siap dicetak</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('order.jakarta-aktif') }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    ← Kembali ke Jakarta Aktif
                </a>
                <button onclick="window.print()" 
                        class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-emerald-700 flex items-center gap-2">
                    🖨️ Print Halaman Ini
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Pesan</label>
                    <input type="text" name="id_pesan" value="{{ request('id_pesan') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="nama_unit" value="{{ request('nama_unit') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div class="flex items-end gap-3 pt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700">Terapkan Filter</button>
                    <a href="{{ route('order.jakarta-printed') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">Reset</a>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-3 text-left">ID Pesan</th>
                        <th class="px-4 py-3 text-left">Nama Unit</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-left">Alamat Kirim</th>
                        <th class="px-4 py-3 text-left">Pesanan</th>
                        <th class="px-4 py-3 text-left">Jasa Kurir</th>
                        <th class="px-4 py-3 text-left">Service</th>
                        <th class="px-4 py-3 text-left">Distribusi</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-left">Tanggal Proses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $item->id_pesan }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit }}</td>
                        <td class="px-4 py-3">{{ $item->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kirim }}</td>
                        <td class="px-4 py-3">{{ $item->pesanan }}</td>
                        <td class="px-4 py-3">{{ $item->ekspedisi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->service_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($item->status_kirim === 'Dikirim')
                                <span class="badge bg-emerald-100 text-emerald-700">Dikirim</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700">Diambil</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($item->processed_at)
                                {{ \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-gray-500">
                            Belum ada data yang sudah diproses.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->count() > 0)
        <div class="mt-6 flex justify-between text-sm text-gray-600">
            <div>Menampilkan <strong>{{ $data->count() }}</strong> data</div>
            <div>{{ $data->links() }}</div>
        </div>
        @endif
    </div>
</body>
</html>