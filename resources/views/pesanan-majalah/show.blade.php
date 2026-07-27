<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan Majalah - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('pesanan-majalah.index') }}"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ← Kembali
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">
                Detail Pesanan Majalah
            </h1>
            <p class="text-gray-600 mt-1">
                {{ $data->judul ?? 'Pesanan Majalah' }} — {{ $data->bulan ?? '-' }} {{ $data->tahun ?? '' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pesanan-majalah.edit', $data->id) }}"
                class="bg-amber-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-amber-600 transition">
                ✏ Edit Periode
            </a>

            <button type="button" onclick="confirmDelete()"
                class="bg-red-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-red-700 transition">
                🗑 Hapus Periode
            </button>

            <form id="deleteForm"
                action="{{ route('pesanan-majalah.destroy', $data->id) }}"
                method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- ALERT --}}
    {{-- ========================================================= --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <div class="flex items-center gap-2">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- INFO PERIODE --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Periode</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Judul</p>
                <p class="font-semibold text-gray-800">{{ $data->judul ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Bulan / Edisi</p>
                <p class="font-semibold text-gray-800">{{ $data->bulan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Tahun</p>
                <p class="font-semibold text-gray-800">{{ $data->tahun ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Periode</p>
                <p class="font-semibold text-gray-800">{{ $data->periode ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABEL UNIT --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Daftar Unit</h2>
            <span class="text-sm text-gray-500">
                Total Unit:
                <strong>
                    {{ $data->kabupaten->sum(fn($k) => $k->units->count()) }}
                </strong>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
               <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="px-4 py-3 text-center">No</th>
                        <th class="px-4 py-3">Nama Unit</th>
                        <th class="px-4 py-3">No Cabang</th>
                        <th class="px-4 py-3 text-center hidden">Jumlah Pesanan</th>
                        <th class="px-4 py-3 text-center">Jumlah Pesanan</th> {{-- ini yang ditampilkan (nilai bulat) --}}
                        <th class="px-4 py-3">Alamat Unit</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3">Kabupaten</th>
                        <th class="px-4 py-3">Contact Person</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @php $no = 1; @endphp

                    @forelse($data->kabupaten as $kabupaten)
                        @foreach($kabupaten->units as $unit)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-medium">{{ $unit->nama_unit ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $unit->no_cabang ?? '-' }}</td>

                                {{-- Nilai asli (disembunyikan) --}}
                                <td class="px-4 py-3 text-center font-semibold hidden">
                                    {{ $unit->jumlah_pesanan ?? 0 }}
                                </td>

                                {{-- Nilai yang sudah dibulatkan (yang terlihat) --}}
                                <td class="px-4 py-3 text-center font-semibold">
                                    {{ number_format(round($unit->jumlah_pesanan ?? 0), 0) }}
                                </td>

                                <td class="px-4 py-3">{{ $unit->alamat_unit ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $unit->telepon ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $kabupaten->nama_kabupaten ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $kabupaten->contact_person ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-gray-500">
                                <div class="text-4xl mb-3">📭</div>
                                <p class="font-semibold">Belum ada data unit</p>
                                <p class="text-sm mt-1">Silakan import Excel untuk mengisi data unit.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function confirmDelete() {
        if (confirm('Yakin ingin menghapus seluruh data periode pesanan majalah ini?\nSemua kabupaten dan unit di dalamnya juga akan ikut terhapus.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</body>
</html>