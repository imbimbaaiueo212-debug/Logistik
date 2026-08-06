<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution Order Manual - biMBA Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { 
            padding: 12px 8px; 
            font-size: 0.875rem; 
        }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <i class="bi bi-truck text-indigo-600"></i>
                Distribution Order Manual
            </h1>
            <p class="text-gray-500 mt-1">Distribusi dari Packing Manual (Majalah / Modul / Sertifikat)</p>
        </div>

        <a href="{{ route('distribution-order.index') }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-2xl font-semibold flex items-center gap-2 transition">
            ← Kembali
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status_distribusi" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="Pending" {{ request('status_distribusi') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Proses" {{ request('status_distribusi') == 'Proses' ? 'selected' : '' }}>Proses</option>
                    <option value="Selesai" {{ request('status_distribusi') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="kategori" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="Majalah" {{ request('kategori') == 'Majalah' ? 'selected' : '' }}>Majalah</option>
                    <option value="Modul" {{ request('kategori') == 'Modul' ? 'selected' : '' }}>Modul</option>
                    <option value="Sertifikat" {{ request('kategori') == 'Sertifikat' ? 'selected' : '' }}>Sertifikat</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Grup</label>
                <select name="grup" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="A" {{ request('grup') == 'A' ? 'selected' : '' }}>A</option>
                    <option value="B" {{ request('grup') == 'B' ? 'selected' : '' }}>B</option>
                    <option value="C" {{ request('grup') == 'C' ? 'selected' : '' }}>C</option>
                    <option value="D" {{ request('grup') == 'D' ? 'selected' : '' }}>D</option>
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" 
                       class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm"
                       placeholder="No PL / No PS / Nama Unit"
                       value="{{ request('search') }}">
            </div>

            <div class="md:col-span-2 flex items-end gap-3">
                <button type="submit" 
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-2xl transition">
                    Filter
                </button>
                <a href="{{ route('distribution-order.manual') }}" 
                   class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 rounded-2xl text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3">No</th>
                    <th class="text-left px-4 py-3">No PL</th>
                    <th class="text-left px-4 py-3">ID biMBA Shop</th>
                    <th class="text-left px-4 py-3">Nama Unit</th>
                    <th class="text-center px-4 py-3">Grup</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Ekspedisi</th>
                    <th class="text-left px-4 py-3">Service</th>
                    <th class="text-center px-4 py-3">Berat</th>
                    <th class="text-center px-4 py-3">Berat Aktual</th>
                    <th class="text-center px-4 py-3">Koli</th>
                    <th class="text-left px-4 py-3">Tgl Kirim</th>
                    <th class="text-left px-4 py-3">No Resi / AWB</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Keterangan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                    <tr class="transition duration-200 hover:bg-gray-50" data-id="{{ $item->id }}">
                        <td class="px-4 py-4 text-center font-semibold">{{ $loop->iteration }}</td>
                        
                        <td class="px-4 py-4 font-semibold text-indigo-700">
                            {{ $item->no_pl ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4">
                            {{ $item->no_ps ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4">
                            {{ $item->nama_unit ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4 text-center">
                            @if($item->grup)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    {{ $item->grup }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        
                        <td class="px-4 py-4">
                            {{ $item->kategori_order ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4">
                            {{ $item->ekspedisi ?? '-' }}
                        </td>
                        
                        <td class="px-4 py-4">
                            {{ $item->service_pengiriman ?? '-' }}
                        </td>
                        
                       <td class="px-4 py-4 text-center">
    @php
        $beratDariOrder = $item->manualPicking?->manualOrder?->order_weight
            ?? $item->berat
            ?? null;
    @endphp

    {{ $beratDariOrder !== null ? number_format($beratDariOrder, 0, ',', '.') : '-' }} gr
</td>
                        
                        <td class="px-4 py-4 text-center">
                            {{ $item->berat_aktual !== null ? number_format($item->berat_aktual, 2, ',', '.') : '-' }} Kg
                        </td>
                        
                        <td class="px-4 py-4 text-center font-medium">
                            {{ $item->koli ?? '-' }}
                        </td>
                        
                        <td class="px-3 py-4">
                            <input type="date"
                                   class="tgl-kirim w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                   data-id="{{ $item->id }}"
                                   value="{{ $item->tgl_kirim ? $item->tgl_kirim->format('Y-m-d') : '' }}">
                        </td>
                        
                        <td class="px-3 py-4">
                            <input type="text"
                                   class="no-resi w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                   data-id="{{ $item->id }}"
                                   value="{{ $item->no_resi ?? '' }}"
                                   placeholder="No. Resi">
                        </td>
                        
                        <td class="px-3 py-4">
                            <select class="status-distribusi w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                    data-id="{{ $item->id }}">
                                <option value="Pending" {{ $item->status_distribusi == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Proses" {{ $item->status_distribusi == 'Proses' ? 'selected' : '' }}>Proses</option>
                                <option value="Selesai" {{ $item->status_distribusi == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </td>
                        
                        <td class="px-3 py-4">
                            <input type="text"
                                   class="keterangan w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                   data-id="{{ $item->id }}"
                                   value="{{ $item->keterangan ?? '' }}"
                                   placeholder="Catatan...">
                        </td>
                        
                        <td class="px-4 py-4 text-center">
                            <button type="button"
                                    class="btn-save text-emerald-600 hover:text-emerald-700"
                                    data-id="{{ $item->id }}"
                                    title="Simpan">
                                <i class="bi bi-check-circle text-xl"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center py-16 text-gray-400">
                            Belum ada data Distribution Order Manual.<br>
                            Data akan muncul setelah Packing Manual selesai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(isset($data) && $data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())
            <div class="px-6 py-4 border-t flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                </div>
                <div>
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function () {
    $('.btn-save').on('click', function () {
        const id = $(this).data('id');
        const row = $(this).closest('tr');

        const payload = {
            _token: '{{ csrf_token() }}',
            tgl_kirim: row.find('.tgl-kirim').val(),
            no_resi: row.find('.no-resi').val(),
            status_distribusi: row.find('.status-distribusi').val(),
            keterangan: row.find('.keterangan').val(),
        };

        $.ajax({
            url: `/distribution-order/manual/${id}/update`,
            method: 'POST',
            data: payload,
            success: function (res) {
                if (res.success) {
                    alert('Data berhasil disimpan');
                } else {
                    alert(res.message || 'Gagal menyimpan');
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Terjadi kesalahan');
            }
        });
    });
});
</script>
</body>
</html>