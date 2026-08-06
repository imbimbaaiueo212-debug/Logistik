<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QC Outgoing - Order Manual - biMBA Logistik</title>
    
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
        <h1 class="text-3xl font-bold text-gray-800">QC Outgoing - Order Manual</h1>
        <p class="text-gray-600">Quality Control dari Picking Order Manual</p>
    </div>

    <div class="flex items-center gap-2">
        {{-- Filter Kategori --}}
        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
            <a href="{{ route('qc-outgoing.index') }}"
           class="px-5 py-2.5  rounded-xl hover:border-blue-500 text-gray-700 hover:text-blue-700 transition">
            Kembali
        </a>
            <a href="{{ route('qc-outgoing.order-manual') }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                Semua
            </a>

            <a href="{{ route('qc-outgoing.order-manual', ['kategori' => 'Modul']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🟢 Modul
            </a>

            <a href="{{ route('qc-outgoing.order-manual', ['kategori' => 'Majalah']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔵 Majalah
            </a>

            <a href="{{ route('qc-outgoing.order-manual', ['kategori' => 'Sertifikat']) }}"
               class="px-5 py-2.5 rounded-3xl font-medium transition-all
               {{ request('kategori') == 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔴 Sertifikat
            </a>
        </div>
    </div>
</div>

        {{-- Flash --}}
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
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No PL / ID Pesan</label>
                    <select name="search" id="filter-search" class="w-full">
                        <option value="">-- Semua --</option>
                        @foreach($noPlList ?? [] as $no)
                            <option value="{{ $no }}" {{ request('search') == $no ? 'selected' : '' }}>
                                {{ $no }}
                            </option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('qc-outgoing.order-manual') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
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
                        <th class="text-left px-4 py-3">Status QC</th>
                        <th class="text-left px-4 py-3">PIC QC</th>
                        <th class="text-left px-4 py-3">Kode QC</th>
                        <th class="text-left px-4 py-3">Keterangan</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $index => $item)
                        @php
                            $qc = $item->manualQcOutgoing ?? null;
                            $statusQc  = $qc->status_qc ?? 'Pending';
                            $picQc     = $qc->pic_qc ?? null;
                            $kodeQc    = $qc->kode_qc ?? null;
                            $keterangan = $qc->keterangan ?? null;
                            $isLolos   = $statusQc === 'Lolos';
                        @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">{{ $data->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->no_pl ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $item->grup ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->kategori_order ?? $item->pesanan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->pic ?? '-' }}
                        </td>

                        <form method="POST" action="{{ route('qc-outgoing.manual.store') }}">
                            @csrf
                            <input type="hidden" name="manual_picking_id" value="{{ $item->id }}">

                            {{-- STATUS QC --}}
                            <td class="px-4 py-3">
                                @if($isLolos)
                                    <span class="inline-block px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
                                        {{ $statusQc }}
                                    </span>
                                    <input type="hidden" name="status_qc" value="{{ $statusQc }}">
                                @else
                                    <select name="status_qc"
                                            class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full">
                                        <option value="Pending" {{ $statusQc == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Lolos"   {{ $statusQc == 'Lolos'   ? 'selected' : '' }}>Lolos</option>
                                        <option value="Reject"  {{ $statusQc == 'Reject'  ? 'selected' : '' }}>Reject</option>
                                        <option value="Revisi"  {{ $statusQc == 'Revisi'  ? 'selected' : '' }}>Revisi</option>
                                    </select>
                                @endif
                            </td>

                            {{-- PIC QC --}}
                            <td class="px-4 py-3">
                                @if($picQc)
                                    <span>{{ preg_replace('/^\d+\s*-\s*/', '', $picQc) }}</span>
                                    <input type="hidden" name="pic_qc" value="{{ $picQc }}">
                                @else
                                    <select name="pic_qc"
                                            class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full"
                                            required>
                                        <option value="">Pilih PIC</option>
                                        <option value="01 - Aep Saefudin">01 - Aep Saefudin</option>
                                        <option value="02 - Yusuf Supena">02 - Yusuf Supena</option>
                                        <option value="03 - Ramdhan Yusuf">03 - Ramdhan Yusuf</option>
                                        <option value="04 - Usman Agung Permana">04 - Usman Agung Permana</option>
                                    </select>
                                @endif
                            </td>

                            {{-- KODE QC --}}
                            <td class="px-4 py-3">
                                @if($kodeQc)
                                    <span>{{ $kodeQc }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- KETERANGAN --}}
                            <td class="px-4 py-3">
                                @if($isLolos)
                                    {{ $keterangan ?? '-' }}
                                    <input type="hidden" name="keterangan" value="{{ $keterangan }}">
                                @else
                                    <input type="text"
                                           name="keterangan"
                                           value="{{ $keterangan }}"
                                           class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full"
                                           placeholder="Keterangan QC">
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-3 text-center">
                                @if($isLolos)
                                    <span class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">
                                        ✓ Selesai
                                    </span>
                                @else
                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                                        {{ $picQc ? 'Update QC' : 'Simpan QC' }}
                                    </button>
                                @endif
                            </td>
                        </form>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-16 text-gray-500">
                            Belum ada data Order Manual yang siap di-QC.
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

            $('#filter-search').select2({ ...config, placeholder: '-- Semua --' });
            $('#filter-nama-unit').select2({ ...config, placeholder: '-- Semua Unit --' });
            $('#filter-grup').select2({ ...config, placeholder: '-- Semua Grup --' });
        });
    </script>
</body>
</html>