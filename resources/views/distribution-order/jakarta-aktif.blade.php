<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution Order Jakarta Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { 
            padding: 12px 8px; 
            font-size: 0.875rem; 
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <i class="bi bi-truck text-blue-600"></i>
            Distribution Order Jakarta Aktif
        </h1>
        <a href="{{ route('distribution-order.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold flex items-center gap-2 transition">
            <i class="bi bi-plus-circle"></i> Tambah Order Baru
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status_pengiriman" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="belum_pickup">Belum Pickup</option>
                    <option value="pickup">Pickup</option>
                    <option value="transit">Transit</option>
                    <option value="hold">Hold</option>
                    <option value="retur">Retur</option>
                    <option value="delivered">Delivered</option>
                    <option value="missing">Missing</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kirim</label>
                <select name="jenis_pengiriman" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="diambil_sendiri">Diambil Sendiri</option>
                    <option value="ekspedisi">Ekspedisi</option>
                </select>
            </div>
            <div class="md:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" 
                       class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm"
                       placeholder="No PL atau Nama Barang"
                       value="{{ request('search') }}">
            </div>
            <div class="md:col-span-3 flex items-end gap-3">
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-2xl transition">
                    Filter
                </button>
                <a href="{{ route('distribution-order.jakarta-aktif') }}" 
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
                    <th class="text-left px-4 py-3">Tgl PL</th>
                    <th class="text-left px-4 py-3">Unit</th>
                    <th class="text-left px-4 py-3">Barang</th>
                    <th class="text-left px-4 py-3">Jenis</th>
                    <th class="text-left px-4 py-3">Service</th>
                    <th class="text-left px-4 py-3">Berat</th>
                    <th class="text-left px-4 py-3">Berat Aktual</th>
                    <th class="text-left px-4 py-3">Koli</th>
                    <th class="text-left px-4 py-3">Tgl Pickup/Ambil</th>
                    
                    <th class="text-left px-4 py-3">AWB</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Tgl Diterima</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($distributionOrders as $item)
                    <tr class="transition duration-200 hover:bg-gray-50">
                        <td class="px-4 py-4 text-center font-semibold">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 font-semibold">{{ $item->no_pl }}</td>
                        <td class="px-4 py-4">{{ $item->tgl_turun_pl?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-4">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-4">{{ Str::limit($item->nama_barang ?? '', 50) }}</td>
                        <td class="px-4 py-4">

                            @php
                                $status = optional($item->jakartaAktif)->status_kirim;
                            @endphp

                            @if($status == 'Diambil')

                                <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">
                                    Diambil
                                </span>

                            @elseif($status == 'Dikirim')

                                <span class="inline-block bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-medium">
                                    Dikirim
                                </span>

                            @else

                                <span class="text-gray-400">-</span>

                            @endif

                        </td>
                        <td class="px-4 py-4">
                            {{ optional($item->jakartaAktif)->service_pengiriman ?? '-' }}
                        </td>

                        {{-- Berat dari Packing --}}
                        <td class="px-4 py-4 text-center">
                            {{ $item->packing && $item->packing->berat !== null
                                ? rtrim(rtrim($item->packing->berat, '0'), '.')
                                : '-' }} g
                        </td>

                        <td class="px-4 py-4 text-center">
                            {{ $item->packing && $item->packing->berat_aktual !== null
                                ? rtrim(rtrim($item->packing->berat_aktual, '0'), '.')
                                : '-' }} Kg
                        </td>
                        {{-- Koli --}}
                        <td class="px-3 py-4 text-center font-medium whitespace-nowrap">
                            <span class="inline-block min-w-[60px]">
                                {{ $item->packing->koli ?? '-' }}
                            </span>
                        </td>

                        <td class="px-3 py-4">
                            <input
                                type="date"
                                name="tgl_pickup"
                                value="{{ $item->tgl_pickup ? \Carbon\Carbon::parse($item->tgl_pickup)->format('Y-m-d') : '' }}"
                                class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </td>

                        

                        <td class="px-3 py-4">
                            <input
                                type="text"
                                name="awb"
                                value="{{ $item->awb }}"
                                placeholder="Masukkan No. Resi"
                                class="w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500
                                    focus:border-blue-500 transition duration-200">
                        </td>
                        <td class="px-3 py-4">
                            <select
                                name="status_pengiriman"
                                class="w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500
                                    focus:border-blue-500 transition duration-200">

                                <option value="belum_pickup"
                                    {{ $item->status_pengiriman == 'belum_pickup' ? 'selected' : '' }}>
                                    Belum Pickup
                                </option>

                                <option value="pickup"
                                    {{ $item->status_pengiriman == 'pickup' ? 'selected' : '' }}>
                                    Pickup
                                </option>

                                <option value="transit"
                                    {{ $item->status_pengiriman == 'transit' ? 'selected' : '' }}>
                                    Transit
                                </option>

                                <option value="hold"
                                    {{ $item->status_pengiriman == 'hold' ? 'selected' : '' }}>
                                    Hold
                                </option>

                                <option value="retur"
                                    {{ $item->status_pengiriman == 'retur' ? 'selected' : '' }}>
                                    Retur
                                </option>

                                <option value="delivered"
                                    {{ $item->status_pengiriman == 'delivered' ? 'selected' : '' }}>
                                    Delivered
                                </option>

                            </select>
                        </td>
                        <td class="px-3 py-4">
                            <input
                                type="date"
                                name="tgl_diterima"
                                value="{{ $item->tgl_diterima ? \Carbon\Carbon::parse($item->tgl_diterima)->format('Y-m-d') : '' }}"
                                class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-blue-500
                                    focus:border-blue-500 transition duration-200">
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('distribution-order.show', $item) }}" class="text-blue-600 hover:text-blue-700">
                                    <i class="bi bi-eye text-xl"></i>
                                </a>
                                <a href="{{ route('distribution-order.edit', $item) }}" class="text-amber-600 hover:text-amber-700">
                                    <i class="bi bi-pencil text-xl"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-12 text-gray-400">
                            Belum ada data Distribution Order Jakarta Aktif.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</body>
</html>