<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Jakarta Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { padding: 12px 8px; font-size: 0.875rem; }
        
        /* Efek muted untuk baris yang selesai */
        tr.locked {
            opacity: 0.75;
            background-color: #f1f5f9 !important;
        }
        tr.locked td {
            color: #64748b;
        }
        input:disabled, select:disabled {
            background-color: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Packing Jakarta Aktif</h1>
        <a href="{{ route('packing.index') }}" class="bg-gray-600 text-white px-5 py-3 rounded-2xl hover:bg-gray-700">← Kembali</a>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3">No</th>
                    <th class="text-left px-4 py-3">No PL</th>
                    <th class="text-left px-4 py-3">Nama Unit</th>
                    <th class="text-left px-4 py-3">Nama Barang</th>
                    <th class="text-left px-4 py-3">Tgl Estimasi</th>
                    <th class="text-left px-4 py-3">Tgl Packing</th>
                    <th class="text-left px-4 py-3">Status Packing</th>
                    <th class="text-left px-4 py-3">Nama Packer</th>
                    <th class="text-right px-4 py-3">Berat biMBA Shop</th>
                    <th class="text-right px-4 py-3">Berat Aktual (KG)</th>
                    <th class="text-right px-4 py-3">Koli (Q)</th>
                    <th class="text-left px-4 py-3">Keterangan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($data as $index => $item)

                @php
                    $isLocked = $item->status_packing === 'selesai';
                @endphp

                <form method="POST" action="{{ route('packing.update', $item->id) }}">
                    @csrf
                    @method('PUT')

                    <tr class="transition duration-200 hover:shadow-md
                        @if($isLocked) locked @else
                            @switch($item->status_packing)
                                @case('belum') bg-red-50 @break
                                @case('proses') bg-yellow-50 @break
                                @case('pending') bg-orange-50 @break
                                @case('selesai') bg-green-50 @break
                            @endswitch
                        @endif">

                        {{-- NO --}}
                        <td class="px-3 py-4 text-center font-semibold whitespace-nowrap">
                            {{ $data->firstItem() + $index }}
                        </td>

                        {{-- NO PL --}}
                        <td class="px-3 py-4 font-semibold whitespace-nowrap">
                            {{ $item->no_pl }}
                        </td>

                        {{-- NAMA UNIT --}}
                        <td class="px-3 py-4 leading-5 font-medium min-w-[180px]">
                            {{ $item->nama_unit }}
                        </td>

                        {{-- NAMA BARANG --}}
                        <td class="px-3 py-4 whitespace-nowrap">
                            {{ $item->nama_barang }}
                        </td>

                        {{-- TGL ESTIMASI --}}
                        <td class="px-3 py-4 whitespace-nowrap">
                            {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}
                        </td>

                        {{-- TGL PACKING --}}
                        <td class="px-3 py-4">
                            <input type="date" 
                                   name="tgl_packing"
                                   value="{{ $item->tgl_packing ? \Carbon\Carbon::parse($item->tgl_packing)->format('Y-m-d') : '' }}"
                                   class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>

                        {{-- STATUS PACKING --}}
                        <td class="px-3 py-4">
                            <select name="status_packing" 
                                    onchange="changeStatusColor(this)"
                                    class="status-select w-44 rounded-lg border font-semibold px-3 py-2 text-sm"
                                    {{ $isLocked ? 'disabled' : '' }}>
                                <option value="belum" {{ $item->status_packing == 'belum' ? 'selected' : '' }}>🔴 Belum Dipacking</option>
                                <option value="proses" {{ $item->status_packing == 'proses' ? 'selected' : '' }}>🟡 Sedang Dipacking</option>
                                <option value="pending" {{ $item->status_packing == 'pending' ? 'selected' : '' }}>🟠 Packing Belum Selesai</option>
                                <option value="selesai" {{ $item->status_packing == 'selesai' ? 'selected' : '' }}>🟢 Selesai Dipacking</option>
                            </select>
                        </td>

                        {{-- NAMA PACKER --}}
                        <td class="px-3 py-4">
                            <select name="nama_packer" 
                                    class="w-48 border rounded-lg px-3 py-2 text-sm"
                                    {{ $isLocked ? 'disabled' : '' }}>
                                <option value="">Pilih</option>
                                <option value="Jodi Setiawan" {{ $item->nama_packer=='Jodi Setiawan'?'selected':'' }}>Jodi Setiawan</option>
                                <option value="Achmad Saefudin" {{ $item->nama_packer=='Achmad Saefudin'?'selected':'' }}>Achmad Saefudin</option>
                                <option value="Amar Romdhoni" {{ $item->nama_packer=='Amar Romdhoni'?'selected':'' }}>Amar Romdhoni</option>
                                <option value="Leo Nur Fajri Dwi Putra" {{ $item->nama_packer=='Leo Nur Fajri Dwi Putra'?'selected':'' }}>Leo Nur Fajri Dwi Putra</option>
                                <option value="Abdullah Syapi'i" {{ $item->nama_packer=="Abdullah Syapi'i"?'selected':'' }}>Abdullah Syapi'i</option>
                                <option value="Ocan Cornelia" {{ $item->nama_packer=='Ocan Cornelia'?'selected':'' }}>Ocan Cornelia</option>
                                <option value="Agus Supriono" {{ $item->nama_packer=='Agus Supriono'?'selected':'' }}>Agus Supriono</option>
                                <option value="Ridwan Al Fajar" {{ $item->nama_packer=='Ridwan Al Fajar'?'selected':'' }}>Ridwan Al Fajar</option>
                                <option value="Muhammad Farhan" {{ $item->nama_packer=='Muhammad Farhan'?'selected':'' }}>Muhammad Farhan</option>
                                <option value="Galih" {{ $item->nama_packer=='Galih'?'selected':'' }}>Galih</option>
                                <option value="Manfalutfi" {{ $item->nama_packer=='Manfalutfi'?'selected':'' }}>Manfalutfi</option>
                                <option value="Mohamad Hafid" {{ $item->nama_packer=='Mohamad Hafid'?'selected':'' }}>Mohamad Hafid</option>
                            </select>
                        </td>

                        {{-- BERAT BIMBA SHOP --}}
                        <td class="px-3 py-4 text-center whitespace-nowrap">
                            <span class="inline-block bg-gray-100 rounded-lg px-3 py-2 font-semibold">
                                {{ $item->berat ? number_format($item->berat,0,',','.') : '-' }} g
                            </span>
                        </td>

                        {{-- BERAT AKTUAL --}}
                        <td class="px-3 py-4">
                            <div class="flex">
                                <input type="number" 
                                       name="berat_aktual"
                                       value="{{ $item->berat_aktual ? (int)$item->berat_aktual : '' }}"
                                       min="0" step="1"
                                       class="w-20 border border-r-0 rounded-l-lg px-3 py-2 text-center"
                                       {{ $isLocked ? 'disabled' : '' }}>
                                <span class="bg-gray-100 border rounded-r-lg px-3 py-2 text-gray-600 font-medium">KG</span>
                            </div>
                        </td>

                        {{-- KOLI --}}
                        <td class="px-3 py-4">
                            <input type="number" 
                                   name="koli"
                                   value="{{ $item->koli }}"
                                   min="1"
                                   class="w-16 border rounded-lg px-2 py-2 text-center font-semibold"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>

                        {{-- KETERANGAN --}}
                        <td class="px-3 py-4">
                            <input type="text" 
                                   name="keterangan_packing"
                                   value="{{ $item->keterangan_packing }}"
                                   placeholder="Tambahkan catatan..."
                                   class="w-64 border rounded-lg px-3 py-2"
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </td>

                        {{-- AKSI --}}
                        <td class="px-3 py-4 text-center">
                            @if($isLocked)
                                <button type="button" 
                                        class="bg-green-600 text-white rounded-lg px-6 py-2 font-semibold cursor-not-allowed flex items-center gap-2 mx-auto"
                                        disabled>
                                    ✅ Selesai
                                </button>
                            @else
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-2 font-semibold">
                                    💾 Simpan
                                </button>
                            @endif
                        </td>
                    </tr>
                </form>

            @empty
                <tr>
                    <td colspan="13" class="text-center py-12 text-gray-400">
                        Belum ada data packing.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

</body>
</html>