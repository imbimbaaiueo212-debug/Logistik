<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pasif Manual - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.top-nav')

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="flex h-screen overflow-hidden">
        <div class="flex-1 overflow-auto">
            <div class="p-8">

                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Detail Pasif Manual</h2>
                        <p class="text-gray-500 mt-1">Edisi {{ $periode->edisi }} — {{ $periode->judul }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('import.pasif.manual.index') }}"
                           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-medium transition">
                            Kembali
                        </a>
                        <a href="{{ route('import.pasif.manual.create') }}"
                           class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-medium transition">
                            + Create Baru
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl">
                        {!! session('success') !!}
                    </div>
                @endif

                {{-- Info Header --}}
                <div class="bg-white rounded-3xl shadow p-6 mb-8 border border-gray-100">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Edisi</p>
                            <p class="font-semibold text-lg">{{ $periode->edisi }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Judul</p>
                            <p class="font-semibold">{{ $periode->judul ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Periode</p>
                            <p class="font-semibold">{{ $periode->periode ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Bulan / Tahun</p>
                            <p class="font-semibold">{{ $periode->bulan ?? '-' }} {{ $periode->tahun ?? '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">No PS</p>
                            <p class="font-semibold">{{ $periode->no_ps ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Total Qty</p>
                            <p class="font-semibold text-rose-700 text-lg">{{ number_format($total) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Tabel Detail --}}
                <div class="bg-white rounded-3xl shadow border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Unit ({{ $periode->transaksis->count() }})</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3 font-semibold text-gray-600">No</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Id Pesan</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Kode Pesan</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Tgl Pesan</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Minggu</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Nama Unit</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Label</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Jumlah</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Pesanan</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Note</th>
                                    <th class="px-4 py-3 font-semibold text-gray-600">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($periode->transaksis as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">{{ $item->no ?? $loop->iteration }}</td>
                                        <td class="px-4 py-3">{{ $item->id_pesan ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $item->kode_pesan ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            {{ $item->tgl_pesan ? $item->tgl_pesan->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3">{{ $item->minggu ?? '-' }}</td>
                                        <td class="px-4 py-3 font-medium">{{ $item->nama_unit }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                                                {{ $item->label ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold">{{ number_format($item->jumlah) }}</td>
                                        <td class="px-4 py-3">{{ $item->pesanan ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $item->note ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500">{{ $item->keterangan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-4 py-10 text-center text-gray-400">
                                            Belum ada data unit.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>