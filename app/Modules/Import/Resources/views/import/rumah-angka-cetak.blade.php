@extends('layouts.panel')

@section('title', 'Rumah Angka Cetak')

@section('content')
@include('partials.page-header', [
    'title'     => 'Rumah Angka Cetak Majalah',
    'subtitle'  => 'Daftar order di balik tiap kode pada Report Angka Cetak',
    'back'      => route('import.report-angka-cetak', ['edisi' => $edisi]),
    'backLabel' => 'Kembali ke Report Angka Cetak',
])

<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    @php $inp = 'border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]'; @endphp
    <div>
        <label class="block text-xs text-gray-500 mb-1">Edisi</label>
        <select name="edisi" class="{{ $inp }}" onchange="this.form.submit()">
            @foreach($edisiList as $e)
                <option value="{{ $e }}" {{ $edisi === $e ? 'selected' : '' }}>{{ $e }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Kode</label>
        <select name="kode" class="{{ $inp }}" onchange="this.form.submit()">
            @foreach($kodeTersedia as $k => $nama)
                <option value="{{ $k }}" {{ $kode === $k ? 'selected' : '' }}>{{ $k }} - {{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Tampilkan</label>
        <select name="tampil" class="{{ $inp }}" onchange="this.form.submit()">
            <option value="dihitung" {{ $tampil === 'dihitung' ? 'selected' : '' }}>Yang dihitung</option>
            <option value="tidak"    {{ $tampil === 'tidak' ? 'selected' : '' }}>Yang tidak dihitung</option>
            <option value="semua"    {{ $tampil === 'semua' ? 'selected' : '' }}>Semua</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Angka Excel</label>
        <input type="text" name="excel" value="{{ $excel ?: '' }}" class="{{ $inp }} w-28" placeholder="45225">
    </div>
    <button class="px-4 py-2.5 rounded-xl bg-[#162749] text-white text-sm font-medium">Terapkan</button>
    @if($data)
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
           class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm text-gray-700 bg-white">Unduh CSV</a>
    @endif
</form>

@if(!$data)
    <div class="bg-white rounded-2xl border border-dashed border-gray-300 py-16 px-6 text-center text-sm text-gray-400">
        Pilih edisi dan kode yang tersedia.
    </div>
@else
    @php
        $totalTampil = (int) $data['rows']->sum('qty');
        $jumlahTampil = $data['rows']->count();
    @endphp

    @include('partials.stat-cards', ['stats' => array_values(array_filter([
        ['Total Qty', number_format($totalTampil), true],
        ['Jumlah Baris Order', number_format($jumlahTampil)],
        $tampil === 'dihitung' && $excel
            ? ['Selisih dengan Excel (' . number_format($excel) . ')', ($totalTampil - $excel > 0 ? '+' : '') . number_format($totalTampil - $excel)]
            : null,
    ]))])

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-auto" style="max-height: calc(100vh - 360px); min-height: 240px;">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        @foreach(['No','Order ID','Tanggal','Nama Billing','Status','Qty','Keterangan'] as $h)
                            <th class="sticky top-0 bg-[#162749] text-white py-2.5 px-4 whitespace-nowrap {{ $h === 'Qty' ? 'text-right' : 'text-left' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['rows'] as $r)
                        <tr class="hover:bg-gray-50 {{ $r['duplikat'] ? 'bg-orange-50' : '' }}">
                            <td class="py-2 px-4 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="py-2 px-4 whitespace-nowrap">{{ $r['order_id'] }}@if($r['duplikat']) <span class="text-xs text-[#D14E1F]">(dobel)</span>@endif</td>
                            <td class="py-2 px-4 whitespace-nowrap">{{ $r['tanggal'] }}</td>
                            <td class="py-2 px-4">{{ $r['nama'] }}</td>
                            <td class="py-2 px-4 whitespace-nowrap">{{ $r['status'] }}</td>
                            <td class="py-2 px-4 text-right font-semibold">{{ number_format($r['qty']) }}</td>
                            <td class="py-2 px-4 text-xs text-gray-500">{{ $r['alasan'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-gray-400">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-t border-gray-100 font-semibold text-sm">
            <span class="text-gray-800">Total ({{ number_format($jumlahTampil) }} baris)</span>
            <span class="text-[#28447F]">{{ number_format($totalTampil) }}</span>
        </div>
    </div>
@endif
@endsection