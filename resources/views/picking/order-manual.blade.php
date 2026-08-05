<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

        /* Select2 custom style */
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
            overflow: hidden;
        }
        .select2-search__field {
            border-radius: 0.5rem !important;
            padding: 6px 10px !important;
        }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">
            
        <!-- Header + Filter Kategori -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">

            <div>
                <h1 class="text-3xl font-bold text-gray-800">Picking - Order Manual</h1>
                <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Manual</p>
            </div>    
            <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
                 <a href="{{ route('picking.order-manual') }}"
                    class="px-6 py-3 rounded-3xl font-medium transition-all
                    {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    Semua
                </a>

                <a href="{{ route('picking.order-manual', ['kategori' => 'Modul']) }}"
                    class="px-6 py-3 rounded-3xl font-medium transition-all
                    {{ request('kategori') == 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🟢 Modul
                </a>

                <a href="{{ route('picking.order-manual', ['kategori' => 'Majalah']) }}"
                    class="px-6 py-3 rounded-3xl font-medium transition-all
                    {{ request('kategori') == 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🔵 Majalah
                </a>

                <a href="{{ route('picking.order-manual', ['kategori' => 'Sertifikat']) }}"
                    class="px-6 py-3 rounded-3xl font-medium transition-all
                    {{ request('kategori') == 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🔴 Sertifikat
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
                
                <!-- No PL / ID Pesan (Select2) -->
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

                <!-- Nama Unit (Select2) -->
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

                <!-- Grup (Select2) -->
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

                <!-- Dari Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>

                <!-- Sampai Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                </div>

                <!-- Tombol -->
                <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('picking.order-manual') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel (tetap sama seperti sebelumnya) -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm" id="picking-table">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">ID Pesan</th>
                        <th class="text-left px-4 py-3">ID biMBA Shop</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-center px-4 py-3">Billing Last Name</th>
                        <th class="text-center px-4 py-3">Group</th>
                        <th class="text-center px-4 py-3">Kategori</th>
                        <th class="text-left px-4 py-3">Payment Date</th>
                        <th class="text-center px-4 py-3">Waktu Estimasi</th>
                        <th class="text-center px-4 py-3">Distribusi</th>
                        <th class="text-center px-4 py-3">Waktu Terima (RA, PL, dan PS)</th>
                        <th class="extra-col text-center px-4 py-3 hidden">Status Persiapan</th>
                        <th class="extra-col text-center px-4 py-3 hidden">Tanggal Picking</th>
                        <th class="extra-col text-center px-4 py-3 hidden">PIC</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-gray-50" data-id="{{ $item->id }}">
                        <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $item->no_pl ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->no_ps ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->billing_last_name ?? '-' }} 
                            @if($item->billing_company)
                                <br><small class="text-gray-500">{{ $item->billing_company }}</small>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->grup ?? '-' }}
                        </td>
                        <td class="text-center text-sm">
                            @php
                                $kategoriProduk = $item->pickingItems
                                    ->map(function ($pickingItem) {
                                        $sku = $pickingItem->product?->sku;
                                        $kategori = $pickingItem->product?->kategori;
                                        $namaBarang = $pickingItem->item_name;
                                        $label = $pickingItem->product?->label ?? '';
                                        $kategoriLower = strtolower($kategori ?? '');
                                        $namaBarangLower = strtolower($namaBarang ?? '');

                                        if (str_contains($kategoriLower, 'sertifikat') || str_contains($namaBarangLower, 'sertifikat')) {
                                            return $sku ? $sku . ' - ' . $kategori : $kategori;
                                        } elseif (str_contains($kategoriLower, 'majalah') || str_contains($namaBarangLower, 'majalah')) {
                                            return $label ? $label . ' - ' . $kategori : $kategori;
                                        } else {
                                            return $kategori ?? $namaBarang ?? '-';
                                        }
                                    })
                                    ->filter()
                                    ->unique()
                                    ->implode(' | ');
                            @endphp
                            {{ $kategoriProduk ?: ($item->pesanan ?? '-') }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($item->payment_date)
                                {{ \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($item->waktu_estimasi_persiapan)
                                {{ \Carbon\Carbon::parse($item->waktu_estimasi_persiapan)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="text-center px-4 py-3">
                            {{ $item->ekspedisi ?? '-' }} 
                            {{ $item->service_pengiriman ? '- ' . $item->service_pengiriman : '' }}
                        </td>
                        <td class="waktu-terima px-4 py-3 text-center whitespace-nowrap">
                            @if($item->tgl_terima)
                                <span class="text-emerald-600 font-medium">{{ \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="extra-col px-4 py-3 text-center {{ $item->tgl_terima ? '' : 'hidden' }}">
                            <select class="status-select border rounded-lg px-2 py-1 text-sm
                                @if($item->status_persiapan == 'Sudah') bg-green-100 text-green-700
                                @elseif($item->status_persiapan == 'Belum') bg-red-100 text-red-700
                                @endif"
                                data-id="{{ $item->id }}">
                                <option value="Belum" {{ $item->status_persiapan == 'Belum' || empty($item->status_persiapan) ? 'selected' : '' }}>Belum</option>
                                <option value="Sudah" {{ $item->status_persiapan == 'Sudah' ? 'selected' : '' }}>Sudah</option>
                            </select>
                        </td>
                        <td class="extra-col px-4 py-3 text-center whitespace-nowrap {{ $item->tgl_terima ? '' : 'hidden' }}">
                            @if($item->tgl_picking)
                                {{ \Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="extra-col px-4 py-3 text-center {{ $item->tgl_terima ? '' : 'hidden' }}">
                            <select class="pic-select border rounded-lg px-2 py-1 text-sm" data-id="{{ $item->id }}">
                                <option value="">-- Pilih --</option>
                                <option value="Asep" {{ $item->pic == 'Asep' ? 'selected' : '' }}>Asep</option>
                                <option value="Arif" {{ $item->pic == 'Arif' ? 'selected' : '' }}>Arif</option>
                                <option value="Rama" {{ $item->pic == 'Rama' ? 'selected' : '' }}>Rama</option>
                                <option value="Riky" {{ $item->pic == 'Riky' ? 'selected' : '' }}>Riky</option>
                            </select>
                        </td>
                        <td class="text-center px-4 py-3">
                            <div class="flex items-center justify-center gap-4 text-sm">
                                <label class="flex items-center gap-1 cursor-pointer" title="Checklist">
                                    <input type="checkbox" 
                                        class="w-4 h-4 accent-emerald-600 checklist-item"
                                        data-id="{{ $item->id }}"
                                        {{ $item->tgl_terima ? 'checked' : '' }}>
                                    <span class="text-emerald-600 text-xs font-medium">Cek</span>
                                </label>
                                <a href="{{ route('picking.edit', $item->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">✏️</a>
                                <a href="#" 
                                   onclick="if(confirm('Hapus Picking ini?')) window.location.href='{{ route('picking.destroy', $item->id) }}'"
                                   class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">🗑</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center py-16 text-gray-500">
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

    <!-- jQuery + Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', () => {

    // ===============================
    // INIT SELECT2 (semua filter)
    // ===============================
    const select2Config = {
        allowClear: true,
        width: '100%',
        language: {
            noResults: () => "Tidak ditemukan",
            searching: () => "Mencari..."
        }
    };

    $('#filter-search').select2({
        ...select2Config,
        placeholder: '-- Semua --'
    });

    $('#filter-nama-unit').select2({
        ...select2Config,
        placeholder: '-- Semua Unit --'
    });

    $('#filter-grup').select2({
        ...select2Config,
        placeholder: '-- Semua Grup --'
    });

    // ===============================
    // Fungsi lain (checklist, status, pic) tetap sama
    // ===============================

    function formatTanggalHariIni() {
        return new Date().toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    async function postData(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (!response.ok || result.success === false) {
            throw new Error(result.message || 'Terjadi kesalahan.');
        }
        return result;
    }

    function updateHeaderVisibility() {
        const hasChecked = document.querySelectorAll('.checklist-item:checked').length > 0;
        document.querySelectorAll('th.extra-col').forEach(th => {
            th.classList.toggle('hidden', !hasChecked);
        });
    }

    function isRowCompleted(row) {
        const status = row.querySelector('.status-select')?.value ?? '';
        const pic = row.querySelector('.pic-select')?.value ?? '';
        return status === 'Sudah' && pic !== '';
    }

    function toggleRowControls(row) {
        const completed = isRowCompleted(row);
        row.querySelector('.checklist-item')?.toggleAttribute('disabled', completed);
        row.querySelector('.status-select')?.toggleAttribute('disabled', completed);
        row.querySelector('.pic-select')?.toggleAttribute('disabled', completed);
        row.querySelectorAll('a').forEach(a => {
            if (a.href.includes('edit') || a.getAttribute('onclick')) {
                a.style.pointerEvents = completed ? 'none' : 'auto';
                a.style.opacity = completed ? '.4' : '1';
            }
        });
        row.classList.toggle('processed-row', completed);
    }

    // Checklist
    document.querySelectorAll('.checklist-item').forEach(chk => {
        chk.addEventListener('change', async function () {
            const row = this.closest('tr');
            try {
                await postData('{{ route("picking.checklist.update") }}', {
                    id: this.dataset.id,
                    checked: this.checked
                });
                row.querySelector('.waktu-terima').innerHTML =
                    this.checked
                        ? `<span class="text-emerald-600 font-medium">${formatTanggalHariIni()}</span>`
                        : `<span class="text-gray-400">-</span>`;
                row.querySelectorAll('.extra-col').forEach(col => {
                    col.classList.toggle('hidden', !this.checked);
                });
                updateHeaderVisibility();
            } catch (e) {
                this.checked = !this.checked;
                alert(e.message);
            }
        });
    });

    // Status
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function () {
            const row = this.closest('tr');
            try {
                await postData('{{ route("picking.status.update") }}', {
                    id: this.dataset.id,
                    status_persiapan: this.value
                });
                this.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
                if (this.value === 'Sudah') {
                    this.classList.add('bg-green-100', 'text-green-700');
                } else {
                    this.classList.add('bg-red-100', 'text-red-700');
                }
                toggleRowControls(row);
            } catch (e) {
                alert(e.message);
                location.reload();
            }
        });
    });

    // PIC
    document.querySelectorAll('.pic-select').forEach(select => {
        select.addEventListener('change', async function () {
            const row = this.closest('tr');
            try {
                await postData('{{ route("picking.pic.update") }}', {
                    id: this.dataset.id,
                    pic: this.value
                });
                toggleRowControls(row);
            } catch (e) {
                alert(e.message);
            }
        });
    });

    // Initial
    document.querySelectorAll('tr[data-id]').forEach(row => {
        toggleRowControls(row);
    });
    updateHeaderVisibility();
});
</script>
</body>
</html>