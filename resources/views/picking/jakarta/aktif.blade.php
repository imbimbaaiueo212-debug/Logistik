<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        
        .status-success { background-color: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; }
        .badge-green { background-color: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-black { background-color: #1f2937; color: #f3f4f6; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }

        .processed-row {
            opacity: 0.65;
            background-color: #f1f5f9 !important;
            color: #64748b;
        }
        
        .pagination {
            display: flex;
            gap: 6px;
        }
        .pagination li a, .pagination li span {
            padding: 8px 14px;
            border-radius: 9999px;
            font-size: 0.9rem;
        }
        .pagination .active span {
            background-color: #4f46e5;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Picking List - Jakarta Aktif</h1>
                <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Jakarta Aktif</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('picking.index') }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    ← Kembali ke Picking Menu
                </a>
                
                <form action="{{ route('picking.generate-all') }}" method="POST" 
                      onsubmit="return confirm('Generate Picking untuk SEMUA order yang belum dibuat picking?')">
                    @csrf
                    <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl font-semibold flex items-center gap-2">
                        🔄 Generate All Picking
                    </button>
                </form>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No PL / ID Pesan</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Cari No PL...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="nama_unit" value="{{ request('nama_unit') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Unit...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>
                <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('picking.jakarta.aktif') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">No PL</th>
                        <th class="text-left px-4 py-3">ID Pesan</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-left px-4 py-3">Customer</th>
                        <th class="text-left px-4 py-3">Tanggal Order</th>
                        <th class="text-left px-4 py-3">Tanggal Picking</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->no_pl ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->id_pesan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3">
                            {{ $item->billing_last_name ?? '-' }} 
                            @if($item->billing_company)
                                <br><small class="text-gray-500">{{ $item->billing_company }}</small>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->tgl_order)
                                {{ \Carbon\Carbon::parse($item->tgl_order)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->tgl_picking)
                                {{ \Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="text-center px-4 py-3">
                            @if($item->status === 'completed' || $item->status === 'printed')
                                <span class="badge-green">✅ Selesai</span>
                            @elseif($item->status === 'draft')
                                <span class="badge-yellow">Draft</span>
                            @else
                                <span class="badge-black">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td class="text-right px-4 py-3 font-semibold">
                            Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-center px-4 py-3">
                            <a href="#" 
                               onclick="if(confirm('Hapus Picking ini?')) window.location.href='{{ route('picking.destroy', $item->id) }}'"
                               class="text-red-600 hover:text-red-800 text-sm">
                                🗑 Hapus
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-16 text-gray-500">
                            Belum ada Picking List yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 bg-white border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <span class="font-medium">{{ $data->firstItem() }}</span> 
                    sampai <span class="font-medium">{{ $data->lastItem() }}</span> 
                    dari total <span class="font-medium">{{ $data->total() }}</span> data
                </div>
                <div>{{ $data->appends(request()->query())->links() }}</div>
            </div>
            @endif
        </div>

    </div>
</body>
</html>