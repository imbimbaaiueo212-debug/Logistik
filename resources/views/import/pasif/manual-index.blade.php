<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasif Manual - biMBA AIUEO Logistik</title>
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
                        <h2 class="text-3xl font-bold text-gray-800">Pasif Manual</h2>
                        <p class="text-gray-500 mt-1">Daftar periode pesanan majalah pasif (input manual)</p>
                    </div>
                    <a href="{{ route('import.pasif.manual.create') }}"
                       class="px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-2xl shadow transition">
                        + Create Manual
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl">
                        {!! session('success') !!}
                    </div>
                @endif

                <div class="bg-white rounded-3xl shadow border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-6 py-4 font-semibold text-gray-600">No</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Edisi</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Judul</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Periode</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Bulan / Tahun</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Total Unit</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Total Qty</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">No PS</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Status</th>
                                    <th class="px-6 py-4 font-semibold text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($periodes as $index => $periode)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">{{ $periodes->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $periode->edisi }}</td>
                                        <td class="px-6 py-4">{{ $periode->judul ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $periode->periode ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $periode->bulan ?? '-' }} {{ $periode->tahun ?? '' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                                {{ $periode->transaksis_count ?? 0 }} unit
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-semibold">
                                            {{ number_format($periode->transaksis_sum_jumlah ?? 0) }}
                                        </td>
                                        <td class="px-6 py-4">{{ $periode->no_ps ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            @if($periode->status === 'aktif')
                                                <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">Aktif</span>
                                            @else
                                                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('import.pasif.manual.show', $periode->id) }}"
                                                class="text-green-600 hover:text-rose-800 font-medium">
                                                    Detail
                                                </a>
                                                <a href="{{ route('import.pasif.manual.edit', $periode->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">
                                                    Edit
                                                </a>
                                                <form action="{{ route('import.pasif.manual.destroy', $periode->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                            Belum ada data Pasif Manual.
                                            <a href="{{ route('import.pasif.manual.create') }}" class="text-rose-600 hover:underline ml-1">
                                                Buat sekarang
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($periodes->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $periodes->links() }}
                        </div>
                    @endif
                </div>

                <div class="mt-10 flex justify-center">
                    <a href="{{ route('import.pasif.index') }}" 
                       class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-rose-600 text-gray-700 hover:text-rose-700 px-8 py-3 rounded-2xl font-medium transition-all">
                        ← Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>