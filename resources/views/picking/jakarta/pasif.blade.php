<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - Jakarta Pasif - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }

        .processed-row {
            opacity: 0.65;
            background-color: #f1f5f9 !important;
            color: #64748b;
        }

        .pagination { display: flex; gap: 6px; }
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
            <h1 class="text-3xl font-bold text-gray-800">Picking List - Jakarta Pasif</h1>
            <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Jakarta Pasif</p>
        </div>
        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
            <a href="{{ route('picking.index') }}"
               class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                Menu
            </a>
            <a href="{{ route('picking.jakarta.pasif') }}"
               class="px-6 py-3 rounded-3xl font-medium transition-all {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                Semua
            </a>
            <a href="{{ route('picking.jakarta.pasif') }}?kategori=Modul"
               class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🟢 Modul
            </a>
            <a href="{{ route('picking.jakarta.pasif') }}?kategori=Majalah"
               class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔵 Majalah
            </a>
            <a href="{{ route('picking.jakarta.pasif') }}?kategori=Sertifikat"
               class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                🔴 Sertifikat
            </a>
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
                <a href="{{ route('picking.jakarta.pasif') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
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
                    <th class="extra-col text-center px-4 py-3 hidden">Status Persiapan</th>
                    <th class="extra-col text-center px-4 py-3 hidden">Tanggal Picking</th>
                    <th class="extra-col text-center px-4 py-3 hidden">PIC</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
    @forelse($data as $index => $item)
        @php
            $pickingItems = $item->items ?? $item->pickingItems ?? collect();
            $isDone = ($item->status_persiapan ?? '') === 'Sudah' && !empty($item->pic);
        @endphp
        <tr class="hover:bg-gray-50 {{ $isDone ? 'processed-row' : '' }}" data-id="{{ $item->id }}">
            <td class="px-4 py-3 text-center">{{ $data->firstItem() + $index }}</td>
            <td class="px-4 py-3">{{ $item->no_pl ?? '-' }}</td>
            <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
            <td class="px-4 py-3 text-center">
                {{ $item->billing_last_name ?? '-' }}
                @if($item->billing_company)
                    <br><small class="text-gray-500">{{ $item->billing_company }}</small>
                @endif
            </td>
            <td class="text-center text-sm px-4 py-3">
                @php
                    $kategoriProduk = $pickingItems
                        ->map(function ($pickingItem) {
                            $sku = $pickingItem->product?->sku ?? $pickingItem->item_sku;
                            $kategori = $pickingItem->product?->kategori;
                            $namaBarang = $pickingItem->item_name;
                            $label = $pickingItem->product?->label ?? '';
                            $kategoriLower = strtolower($kategori ?? '');
                            $namaBarangLower = strtolower($namaBarang ?? '');

                            if (str_contains($kategoriLower, 'sertifikat') || str_contains($namaBarangLower, 'sertifikat')) {
                                return $sku ? $sku . ' - ' . ($kategori ?: 'Sertifikat') : ($kategori ?: 'Sertifikat');
                            }
                            if (str_contains($kategoriLower, 'majalah') || str_contains($namaBarangLower, 'majalah')) {
                                return $label ? $label . ' - ' . ($kategori ?: 'Majalah') : ($kategori ?: 'Majalah');
                            }
                            return $kategori ?? $namaBarang ?? '-';
                        })
                        ->filter()
                        ->unique()
                        ->implode(' | ');
                @endphp
                {{ $kategoriProduk ?: ($item->kategori_order ?? $item->pesanan ?? '-') }}
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
                @if($item->tgl_terima ?? null)
                    <span class="text-emerald-600 font-medium">{{ \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') }}</span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>

            {{-- Status Persiapan --}}
            <td class="extra-col px-4 py-3 text-center {{ ($item->tgl_terima ?? null) ? '' : 'hidden' }}">
                <select class="status-select border rounded-lg px-2 py-1 text-sm
                    @if($isDone) bg-green-100 text-green-700
                    @else bg-red-100 text-red-700
                    @endif"
                    data-id="{{ $item->id }}"
                    {{ $isDone ? 'disabled' : '' }}>
                    <option value="Belum" {{ ($item->status_persiapan ?? 'Belum') == 'Belum' ? 'selected' : '' }}>Belum</option>
                    <option value="Sudah" {{ ($item->status_persiapan ?? '') == 'Sudah' ? 'selected' : '' }}>Sudah</option>
                </select>
            </td>

            {{-- Tanggal Picking --}}
            <td class="extra-col tanggal-picking px-4 py-3 text-center whitespace-nowrap {{ ($item->tgl_terima ?? null) ? '' : 'hidden' }}">
                @if($item->tgl_picking)
                    {{ \Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y') }}
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>

            {{-- PIC --}}
            <td class="extra-col px-4 py-3 text-center {{ ($item->tgl_terima ?? null) ? '' : 'hidden' }}">
                <select class="pic-select border rounded-lg px-2 py-1 text-sm"
                        data-id="{{ $item->id }}"
                        {{ $isDone ? 'disabled' : '' }}>
                    <option value="">-- Pilih --</option>
                    <option value="Asep" {{ ($item->pic ?? '') == 'Asep' ? 'selected' : '' }}>Asep</option>
                    <option value="Arif" {{ ($item->pic ?? '') == 'Arif' ? 'selected' : '' }}>Arif</option>
                    <option value="Rama" {{ ($item->pic ?? '') == 'Rama' ? 'selected' : '' }}>Rama</option>
                    <option value="Riky" {{ ($item->pic ?? '') == 'Riky' ? 'selected' : '' }}>Riky</option>
                </select>
            </td>

            {{-- Checkbox Cek --}}
            <td class="text-center px-4 py-3">
                <div class="flex items-center justify-center gap-4 text-sm">
                    <label class="flex items-center gap-1 {{ $isDone ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}" title="Checklist">
                        <input type="checkbox"
                               class="w-4 h-4 accent-emerald-600 checklist-item"
                               data-id="{{ $item->id }}"
                               {{ ($item->tgl_terima ?? null) ? 'checked' : '' }}
                               {{ $isDone ? 'disabled' : '' }}>
                        <span class="text-emerald-600 text-xs font-medium">Cek</span>
                    </label>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="13" class="text-center py-16 text-gray-500">
                Belum ada Picking List Jakarta Pasif.
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
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    }

    // Fungsi untuk mengunci baris
    function lockRow(row) {
        row.classList.add('processed-row');

        const checkbox = row.querySelector('.checklist-item');
        if (checkbox) {
            checkbox.disabled = true;
            checkbox.closest('label')?.classList.add('cursor-not-allowed', 'opacity-60');
            checkbox.closest('label')?.classList.remove('cursor-pointer');
        }

        const statusSelect = row.querySelector('.status-select');
        if (statusSelect) {
            statusSelect.disabled = true;
            statusSelect.classList.remove('bg-red-100', 'text-red-700');
            statusSelect.classList.add('bg-green-100', 'text-green-700');
        }

        const picSelect = row.querySelector('.pic-select');
        if (picSelect) {
            picSelect.disabled = true;
        }
    }

    // Cek apakah sudah siap dikunci (Status Sudah + PIC terisi)
    function shouldLock(row) {
        const statusSelect = row.querySelector('.status-select');
        const picSelect = row.querySelector('.pic-select');
        return statusSelect?.value === 'Sudah' && picSelect?.value !== '';
    }

    // ==================== CHECKLIST ====================
    document.querySelectorAll('.checklist-item').forEach(chk => {
        if (chk.disabled) return;

        chk.addEventListener('change', function () {
            const id = this.dataset.id;
            const checked = this.checked;
            const row = this.closest('tr');

            row.querySelector('.waktu-terima').innerHTML = checked
                ? `<span class="text-emerald-600 font-medium">${formatTanggalHariIni()}</span>`
                : `<span class="text-gray-400">-</span>`;

            row.querySelectorAll('.extra-col').forEach(col => {
                col.classList.toggle('hidden', !checked);
            });

            const hasChecked = document.querySelectorAll('.checklist-item:checked').length > 0;
            document.querySelectorAll('th.extra-col').forEach(th => {
                th.classList.toggle('hidden', !hasChecked);
            });

            fetch("{{ route('picking.pasif.checklist') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id, checked: checked })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update checklist: ' + (data.message || ''));
                    this.checked = !checked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
                this.checked = !checked;
            });
        });
    });

    // ==================== STATUS PERSIAPAN ====================
    document.querySelectorAll('.status-select').forEach(select => {
        if (select.disabled) return;

        select.addEventListener('change', function () {
            const id = this.dataset.id;
            const status = this.value;
            const row = this.closest('tr');

            this.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            if (status === 'Sudah') {
                this.classList.add('bg-green-100', 'text-green-700');
            } else {
                this.classList.add('bg-red-100', 'text-red-700');
            }

            fetch("{{ route('picking.pasif.status') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: id,
                    status_persiapan: status
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update status: ' + (data.message || ''));
                    return;
                }

                // Hanya kunci jika Status = Sudah DAN PIC sudah terisi
                if (shouldLock(row)) {
                    lockRow(row);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
            });
        });
    });

    // ==================== PIC ====================
    document.querySelectorAll('.pic-select').forEach(select => {
        if (select.disabled) return;

        select.addEventListener('change', function () {
            const id = this.dataset.id;
            const pic = this.value;
            const row = this.closest('tr');

            fetch("{{ route('picking.pasif.pic') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id, pic: pic })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update PIC');
                    return;
                }

                // Setelah PIC diisi, cek apakah Status sudah Sudah → kunci
                if (shouldLock(row)) {
                    lockRow(row);
                }
            })
            .catch(err => console.error(err));
        });
    });

    // ==================== SAAT PAGE LOAD ====================
    document.querySelectorAll('tr[data-id]').forEach(row => {
        if (shouldLock(row)) {
            lockRow(row);
            row.querySelectorAll('.extra-col').forEach(col => col.classList.remove('hidden'));
        }
    });

    // Tampilkan header extra-col jika ada yang sudah dicek
    const hasAnyChecked = document.querySelectorAll('.checklist-item:checked').length > 0;
    if (hasAnyChecked) {
        document.querySelectorAll('th.extra-col').forEach(th => th.classList.remove('hidden'));
    }
});
</script>
</body>
</html>