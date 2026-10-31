@php
    $__tertahan = app(\App\Modules\Picking\Services\KetersediaanBarangService::class)->tertahan();
@endphp

@if(session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

@if($__tertahan->isNotEmpty())
<div class="rounded-3xl p-5 mb-6" style="background:#fef2f2;border:1px solid #fecaca;color:#7f1d1d">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
        <div class="font-semibold">Barang belum datang &mdash; Picking tertahan sampai ditandai datang</div>
        <a href="{{ route('picking.barang.index') }}" class="text-sm underline">Kelola Ketersediaan Barang</a>
    </div>
    <div class="flex flex-wrap gap-3">
        @foreach($__tertahan as $t)
        <form method="POST" action="{{ route('picking.barang.update') }}" class="bg-white border border-red-200 rounded-2xl px-4 py-2 flex items-center gap-3"
              onsubmit="return confirm('Tandai {{ $t['jenis'] }} {{ $t['kode'] }} sudah datang? Semua Picking edisi/tahun ini akan terbuka.')">
            @csrf
            <input type="hidden" name="jenis" value="{{ $t['jenis'] }}">
            <input type="hidden" name="kode" value="{{ $t['kode'] }}">
            <input type="hidden" name="status" value="datang">
            <span><b>{{ $t['jenis'] }} {{ $t['kode'] }}</b> &middot; {{ $t['jumlah'] }} picking menunggu</span>
            <button type="submit" class="bg-green-600 text-white px-4 py-1.5 rounded-xl text-sm hover:bg-green-700">Barang Sudah Datang</button>
        </form>
        @endforeach
    </div>
</div>
@endif
