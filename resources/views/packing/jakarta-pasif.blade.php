<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Jakarta Pasif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        th, td { padding: 12px 8px; font-size: 0.875rem; }
        
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
        <h1 class="text-3xl font-bold text-gray-800">Packing Jakarta Pasif</h1>
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
        $isLocked = in_array(strtolower($item->status_packing ?? ''), ['selesai']);
        $formId = 'packing-form-' . $item->id;
    @endphp

    <tr class="transition duration-200 hover:shadow-md
        @if($isLocked) locked @else
            @switch(strtolower($item->status_packing ?? 'pending'))
                @case('pending') bg-red-50 @break
                @case('proses') bg-yellow-50 @break
                @case('selesai') bg-green-50 @break
                @default bg-white
            @endswitch
        @endif">

        {{-- Form tersembunyi di sel pertama --}}
        <td class="px-3 py-4 text-center font-semibold whitespace-nowrap">
            <form id="{{ $formId }}" method="POST" action="{{ route('packing.pasif.update', $item->id) }}" class="packing-form">
                @csrf
                @method('PUT')
            </form>
            {{ $data->firstItem() + $index }}
        </td>

        <td class="px-3 py-4 font-semibold whitespace-nowrap">{{ $item->no_pl }}</td>
        <td class="px-3 py-4 leading-5 font-medium min-w-[180px]">{{ $item->nama_unit }}</td>
        <td class="px-3 py-4 whitespace-nowrap">{{ $item->nama_barang }}</td>
        <td class="px-3 py-4 whitespace-nowrap">
            {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}
        </td>

        <!-- TGL PACKING -->
        <td class="px-3 py-4">
            <input type="date"
                   form="{{ $formId }}"
                   name="tgl_packing"
                   value="{{ $item->tgl_packing ? \Carbon\Carbon::parse($item->tgl_packing)->format('Y-m-d') : '' }}"
                   class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                   {{ $isLocked ? 'disabled' : '' }} required>
        </td>

        <!-- STATUS -->
        <td class="px-3 py-4">
            <select form="{{ $formId }}"
                    name="status_packing"
                    class="status-select w-44 rounded-lg border font-semibold px-3 py-2 text-sm"
                    {{ $isLocked ? 'disabled' : '' }} required>
                <option value="Pending" {{ ($item->status_packing ?? '') == 'Pending' ? 'selected' : '' }}>🔴 Belum Dipacking</option>
                <option value="Proses" {{ ($item->status_packing ?? '') == 'Proses' ? 'selected' : '' }}>🟡 Sedang Dipacking</option>
                <option value="Selesai" {{ ($item->status_packing ?? '') == 'Selesai' ? 'selected' : '' }}>🟢 Selesai Dipacking</option>
            </select>
        </td>

        <!-- NAMA PACKER -->
        <td class="px-3 py-4">
            <select form="{{ $formId }}"
                    name="nama_packer"
                    class="w-48 border rounded-lg px-3 py-2 text-sm"
                    {{ $isLocked ? 'disabled' : '' }} required>
                <option value="">Pilih Packer</option>
                <option value="Jodi Setiawan" {{ ($item->nama_packer ?? $item->pic_packing) == 'Jodi Setiawan' ? 'selected' : '' }}>Jodi Setiawan</option>
                <option value="Achmad Saefudin" {{ ($item->nama_packer ?? $item->pic_packing) == 'Achmad Saefudin' ? 'selected' : '' }}>Achmad Saefudin</option>
                <option value="Amar Romdhoni" {{ ($item->nama_packer ?? $item->pic_packing) == 'Amar Romdhoni' ? 'selected' : '' }}>Amar Romdhoni</option>
                <option value="Leo Nur Fajri Dwi Putra" {{ ($item->nama_packer ?? $item->pic_packing) == 'Leo Nur Fajri Dwi Putra' ? 'selected' : '' }}>Leo Nur Fajri Dwi Putra</option>
                <option value="Abdullah Syapi'i" {{ ($item->nama_packer ?? $item->pic_packing) == "Abdullah Syapi'i" ? 'selected' : '' }}>Abdullah Syapi'i</option>
                <option value="Ocan Cornelia" {{ ($item->nama_packer ?? $item->pic_packing) == 'Ocan Cornelia' ? 'selected' : '' }}>Ocan Cornelia</option>
                <option value="Agus Supriono" {{ ($item->nama_packer ?? $item->pic_packing) == 'Agus Supriono' ? 'selected' : '' }}>Agus Supriono</option>
                <option value="Ridwan Al Fajar" {{ ($item->nama_packer ?? $item->pic_packing) == 'Ridwan Al Fajar' ? 'selected' : '' }}>Ridwan Al Fajar</option>
                <option value="Muhammad Farhan" {{ ($item->nama_packer ?? $item->pic_packing) == 'Muhammad Farhan' ? 'selected' : '' }}>Muhammad Farhan</option>
                <option value="Galih" {{ ($item->nama_packer ?? $item->pic_packing) == 'Galih' ? 'selected' : '' }}>Galih</option>
                <option value="Manfalutfi" {{ ($item->nama_packer ?? $item->pic_packing) == 'Manfalutfi' ? 'selected' : '' }}>Manfalutfi</option>
                <option value="Mohamad Hafid" {{ ($item->nama_packer ?? $item->pic_packing) == 'Mohamad Hafid' ? 'selected' : '' }}>Mohamad Hafid</option>
            </select>
        </td>

        <!-- BERAT BIMBA -->
        <td class="px-3 py-4 text-center whitespace-nowrap">
            <span class="inline-block bg-gray-100 rounded-lg px-3 py-2 font-semibold">
    {{ $item->berat_bimbashop ? number_format($item->berat_bimbashop, 0, ',', '.') : '-' }} g
</span>
        </td>

        <!-- BERAT AKTUAL -->
        <!-- BERAT AKTUAL -->
        <td class="px-3 py-4">
            <div class="flex">
                <input type="number"
                    form="{{ $formId }}"
                    name="berat_aktual"
                    value="{{ $item->berat_aktual ? (float)$item->berat_aktual : '' }}"
                    min="0"
                    step="0.001"
                    class="w-24 border border-r-0 rounded-l-lg px-3 py-2 text-center"
                    {{ $isLocked ? 'disabled' : '' }} required>
                <span class="bg-gray-100 border rounded-r-lg px-3 py-2 text-gray-600 font-medium">KG</span>
            </div>
        </td>

        <!-- KOLI -->
        <td class="px-3 py-4">
            <input type="number"
                   form="{{ $formId }}"
                   name="koli"
                   value="{{ $item->koli ?? '' }}"
                   min="1"
                   class="w-16 border rounded-lg px-2 py-2 text-center font-semibold"
                   {{ $isLocked ? 'disabled' : '' }} required>
        </td>

        <!-- KETERANGAN -->
        <td class="px-3 py-4">
            <input type="text"
                   form="{{ $formId }}"
                   name="keterangan_packing"
                   value="{{ $item->keterangan_packing ?? $item->keterangan }}"
                   placeholder="Tambahkan catatan..."
                   class="w-64 border rounded-lg px-3 py-2"
                   {{ $isLocked ? 'disabled' : '' }}>
        </td>

        <!-- AKSI -->
        <td class="px-3 py-4 text-center">
            @if($isLocked)
                <button type="button" class="bg-green-600 text-white rounded-lg px-6 py-2 font-semibold cursor-not-allowed flex items-center gap-2 mx-auto" disabled>
                    ✅ Selesai
                </button>
            @else
                <button type="submit" form="{{ $formId }}" class="save-btn bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-2 font-semibold">
                    💾 Simpan
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="13" class="text-center py-12 text-gray-400">Belum ada data packing Jakarta Pasif.</td>
    </tr>
@endforelse
            </tbody>
        </table>

        @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                </div>
                <div>{{ $data->links() }}</div>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.packing-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const formId = form.id;

        // Ambil input yang terhubung lewat atribut form="..."
        const tglPacking  = document.querySelector(`input[name="tgl_packing"][form="${formId}"]`)?.value?.trim();
        const status      = document.querySelector(`select[name="status_packing"][form="${formId}"]`)?.value;
        const packer      = document.querySelector(`select[name="nama_packer"][form="${formId}"]`)?.value;
        const beratAktual = document.querySelector(`input[name="berat_aktual"][form="${formId}"]`)?.value?.trim();
        const koli        = document.querySelector(`input[name="koli"][form="${formId}"]`)?.value?.trim();

        let errors = [];

        if (!tglPacking) errors.push('Tanggal Packing harus diisi');
        if (!status) errors.push('Status Packing harus dipilih');
        if (!packer) errors.push('Nama Packer harus dipilih');
        if (!beratAktual || parseFloat(beratAktual) <= 0)
            errors.push('Berat Aktual harus diisi dan lebih dari 0');
        if (!koli || parseInt(koli) < 1)
            errors.push('Koli harus diisi minimal 1');

        if (errors.length > 0) {
            e.preventDefault();
            alert('❌ Data belum lengkap:\n\n' + errors.join('\n'));
        }
    });
});
</script>

</body>
</html>