<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QC Outgoing - Jakarta Aktif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        tr:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">QC Outgoing - Jakarta Aktif</h1>
            <a href="{{ route('qc-outgoing.index') }}" class="bg-gray-600 text-white px-5 py-3 rounded-2xl hover:bg-gray-700">← Kembali</a>
        </div>

        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">No PL</th>
                        <th class="text-left px-4 py-3">Tgl Turun PL</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-left px-4 py-3">Pengiriman</th>
                        <th class="text-left px-4 py-3">Nama Barang</th>
                        <th class="text-left px-4 py-3">Tgl Bayar</th>
                        <th class="text-right px-4 py-3">Jumlah Bayar</th>
                        <th class="text-left px-4 py-3">Tgl Estimasi</th>
                        <th class="text-left px-4 py-3">Status QC</th>
                        <th class="text-left px-4 py-3">PIC QC</th>
                        <th class="text-left px-4 py-3">Kode QC</th>
                        <th class="text-left px-4 py-3">Keterangan</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->no_pl }}</td>
                        <td class="px-4 py-3">{{ $item->tgl_turun_pl }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit }}</td>
                        <td class="px-4 py-3">{{ $item->pengiriman }}</td>
                        <td class="px-4 py-3 text-xs">{{ $item->nama_barang }}</td>
                        <td class="px-4 py-3">{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>

                        <form method="POST" action="{{ route('qc-outgoing.store') }}">
    @csrf

    <input type="hidden"
           name="picking_id"
           value="{{ $item->picking_id ?? ($item->picking->id ?? $item->id) }}">

    {{-- STATUS QC --}}
    <td class="px-4 py-3">
    @if($item->status_qc == 'Lolos')

        <span class="inline-block px-3 py-1 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
            {{ $item->status_qc }}
        </span>

        <input type="hidden" name="status_qc" value="{{ $item->status_qc }}">

    @else

        <select name="status_qc"
                class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full">

            <option value="Pending" {{ $item->status_qc=='Pending'?'selected':'' }}>Pending</option>
            <option value="Lolos" {{ $item->status_qc=='Lolos'?'selected':'' }}>Lolos</option>
            <option value="Reject" {{ $item->status_qc=='Reject'?'selected':'' }}>Reject</option>
            <option value="Revisi" {{ $item->status_qc=='Revisi'?'selected':'' }}>Revisi</option>

        </select>

    @endif
</td>

    {{-- PIC QC --}}
    <td class="px-4 py-3">

@if($item->pic_qc)

    <span>{{ preg_replace('/^\d+\s*-\s*/', '', $item->pic_qc) }}</span>

    <input
        type="hidden"
        name="pic_qc"
        value="{{ $item->pic_qc }}">

@else

    <select
        name="pic_qc"
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

                @if($item->kode_qc)
                    <span>{{ $item->kode_qc }}</span>
                @else
                    <span>-</span>
                @endif

    </td>

    {{-- KETERANGAN --}}
    <td class="px-4 py-3">

@if($item->status_qc == 'Lolos')

    {{ $item->keterangan }}

    <input
        type="hidden"
        name="keterangan"
        value="{{ $item->keterangan }}">

@else

    <input
        type="text"
        name="keterangan"
        value="{{ $item->keterangan }}"
        class="border border-gray-300 rounded-lg px-3 py-1 text-sm w-full"
        placeholder="Keterangan QC">

@endif

</td>

    {{-- AKSI --}}
    <td class="px-4 py-3 text-center">

@if($item->status_qc == 'Lolos')

    <span class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">
        ✓ Selesai
    </span>

@else

    <button
        type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">

        {{ $item->pic_qc ? 'Update QC' : 'Simpan QC' }}

    </button>

@endif

</td>

</form>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-center py-10 text-gray-500">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>