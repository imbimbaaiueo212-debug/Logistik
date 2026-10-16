@extends('layouts.panel')

@section('title', 'Detail Pesanan Majalah Kotamadya')

@push('styles')
{{-- Select2 (filter unit punya daftar panjang, jadi tetap pakai kolom cari) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 1200px;
        font-size: 0.8125rem;
    }
    .data-table th,
    .data-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
        border-bottom: 1px solid rgba(15, 27, 51, 0.06);
    }
    .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #F4F6FA;
        color: rgba(15, 27, 51, 0.6);
        font-weight: 600;
        font-size: 0.75rem;
        border-bottom: 1px solid rgba(15, 27, 51, 0.1);
    }
    .data-table tbody tr:hover td { background-color: #F7F9FC; }
    .data-table .ctr { text-align: center; }
    .data-table tr.baris-total td { background: #F4F6FA; font-weight: 700; }

    /* Teks panjang dipotong "...", isi lengkap muncul saat kursor diarahkan (atribut title) */
    .cell-clip { max-width: 240px; overflow: hidden; text-overflow: ellipsis; }

    /* Select2 disesuaikan dengan tema */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid rgba(15, 27, 51, 0.1) !important;
        border-radius: 0.75rem !important;
        padding: 6px 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #0F1B33 !important;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single { border-color: #28447F !important; }
    .select2-dropdown { border: 1px solid rgba(15, 27, 51, 0.1) !important; border-radius: 0.75rem !important; font-size: 0.875rem; }
    .select2-container--default .select2-results__option--highlighted { background-color: #28447F !important; color: #fff !important; }
</style>
@endpush

@section('content')

    @php
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $periodeText = (!empty($data->periode) && preg_match('/^\d{4}-(\d{2})$/', $data->periode, $mm))
            ? ($namaBulan[(int) $mm[1]] ?? $data->periode)
            : '-';

        // Info periode: [label, isi]
        $info = [
            ['Judul', $data->judul ?? '-'],
            ['Bulan / Edisi', $data->bulan ?? '-'],
            ['Tahun', $data->tahun ?? '-'],
            ['Periode', $periodeText],
        ];

        // Filter unit: [name, label, placeholder, daftar opsi]
        $filters = [
            ['nama_unit', 'Nama Unit', 'Semua unit', $listNamaUnit],
            ['no_cabang', 'No Cabang', 'Semua no cabang', $listNoCabang],
            ['kotamadya', 'Kotamadya / Kabupaten', 'Semua kotamadya', $listKotamadya],
        ];

        // Kolom tabel setelah "No": [judul, field, tipe]. Tipe: text, clip, num, kontak
        $kolom = [
            ['No Cabang', 'no_cabang'],
            ['Nama Unit', 'nama_unit', 'clip'],
            ['Jumlah Pesanan', 'jumlah_pesanan', 'num'],
            ['Alamat Unit', 'alamat_unit', 'clip'],
            ['Telepon', 'telepon'],
            ['Kotamadya / Kabupaten', 'nama_kotamadya'],
            ['Contact Person', 'contact_person', 'kontak'],
        ];

        // Daftar nama unit yang tidak match (dari flash session import, atau dari $mismatches)
        $listMismatch = session('unit_nama_mismatch');
        if (empty($listMismatch) && isset($mismatches) && $mismatches->count() > 0) {
            $listMismatch = $mismatches->map(fn ($m) => [
                'no_cab'      => $m->no_cab,
                'nama_excel'  => $m->nama_excel,
                'nama_master' => $m->nama_master,
            ])->toArray();
        }
        $listMismatch = $listMismatch ?? [];

        $lbl = 'block text-sm text-navy-950/60 mb-1.5';
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Detail Pesanan Majalah Kotamadya</h1>
            <p class="mt-1 text-sm text-navy-950/55">{{ $data->judul ?? 'Pesanan Majalah Kotamadya' }} — {{ $data->bulan ?? '-' }} {{ $data->tahun ?? '' }}</p>
        </div>

        <a href="{{ route('pesanan-majalah-kotamadya.index') }}"
           class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </div>

    {{-- ============ NOTIFIKASI ============ --}}
    @if (session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 whitespace-pre-line">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Tombol nama unit tidak match --}}
    @if (count($listMismatch) > 0)
        <div class="mt-5">
            <button type="button" onclick="bukaMismatch()"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 3 19h18L12 4Z"/><path d="M12 10v4M12 17h.01"/></svg>
                Nama unit tidak match
                <span class="bg-white text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ count($listMismatch) }}</span>
            </button>
        </div>
    @endif

    {{-- ============ INFO PERIODE ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <h2 class="text-base font-semibold text-navy-950">Informasi Periode</h2>
        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($info as [$label, $isi])
                <div class="min-w-0">
                    <p class="text-xs text-navy-950/50 mb-1">{{ $label }}</p>
                    <p class="text-sm font-semibold text-navy-950 truncate" title="{{ $isi }}">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ FILTER UNIT ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" action="{{ route('pesanan-majalah-kotamadya.show', $data->id) }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($filters as [$nama, $label, $ph, $opsi])
                    <div>
                        <label for="f_{{ $nama }}" class="{{ $lbl }}">{{ $label }}</label>
                        <select id="f_{{ $nama }}" name="{{ $nama }}" class="select2 w-full">
                            <option value="">{{ $ph }}</option>
                            @foreach ($opsi as $o)
                                <option value="{{ $o }}" {{ request($nama) == $o ? 'selected' : '' }}>{{ $o }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    Filter
                </button>
                <a href="{{ route('pesanan-majalah-kotamadya.show', $data->id) }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-3 py-2.5">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL UNIT ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-navy-950/10 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-navy-950">Daftar Unit</h2>
            <p class="text-sm text-navy-950/55">
                Total unit: <span class="font-semibold text-navy-950">{{ number_format($totalUnits ?? 0, 0, ',', '.') }}</span>
                <span class="mx-2 text-navy-950/25">|</span>
                Total pesanan: <span class="font-semibold text-navy-950">{{ number_format(round($totalPesanan ?? 0), 0, ',', '.') }}</span>
            </p>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="ctr">No</th>
                        @foreach ($kolom as $kol)
                            <th class="{{ ($kol[2] ?? '') === 'num' ? 'ctr' : '' }}">{{ $kol[0] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td class="ctr">{{ $loop->iteration }}</td>

                            @foreach ($kolom as $kol)
                                @php
                                    $tipe = $kol[2] ?? 'text';
                                    $val  = $unit->{$kol[1]};
                                    if ($tipe === 'num') {
                                        $val = number_format(round($val ?? 0), 0, ',', '.');
                                    } elseif ($tipe === 'kontak') {
                                        // nama contact person + nomor teleponnya
                                        $val = trim(($unit->contact_person ?? '') . ' ' . ($unit->telepon_contact_person ?? ''));
                                    }
                                    $tampil = filled($val) ? $val : '-';
                                @endphp
                                <td class="{{ $kol[1] === 'nama_unit' ? 'font-medium' : '' }} {{ $tipe === 'num' ? 'ctr font-semibold' : '' }} {{ in_array($tipe, ['clip', 'kontak']) ? 'cell-clip' : '' }}"
                                    @if (in_array($tipe, ['clip', 'kontak'])) title="{{ $tampil }}" @endif>{{ $tampil }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($kolom) + 1 }}" class="!text-center py-16 text-navy-950/50">
                                <p class="font-semibold text-navy-950/70">Tidak ada data unit</p>
                                <p class="mt-1">Coba ubah filter atau import Excel.</p>
                            </td>
                        </tr>
                    @endforelse

                    @if ($units->count() > 0)
                        <tr class="baris-total">
                            <td colspan="3" class="!text-right">TOTAL</td>
                            <td class="ctr">{{ number_format(round($totalPesanan ?? 0), 0, ',', '.') }}</td>
                            <td colspan="{{ count($kolom) - 3 }}"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ MODAL NAMA UNIT TIDAK MATCH ============ --}}
    @if (count($listMismatch) > 0)
        <div id="mismatchModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
            <div class="absolute inset-0 bg-navy-950/50" onclick="tutupMismatch()"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-200 bg-amber-50 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-amber-900 text-base">Nama unit tidak match</h3>
                        <p class="text-sm text-amber-800/80 mt-0.5">{{ count($listMismatch) }} unit tidak masuk ke data majalah</p>
                    </div>
                    <button type="button" onclick="tutupMismatch()" aria-label="Tutup"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-amber-800/60 hover:text-amber-900 hover:bg-amber-100 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-amber-100">
                            <tr class="text-left text-amber-900">
                                <th class="px-4 py-3 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">No Cab</th>
                                <th class="px-4 py-3 font-semibold">Nama di Excel</th>
                                <th class="px-4 py-3 font-semibold">Nama di Unit Kemitraan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100">
                            @foreach ($listMismatch as $i => $m)
                                <tr class="hover:bg-amber-50/60">
                                    <td class="px-4 py-3 text-navy-950/60">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-navy-950">{{ $m['no_cab'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-red-600">{{ $m['nama_excel'] ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-emerald-700">{{ $m['nama_master'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-navy-950/10 bg-navy-950/[0.02] flex justify-end">
                    <button type="button" onclick="tutupMismatch()"
                            class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('.select2').select2({ placeholder: 'Cari / pilih...', allowClear: true, width: '100%' });
    });

    function bukaMismatch() {
        var m = document.getElementById('mismatchModal');
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function tutupMismatch() {
        var m = document.getElementById('mismatchModal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupMismatch(); });

    @if (session('unit_nama_mismatch'))
        // Buka otomatis setelah import yang menghasilkan nama tidak match
        document.addEventListener('DOMContentLoaded', bukaMismatch);
    @endif
</script>
@endpush