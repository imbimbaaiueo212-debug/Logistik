<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    
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
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">
            
            <!-- Header + Filter Kategori -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">

                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Picking List - Jakarta Aktif</h1>
                    <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Jakarta Aktif</p>
                </div>

                <!-- Filter Kategori -->
                <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
                    <a href="{{ route('picking.jakarta.aktif') }}" 
                    class="px-6 py-3 rounded-3xl font-medium transition-all {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                        Semua
                    </a>
                    <a href="{{ route('picking.jakarta.aktif') }}?kategori=Modul" 
                    class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                        🟢 Modul
                    </a>
                    <a href="{{ route('picking.jakarta.aktif') }}?kategori=Majalah" 
                    class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                        🔵 Majalah
                    </a>
                    <a href="{{ route('picking.jakarta.aktif') }}?kategori=Sertifikat" 
                    class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                        🔴 Sertifikat
                    </a>
                </div>

                <div>
                    <a href="{{ route('picking.index') }}"
                    class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                        ← Kembali
                    </a>
                </div>
            </div>

            
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No PL / ID Pesan</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Cari No PL...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="nama_unit" value="{{ request('nama_unit') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Unit...">
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
                    <a href="{{ route('picking.jakarta.aktif') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm" id="picking-table">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">ID Pesan</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-center px-4 py-3">Billing Last Name</th>
                        <th class="text-center px-4 py-3">Kategori</th>
                        <th class="text-left px-4 py-3">Payment Date</th>
                        <th class="text-center px-4 py-3">Waktu Estimasi</th>
                        <th class="text-center px-4 py-3">Distribusi</th>
                        <th class="text-center px-4 py-3">Waktu Terima (RA, PL, dan PS)</th>

                        <!-- Kolom yang bisa di-toggle -->
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
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->billing_last_name ?? '-' }} 
                            @if($item->billing_company)
                                <br><small class="text-gray-500">{{ $item->billing_company }}</small>
                            @endif
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

                // ==============================
                // SERTIFIKAT
                // ==============================
                if (
                    str_contains($kategoriLower, 'sertifikat') ||
                    str_contains($namaBarangLower, 'sertifikat')
                ) {
                    return $sku
                        ? $sku . ' - ' . $kategori
                        : $kategori;
                }

                // ==============================
                // MAJALAH
                // ==============================
                elseif (
                    str_contains($kategoriLower, 'majalah') ||
                    str_contains($namaBarangLower, 'majalah')
                ) {
                    return $label
                        ? $label . ' - ' . $kategori
                        : $kategori;
                }

                // ==============================
                // NORMAL
                // ==============================
                else {
                    return $kategori
                        ?? $namaBarang
                        ?? '-';
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

                        <!-- Waktu Terima -->
                        <td class="waktu-terima px-4 py-3 text-center whitespace-nowrap">
                            @if($item->tgl_terima)
                                <span class="text-emerald-600 font-medium">{{ \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- STATUS PERSIAPAN -->
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

                        <!-- TANGGAL PICKING -->
                        <td class="extra-col px-4 py-3 text-center whitespace-nowrap {{ $item->tgl_terima ? '' : 'hidden' }}">
                            @if($item->tgl_picking)
                                {{ \Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- PIC -->
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
                                   class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    ✏️
                                </a>

                                <a href="#" 
                                   onclick="if(confirm('Hapus Picking ini?')) window.location.href='{{ route('picking.destroy', $item->id) }}'"
                                   class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                    🗑
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-16 text-gray-500">
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

   <script>
document.addEventListener('DOMContentLoaded', () => {

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

        const status =
            row.querySelector('.status-select')?.value ?? '';

        const pic =
            row.querySelector('.pic-select')?.value ?? '';

        return status === 'Sudah' && pic !== '';
    }

    function toggleRowControls(row) {

        const completed = isRowCompleted(row);

        row.querySelector('.checklist-item')?.toggleAttribute('disabled', completed);
        row.querySelector('.status-select')?.toggleAttribute('disabled', completed);
        row.querySelector('.pic-select')?.toggleAttribute('disabled', completed);

        row.querySelectorAll('a').forEach(a => {

            if (
                a.href.includes('edit') ||
                a.getAttribute('onclick')
            ) {
                a.style.pointerEvents = completed ? 'none' : 'auto';
                a.style.opacity = completed ? '.4' : '1';
            }

        });

        row.classList.toggle('processed-row', completed);
    }

    // ===============================
    // CHECKLIST
    // ===============================

    document.querySelectorAll('.checklist-item').forEach(chk => {

        chk.addEventListener('change', async function () {

            const row = this.closest('tr');

            try {

                await postData(
                    '{{ route("picking.checklist.update") }}',
                    {
                        id: this.dataset.id,
                        checked: this.checked
                    }
                );

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

    // ===============================
    // STATUS
    // ===============================

    document.querySelectorAll('.status-select').forEach(select => {

        select.addEventListener('change', async function () {

            const row = this.closest('tr');

            try {

                await postData(
                    '{{ route("picking.status.update") }}',
                    {
                        id: this.dataset.id,
                        status_persiapan: this.value
                    }
                );

                this.classList.remove(
                    'bg-green-100',
                    'text-green-700',
                    'bg-red-100',
                    'text-red-700'
                );

                if (this.value === 'Sudah') {

                    this.classList.add(
                        'bg-green-100',
                        'text-green-700'
                    );

                    const tgl = row.querySelector('.tanggal-picking');

                    if (tgl) {

                        tgl.innerHTML =
                            `<span class="text-emerald-600 font-medium">${formatTanggalHariIni()}</span>`;

                    }

                } else {

                    this.classList.add(
                        'bg-red-100',
                        'text-red-700'
                    );

                }

                toggleRowControls(row);

            } catch (e) {

                alert(e.message);

                location.reload();

            }

        });

    });

    // ===============================
    // PIC
    // ===============================

    document.querySelectorAll('.pic-select').forEach(select => {

        select.addEventListener('change', async function () {

            const row = this.closest('tr');

            try {

                await postData(
                    '{{ route("picking.pic.update") }}',
                    {
                        id: this.dataset.id,
                        pic: this.value
                    }
                );

                toggleRowControls(row);

            } catch (e) {

                alert(e.message);

            }

        });

    });

    // ===============================
    // INITIAL
    // ===============================

    document.querySelectorAll('tr[data-id]').forEach(row => {
        toggleRowControls(row);
    });

    updateHeaderVisibility();

});
</script>
</body>
</html>