<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edisi {{ $edisi }} - {{ strtoupper($kategori) }} | biMBA AIUEO</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        .truncate { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nominal { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edisi {{ $edisi }}</h1>
                <p class="text-gray-600">Kategori : {{ strtoupper($kategori) }}</p>
            </div>
            <div>
                <a href="{{ route('majalah.2026.diproses', $edisi) }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                    ← Kembali
                </a>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left w-10">No</th>
                        <th class="text-left">No Order</th>
                        <th class="text-left">Unit</th>
                        <th class="text-left">Tanggal Order</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-center">Qty</th>
                        <th class="text-left">Payment Date</th>
                        <th class="text-left">Alamat</th>
                        <th class="text-left">Kab/Kota</th>
                        <th class="text-left">Distribusi</th>
                        
                        
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-center">{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $item->id_pesan }}</td>
                        <td>{{ $item->nama_unit }}</td>
                        <td>{{ $item->tgl_pesan }}</td>
                        <td class="truncate">{{ $item->pesanan }}</td>
                        <td class="text-center font-medium">{{ $item->item_qty }}</td>
                        <td>{{ $item->payment_date }}</td>
                        <td class="truncate">{{ $item->kirim }}</td>
                        <td>{{ $item->kab_kota_provinsi }}</td>
                       @php
                            $status = strtolower(trim($item->status_kirim));
                        @endphp

                        <td class="text-center">
                            @if($status == 'dikirim')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-300">
                                    Dikirim
                                </span>

                            @elseif($status == 'diambil')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-300">
                                    Diambil
                                </span>

                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ $item->status_kirim }}
                                </span>
                            @endif
                        </td>
                        
                        
                        
                        
                        
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-16 text-gray-500">
                            Tidak ada data.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>
                Menampilkan <strong>{{ $data->count() }}</strong> data 
                <span class="text-gray-400">(Total: {{ $data->total() ?? $data->count() }} data)</span>
            </div>
            <div>
                {!! $data->links() !!}
            </div>
        </div>
        @endif

    </div>

</body>
</html>