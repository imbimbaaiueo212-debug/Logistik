<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data DLC - biMBA AIUEO Logistik</title>
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

        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Data Pemesanan Majalah DLC</h2>
            <a href="{{ route('import.dlc.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition">
                + Tambah Data DLC
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-xl mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Edisi</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Judul</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Periode</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">No PS</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Jumlah Unit</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Total Qty</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($periodes as $periode)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $periode->edisi }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $periode->judul }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $periode->periode }}
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $periode->no_ps ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $periode->pesanan_count }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-800">
                                {{ $periode->pesanan_sum_qty ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs rounded-full font-medium
                                    {{ $periode->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($periode->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('import.dlc.show', $periode->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Detail
                                </a>

                                <a href="{{ route('import.dlc.edit', $periode->id) }}" 
                                   class="text-amber-600 hover:text-amber-800 font-medium text-sm">
                                    Mode Edit
                                </a>

                                <form action="{{ route('import.dlc.destroy', $periode->id) }}" 
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                                Belum ada data DLC.
                                <a href="{{ route('import.dlc.create') }}" class="text-blue-600 hover:underline ml-1">
                                    Tambah sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periodes->hasPages())
            <div class="mt-6">
                {{ $periodes->links() }}
            </div>
        @endif

        <div class="mt-10 flex justify-center">
            <a href="{{ route('order-manual.index') }}" 
               class="flex items-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-6 py-3 rounded-2xl font-medium transition">
                ← Kembali
            </a>
        </div>

    </div>
</div>

</body>
</html>