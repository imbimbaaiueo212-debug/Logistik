<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan Majalah PUW1</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            white-space: nowrap;
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
                <a href="{{ route('pesanan-majalah-puw1.index') }}"
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    ← Kembali
                </a>
            </div>

            <h1 class="text-3xl font-bold text-gray-800">
                Detail Pesanan Majalah PUW1
            </h1>

            <p class="text-gray-600 mt-1">
                {{ $data->judul ?? 'Pesanan Majalah PUW1' }}
                —
                {{ $data->bulan ?? '-' }}
                {{ $data->tahun ?? '' }}
            </p>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- ALERT --}}
    {{-- ========================================================= --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- INFORMASI PERIODE --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow p-6 mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4">
            Informasi Periode
        </h2>

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

           <div class="lg:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Contact Person</p>
                <p class="font-semibold text-gray-800">
                    @php
                        $names  = array_filter(array_map('trim', explode(',', $data->contact_person ?? '')));
                        $phones = array_filter(array_map('trim', explode(',', $data->telepon_contact_person ?? '')));

                        $combined = [];
                        foreach ($names as $i => $name) {
                            $phone = $phones[$i] ?? null;
                            $combined[] = $phone
                                ? "{$name} ({$phone})"
                                : $name;
                        }
                    @endphp

                    {{ !empty($combined) ? implode(', ', $combined) : '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- TABEL UNIT --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                Daftar Unit PUW1
            </h2>

            <span class="text-sm text-gray-500">
                Total Unit:
                <strong>{{ $data->units->count() }}</strong>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="px-4 py-3 text-center">No</th>
                        <th class="px-4 py-3">Nama Unit</th>
                        <th class="px-4 py-3">No Cabang</th>
                        <th class="px-4 py-3">Kabupaten / Kota</th>
                        <th class="px-4 py-3 text-center">Jumlah Pesanan</th>
                        <th class="px-4 py-3">Alamat Unit</th>
                        <th class="px-4 py-3">Telepon</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @php
                        $no = 1;
                        $totalPesanan = 0;
                    @endphp

                    @forelse($data->units as $unit)
                        @php
                            $totalPesanan += (float) ($unit->jumlah_pesanan ?? 0);
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center">
                                {{ $no++ }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ $unit->nama_unit ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $unit->no_cabang ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $unit->kabupaten_kota ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-center font-semibold">
                                {{-- Dibulatkan di tampilan, database tetap desimal --}}
                                {{ number_format(round((float) ($unit->jumlah_pesanan ?? 0)), 0, ',', '.') }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $unit->alamat_unit ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $unit->telepon ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 text-gray-500">
                                <div class="text-4xl mb-3">📭</div>
                                <p class="font-semibold">Belum ada data unit</p>
                                <p class="text-sm mt-1">Silakan import Excel PUW1.</p>
                            </td>
                        </tr>
                    @endforelse

                    @if($data->units->count() > 0)
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="4" class="px-4 py-3 text-right">
                                TOTAL
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{-- Total juga dibulatkan di tampilan --}}
                                {{ number_format(round($totalPesanan), 0, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function confirmDelete() {
        if (confirm('Yakin ingin menghapus seluruh data periode PUW1 ini?\nSemua unit di dalamnya juga akan ikut terhapus.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</body>
</html>