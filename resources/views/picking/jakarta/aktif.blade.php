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

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Picking List - Jakarta Aktif</h1>
                <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Jakarta Aktif</p>
            </div>
            
            <div class="flex gap-3">

                <a href="{{ route('picking.index') }}"
                class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    ← Kembali
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
                    <a href="{{ route('picking.jakarta.aktif') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
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

                            @if($data->contains(fn($item) => $item->tgl_terima))
                                <th class="text-center px-4 py-3">Status Persiapan</th>
                                <th class="text-center px-4 py-3">Tanggal Picking</th>
                                <th class="text-center px-4 py-3">PIC</th>
                            @endif

                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $item->no_pl ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            {{ $item->billing_last_name ?? '-' }} 
                            @if($item->billing_company)
                                <br><small class="text-gray-500">{{ $item->billing_company }}</small>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $item->pesanan ?? '-' }}</td>
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
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($item->tgl_terima)
                                {{ \Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        @if($item->tgl_terima)

                        <td class="px-4 py-3 text-center">
                            <select
                                class="status-select border rounded-lg px-2 py-1 text-sm
                                @if($item->status_persiapan == 'Sudah Disiapkan')
                                    bg-green-100 text-green-700
                                @elseif($item->status_persiapan == 'Belum Dipersiapkan')
                                    bg-red-100 text-red-700
                                @elseif($item->status_persiapan == 'On Proses')
                                    bg-yellow-100 text-yellow-700
                                @elseif($item->status_persiapan == 'Hold')
                                    bg-gray-800 text-white
                                @endif"
                                data-id="{{ $item->id }}">

                                <option value="Belum Dipersiapkan"
                                    {{ $item->status_persiapan == 'Belum Dipersiapkan' ? 'selected' : '' }}>
                                    Belum Dipersiapkan
                                </option>

                                <option value="On Proses"
                                    {{ $item->status_persiapan == 'On Proses' ? 'selected' : '' }}>
                                    On Proses
                                </option>

                                <option value="Hold"
                                    {{ $item->status_persiapan == 'Hold' ? 'selected' : '' }}>
                                    Hold
                                </option>

                                <option value="Sudah Disiapkan"
                                    {{ $item->status_persiapan == 'Sudah Disiapkan' ? 'selected' : '' }}>
                                    Sudah Disiapkan
                                </option>

                            </select>
                        </td>
                        
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($item->tgl_picking)
                                {{ \Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>


                        <!-- PIC -->
                        <td class="px-4 py-3 text-center">
                            <select
                                class="pic-select border rounded-lg px-2 py-1 text-sm"
                                data-id="{{ $item->id }}">

                                <option value="">-- Pilih --</option>
                                <option value="Asep" {{ $item->pic == 'Asep' ? 'selected' : '' }}>Asep</option>
                                <option value="Arif" {{ $item->pic == 'Arif' ? 'selected' : '' }}>Arif</option>
                                <option value="Rama" {{ $item->pic == 'Rama' ? 'selected' : '' }}>Rama</option>
                                <option value="Riky" {{ $item->pic == 'Riky' ? 'selected' : '' }}>Riky</option>

                            </select>
                        </td>
                        <!-- END PIC -->
                        @endif
                        
                        <td class="text-center px-4 py-3">
                            <div class="flex items-center justify-center gap-4 text-sm">
                                
                                <!-- Checklist -->
                                <label class="flex items-center gap-1 cursor-pointer" title="Checklist">
                                    <input type="checkbox" 
                                        class="w-4 h-4 accent-emerald-600 checklist-item"
                                        data-id="{{ $item->id }}"
                                        {{ $item->status_persiapan == 'Sudah Dipersiapkan' ? 'checked' : '' }}>
                                    <span class="text-emerald-600 text-xs font-medium">Cek</span>
                                </label>

                                <!-- Edit -->
                                <a href="{{ route('picking.edit', $item->id) }}" 
                                class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    ✏️
                                </a>

                                <!-- Delete -->
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
                        <td colspan="10" class="text-center py-16 text-gray-500">
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

    document.querySelectorAll('.checklist-item').forEach(checkbox => {

        checkbox.addEventListener('change', async function () {

            const checkboxElement = this;
            const id = checkboxElement.dataset.id;
            const checked = checkboxElement.checked;
            const row = checkboxElement.closest('tr');

            try {

                const response = await fetch('{{ route("picking.checklist.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: id,
                        checked: checked
                    })
                });

                const text = await response.text();

                let result = {};

                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error(text);
                    throw new Error('Response bukan JSON');
                }

                if (!response.ok) {
                    throw new Error(result.message || 'Server Error');
                }

                if (!result.success) {
                    throw new Error(result.message || 'Gagal menyimpan data');
                }

                // ===========================
                // Update Waktu Terima
                // ===========================

                const waktuTerimaCell = row.cells[8];

                if (waktuTerimaCell) {

                    if (checked) {

                        const today = new Date().toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });

                        waktuTerimaCell.innerHTML =
                            `<span class="text-emerald-600 font-medium">${today}</span>`;

                    } else {

                        waktuTerimaCell.innerHTML =
                            `<span class="text-gray-400">-</span>`;

                    }
                }

            } catch (error) {

                console.error(error);

                // Kembalikan checkbox seperti semula
                checkboxElement.checked = !checked;

                alert(error.message);

            }

        });

    });

});

document.querySelectorAll('.pic-select').forEach(select => {

    select.addEventListener('change', async function () {

        try {

            const response = await fetch('{{ route("picking.pic.update") }}', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify({
                    id: this.dataset.id,
                    pic: this.value
                })

            });

            const result = await response.json();

            if (!result.success) {
                throw new Error('Gagal menyimpan PIC');
            }

        } catch (error) {

            alert(error.message);

        }

    });

});

document.querySelectorAll('.status-select').forEach(select => {

    select.addEventListener('change', async function () {

        try {

            const response = await fetch('{{ route("picking.status.update") }}', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                body: JSON.stringify({
                    id: this.dataset.id,
                    status_persiapan: this.value
                })

            });

            const result = await response.json();

            if (!result.success) {
                throw new Error('Gagal menyimpan status');
            }

            // Ganti warna dropdown sesuai status
            this.classList.remove(
                'bg-green-100','text-green-700',
                'bg-red-100','text-red-700',
                'bg-yellow-100','text-yellow-700',
                'bg-gray-800','text-white'
            );

            switch(this.value){

                case 'Sudah Disiapkan':
                    this.classList.add('bg-green-100','text-green-700');
                    break;

                case 'Belum Dipersiapkan':
                    this.classList.add('bg-red-100','text-red-700');
                    break;

                case 'On Proses':
                    this.classList.add('bg-yellow-100','text-yellow-700');
                    break;

                case 'Hold':
                    this.classList.add('bg-gray-800','text-white');
                    break;
            }

        } catch (e) {
            alert(e.message);
        }

    });

});
</script>
</body>
</html>