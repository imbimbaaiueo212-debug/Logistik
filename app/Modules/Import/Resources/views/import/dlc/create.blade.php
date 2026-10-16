@extends('layouts.panel')

@section('title', 'Tambah Data DLC')

@section('content')
@php
    $input = 'w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
    $units = ['Anggrek 1', 'Anggrek 2', 'Anggrek 3', 'Anggrek 5', 'Dhuafa'];
@endphp

<div class="max-w-3xl">
    <h2 class="text-2xl font-bold text-[#162749] mb-5">Tambah Data Pemesanan DLC</h2>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('import.dlc.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="edisi" class="block text-sm font-medium text-gray-700 mb-1">Edisi <span class="text-red-500">*</span></label>
                <select name="edisi" id="edisi" required class="{{ $input }}">
                    <option value="">-- Pilih Edisi --</option>
                    @for($i = 150; $i <= 180; $i++)
                        <option value="M{{ $i }}" {{ old('edisi') === "M$i" ? 'selected' : '' }}>M{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="periode" class="block text-sm font-medium text-gray-700 mb-1">Periode <span class="text-red-500">*</span></label>
                <input type="text" name="periode" id="periode" value="{{ old('periode') }}" required
                       placeholder="Contoh: 23-31 2026" class="{{ $input }}">
            </div>

            <div>
                <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" id="judul" value="{{ old('judul') }}" readonly
                       placeholder="Terisi otomatis" class="{{ $input }} bg-gray-50">
            </div>

            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <input type="text" name="bulan" id="bulan" value="{{ old('bulan') }}" readonly
                       placeholder="Terisi otomatis" class="{{ $input }} bg-gray-50">
            </div>
        </div>

        <h3 class="text-base font-semibold text-[#162749] mb-3">Daftar Unit & Qty</h3>
        <div class="space-y-2.5 mb-6">
            @foreach($units as $i => $unit)
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-800">
                        {{ $unit }}
                    </div>
                    <input type="hidden" name="items[{{ $i }}][nama_unit]" value="{{ $unit }}">
                    <input type="number" name="items[{{ $i }}][qty]" min="0" value="{{ old("items.$i.qty") }}"
                           placeholder="Qty" aria-label="Qty {{ $unit }}"
                           class="w-28 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]">
                </div>
            @endforeach
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-100">
            <button type="submit"
                    class="bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                Simpan Data DLC
            </button>
            <a href="{{ route('import.dlc.index') }}"
               class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var edisiEl = document.getElementById('edisi');
        var judulEl = document.getElementById('judul');
        var bulanEl = document.getElementById('bulan');

        function isiOtomatis() {
            var edisi = edisiEl.value;
            if (!edisi) { judulEl.value = ''; bulanEl.value = ''; return; }

            // M159 = Juli (indeks 6); tiap edisi maju satu bulan
            var nomor = parseInt(edisi.replace('M', ''), 10);
            var bulan = namaBulan[(((nomor - 159 + 6) % 12) + 12) % 12];

            bulanEl.value = bulan;
            judulEl.value = 'Majalah ' + edisi + ' (' + bulan + ')';
        }

        edisiEl.addEventListener('change', isiOtomatis);
        if (edisiEl.value) isiOtomatis();
    })();
</script>
@endpush