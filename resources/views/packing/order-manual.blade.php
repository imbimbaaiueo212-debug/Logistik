<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing - Order Manual - biMBA Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }

        .select2-container .select2-selection--single {
            height: 42px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important;
            padding: 6px 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            color: #374151 !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
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

       {{-- Header + Filter Kategori --}}
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Packing - Order Manual</h1>
        <p class="text-gray-600">Packing dari QC Order Manual</p>
    </div>

    <div class="flex items-center gap-2">
        {{-- Filter Kategori --}}
        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
            <a href="{{ route('packing.index') }}"
           class="px-5 py-2.5 border-gray-300 rounded-xl hover:border-blue-500 text-gray-700 hover:text-blue-700 transition">
            Kembali
        </a>
            <a href="{{ route('packing.order-manual') }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                Semua
            </a>

            <a href="{{ route('packing.order-manual', ['kategori' => 'Modul']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🟢 Modul
            </a>

            <a href="{{ route('packing.order-manual', ['kategori' => 'Majalah']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔵 Majalah
            </a>

            <a href="{{ route('packing.order-manual', ['kategori' => 'Sertifikat']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔴 Sertifikat
            </a>
        </div>
    </div>
</div>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-5 py-3 rounded-2xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-5 py-3 rounded-2xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No PL / Nama Unit</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari No PL / Unit..."
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <select name="nama_unit" id="filter-nama-unit" class="w-full">
                        <option value="">-- Semua Unit --</option>
                        @foreach($namaUnitList ?? [] as $unit)
                            <option value="{{ $unit }}" {{ request('nama_unit') == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grup</label>
                    <select name="grup" id="filter-grup" class="w-full">
                        <option value="">-- Semua Grup --</option>
                        @foreach($grupList ?? [] as $g)
                            <option value="{{ $g }}" {{ request('grup') == $g ? 'selected' : '' }}>
                                {{ $g }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Packing</label>
                    <select name="status_packing" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                        <option value="">-- Semua Status --</option>
                        <option value="Pending"  {{ request('status_packing') == 'Pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="Proses"   {{ request('status_packing') == 'Proses'   ? 'selected' : '' }}>Proses</option>
                        <option value="Selesai"  {{ request('status_packing') == 'Selesai'  ? 'selected' : '' }}>Selesai</option>
                        <option value="Batal"    {{ request('status_packing') == 'Batal'    ? 'selected' : '' }}>Batal</option>
                    </select>
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

                <div class="flex items-end gap-3 pt-6 lg:col-span-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('packing.order-manual') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-center px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">No PL</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-center px-4 py-3">Grup</th>
                        <th class="text-center px-4 py-3">Kategori</th>
                        <th class="text-center px-4 py-3">PIC Picking</th>
                        <th class="text-left px-4 py-3">Status Packing</th>
                        <th class="text-left px-4 py-3">Nama Packer</th>
                        <th class="text-center px-4 py-3">Tgl Packing</th>
                        <th class="text-center px-4 py-3">Berat Aktual</th>
                        <th class="text-center px-4 py-3">Koli</th>
                        <th class="text-left px-4 py-3">Keterangan</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
    @forelse($data as $index => $item)
        @php
            $packing   = $item->manualPacking;
            $isSelesai = $packing && $packing->status_packing === 'Selesai';
        @endphp
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-center">{{ $data->firstItem() + $index }}</td>
            <td class="px-4 py-3 font-medium">{{ $item->no_pl ?? '-' }}</td>
            <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
            <td class="px-4 py-3 text-center">{{ $item->grup ?? '-' }}</td>
            <td class="px-4 py-3 text-center">{{ $item->kategori_order ?? '-' }}</td>
            <td class="px-4 py-3 text-center">
                {{ $item->manualPicking->pic ?? $packing->pic_picking ?? '-' }}
            </td>

            @if($packing)
                {{-- Sudah ada record packing --}}
                <form method="POST" action="{{ route('packing.order-manual.update', $packing->id) }}" class="contents">
                    @csrf
                    @method('PUT')

                    {{-- STATUS PACKING --}}
                    <td class="px-4 py-3">
                        @if($isSelesai)
                            <span class="inline-block px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
                                {{ $packing->status_packing }}
                            </span>
                            <input type="hidden" name="status_packing" value="{{ $packing->status_packing }}">
                        @else
                            <select name="status_packing" class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full">
                                <option value="Pending"  {{ $packing->status_packing == 'Pending'  ? 'selected' : '' }}>Pending</option>
                                <option value="Proses"   {{ $packing->status_packing == 'Proses'   ? 'selected' : '' }}>Proses</option>
                                <option value="Selesai"  {{ $packing->status_packing == 'Selesai'  ? 'selected' : '' }}>Selesai</option>
                                <option value="Batal"    {{ $packing->status_packing == 'Batal'    ? 'selected' : '' }}>Batal</option>
                            </select>
                        @endif
                    </td>

                    {{-- NAMA PACKER --}}
                    <td class="px-4 py-3">
                        @if($isSelesai)
                            {{ $packing->nama_packer ?? '-' }}
                            <input type="hidden" name="nama_packer" value="{{ $packing->nama_packer }}">
                        @else
                            <input type="text" name="nama_packer" value="{{ $packing->nama_packer }}"
                                   class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full"
                                   placeholder="Nama Packer">
                        @endif
                    </td>

                    {{-- TGL PACKING --}}
                    <td class="px-4 py-3 text-center">
                        @if($isSelesai)
                            {{ $packing->tgl_packing ? $packing->tgl_packing->format('d/m/Y') : '-' }}
                            <input type="hidden" name="tgl_packing" value="{{ $packing->tgl_packing?->format('Y-m-d') }}">
                        @else
                            <input type="date" name="tgl_packing"
                                   value="{{ $packing->tgl_packing?->format('Y-m-d') ?? now()->format('Y-m-d') }}"
                                   class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-full">
                        @endif
                    </td>

                    {{-- BERAT AKTUAL --}}
                    <td class="px-4 py-3 text-center">
                        @if($isSelesai)
                            {{ $packing->berat_aktual ?? '-' }}
                            <input type="hidden" name="berat_aktual" value="{{ $packing->berat_aktual }}">
                        @else
                            <input type="number" step="0.01" name="berat_aktual" value="{{ $packing->berat_aktual }}"
                                   class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-24" placeholder="0.00">
                        @endif
                    </td>

                    {{-- KOLI --}}
                    <td class="px-4 py-3 text-center">
                        @if($isSelesai)
                            {{ $packing->koli ?? '-' }}
                            <input type="hidden" name="koli" value="{{ $packing->koli }}">
                        @else
                            <input type="text" name="koli" value="{{ $packing->koli }}"
                                   class="border border-gray-300 rounded-lg px-2 py-1 text-sm w-20" placeholder="Koli">
                        @endif
                    </td>

                    {{-- KETERANGAN --}}
                    <td class="px-4 py-3">
                        @if($isSelesai)
                            {{ $packing->keterangan_packing ?? '-' }}
                            <input type="hidden" name="keterangan_packing" value="{{ $packing->keterangan_packing }}">
                        @else
                            <input type="text" name="keterangan_packing" value="{{ $packing->keterangan_packing }}"
                                   class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full"
                                   placeholder="Keterangan">
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td class="px-4 py-3 text-center">
                        @if($isSelesai)
                            <span class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">
                                ✓ Selesai
                            </span>
                        @else
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                                Simpan
                            </button>
                        @endif
                    </td>
                </form>
            @else
                {{-- Belum ada record packing (harusnya otomatis terbuat saat QC Lolos) --}}
                <td class="px-4 py-3" colspan="7">
                    <span class="text-red-500 text-sm">Belum ada data packing. Pastikan QC sudah Lolos & generate packing berjalan.</span>
                </td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="13" class="text-center py-16 text-gray-500">
                Belum ada data QC Order Manual yang Lolos.
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const config = {
                allowClear: true,
                width: '100%',
                language: {
                    noResults: () => "Tidak ditemukan",
                    searching: () => "Mencari..."
                }
            };

            $('#filter-nama-unit').select2({ ...config, placeholder: '-- Semua Unit --' });
            $('#filter-grup').select2({ ...config, placeholder: '-- Semua Grup --' });
        });
    </script>
</body>
</html>