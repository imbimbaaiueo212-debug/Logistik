<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan Majalah - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 10px 8px; font-size: 0.85rem; }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        tr:hover { background-color: #f8fafc; }

        /* Select2 sesuaikan dengan Tailwind */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important;
            padding: 6px 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            color: #1f2937 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
        .select2-dropdown {
            border-radius: 0.75rem !important;
            border: 1px solid #d1d5db !important;
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
{{-- TOMBOL MISMATCH (jika ada data) --}}
{{-- ========================================================= --}}
@php
    $listMismatch = session('unit_nama_mismatch');
    if (empty($listMismatch) && isset($mismatches) && $mismatches->count() > 0) {
        $listMismatch = $mismatches->map(fn ($m) => [
            'no_cab'      => $m->no_cab,
            'nama_excel'  => $m->nama_excel,
            'nama_master' => $m->nama_master,
        ])->toArray();
    }
    $listMismatch = $listMismatch ?? [];
@endphp

@if(count($listMismatch) > 0)
<div class="mb-6">
    <button type="button"
            onclick="document.getElementById('mismatchModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow transition">
        ⚠️ Nama Unit Tidak Match
        <span class="bg-white text-orange-600 text-xs font-bold px-2 py-0.5 rounded-full">
            {{ count($listMismatch) }}
        </span>
    </button>
</div>
@endif

{{-- ========================================================= --}}
{{-- MODAL MISMATCH --}}
{{-- ========================================================= --}}
@if(count($listMismatch) > 0)
<div id="mismatchModal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40"
         onclick="document.getElementById('mismatchModal').classList.add('hidden')"></div>

    {{-- Panel --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-orange-200 bg-orange-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚠️</span>
                <div>
                    <h3 class="font-bold text-orange-900 text-lg">Nama Unit Tidak Match</h3>
                    <p class="text-sm text-orange-700">
                        {{ count($listMismatch) }} unit tidak masuk ke data majalah
                    </p>
                </div>
            </div>
            <button type="button"
                    onclick="document.getElementById('mismatchModal').classList.add('hidden')"
                    class="text-orange-600 hover:text-orange-800 text-2xl leading-none font-bold px-2">
                ×
            </button>
        </div>

        {{-- Body (scrollable) --}}
        <div class="overflow-y-auto flex-1 p-0">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-orange-100">
                    <tr class="border-b border-orange-200">
                        <th class="px-4 py-3 text-left text-orange-900">No</th>
                        <th class="px-4 py-3 text-left text-orange-900">No Cab</th>
                        <th class="px-4 py-3 text-left text-orange-900">Nama di Excel</th>
                        <th class="px-4 py-3 text-left text-orange-900">Nama di Unit Kemitraan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-100">
                    @foreach($listMismatch as $i => $m)
                        <tr class="hover:bg-orange-50/60">
                            <td class="px-4 py-3 text-gray-600">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $m['no_cab'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-red-600 font-medium">{{ $m['nama_excel'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-emerald-700 font-medium">{{ $m['nama_master'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-orange-100 bg-gray-50 flex justify-end">
            <button type="button"
                    onclick="document.getElementById('mismatchModal').classList.add('hidden')"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-xl font-semibold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- Auto buka modal jika baru dari import (ada flash session) --}}
@if(session('unit_nama_mismatch'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mismatchModal')?.classList.remove('hidden');
    });
</script>
@endif
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
                <p class="font-semibold text-gray-800">
                    @php
                        $namaBulan = [
                            1  => 'Januari',
                            2  => 'Februari',
                            3  => 'Maret',
                            4  => 'April',
                            5  => 'Mei',
                            6  => 'Juni',
                            7  => 'Juli',
                            8  => 'Agustus',
                            9  => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ];

                        $periodeText = '-';
                        if (!empty($data->periode) && preg_match('/^\d{4}-(\d{2})$/', $data->periode, $m)) {
                            $periodeText = $namaBulan[(int) $m[1]] ?? $data->periode;
                        }
                    @endphp
                    {{ $periodeText }}
                </p>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- FILTER UNIT (Select2) --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET"
              action="{{ route('pesanan-majalah.show', $data->id) }}"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

            {{-- Nama Unit --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Unit
                </label>
                <select name="nama_unit" class="select2 w-full">
                    <option value="">-- Semua Unit --</option>
                    @foreach($listNamaUnit as $nama)
                        <option value="{{ $nama }}"
                            {{ request('nama_unit') == $nama ? 'selected' : '' }}>
                            {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- No Cabang --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    No Cabang
                </label>
                <select name="no_cabang" class="select2 w-full">
                    <option value="">-- Semua No Cabang --</option>
                    @foreach($listNoCabang as $cabang)
                        <option value="{{ $cabang }}"
                            {{ request('no_cabang') == $cabang ? 'selected' : '' }}>
                            {{ $cabang }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kabupaten --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kabupaten
                </label>
                <select name="kabupaten" class="select2 w-full">
                    <option value="">-- Semua Kabupaten --</option>
                    @foreach($listKabupaten as $kab)
                        <option value="{{ $kab }}"
                            {{ request('kabupaten') == $kab ? 'selected' : '' }}>
                            {{ $kab }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mitra Pengelola --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Mitra Pengelola
                </label>
                <select name="mitra_pengelolaan" class="select2 w-full">
                    <option value="">-- Semua Mitra --</option>
                    @foreach($listMitraPengelola as $mitra)
                        <option value="{{ $mitra }}"
                            {{ request('mitra_pengelolaan') == $mitra ? 'selected' : '' }}>
                            {{ $mitra }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol --}}
            <div class="flex items-end gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition">
                    🔍 Filter
                </button>

                <a href="{{ route('pesanan-majalah.show', $data->id) }}"
                   class="bg-gray-500 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-gray-600 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ========================================================= --}}
    {{-- TABEL UNIT --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Daftar Unit</h2>
            <span class="text-sm text-gray-500">
                Total Unit: <strong>{{ $totalUnits ?? 0 }}</strong>
                &nbsp;|&nbsp;
                Total Pesanan: <strong>{{ number_format(round($totalPesanan ?? 0), 0, ',', '.') }}</strong>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="px-4 py-3 text-center">No</th>
                        <th class="px-4 py-3">Nama Unit</th>
                        <th class="px-4 py-3">No Cabang</th>
                        <th class="px-4 py-3">Mitra Pengelola</th>
                        <th class="px-4 py-3 text-center">Jumlah Pesanan</th>
                        <th class="px-4 py-3">Alamat Unit</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3">Kabupaten</th>
                        <th class="px-4 py-3">Contact Person</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @php $no = 1; @endphp

                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center">{{ $no++ }}</td>
                            <td class="px-4 py-3 font-medium">{{ $unit->nama_unit ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $unit->no_cabang ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $unit->mitra_pengelolaan ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-semibold">
                                {{ number_format(round($unit->jumlah_pesanan ?? 0), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">{{ $unit->alamat_unit ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $unit->telepon ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $unit->nama_kabupaten ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $unit->contact_person ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-gray-500">
                                <div class="text-4xl mb-3">📭</div>
                                <p class="font-semibold">Tidak ada data unit</p>
                                <p class="text-sm mt-1">Coba ubah filter atau import Excel.</p>
                            </td>
                        </tr>
                    @endforelse

                    @if($units->count() > 0)
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="4" class="px-4 py-3 text-right">TOTAL</td>
                            <td class="px-4 py-3 text-center">
                                {{ number_format(round($totalPesanan), 0, ',', '.') }}
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- jQuery + Select2 --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            placeholder: 'Cari / pilih...',
            allowClear: true,
            width: '100%'
        });
    });

    function confirmDelete() {
        if (confirm('Yakin ingin menghapus seluruh data periode pesanan majalah ini?\nSemua kabupaten dan unit di dalamnya juga akan ikut terhapus.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

</body>
</html>