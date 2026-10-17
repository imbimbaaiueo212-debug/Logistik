{{--
    Filter rumah (Jakarta) atau vendor (Logistik) di menu Penjualan.
    Param: $mode = 'jakarta' | 'logistik', $aktif = kunci pilihan yang sedang dibuka (SEMUA, JKT, JKTP, ... / AKTIF, PASIF) atau null.
    Tiap pilihan membuka wadah (halaman) rumah/vendor itu; Rekap Aktual ada di tombol Rekap pada halaman wadahnya.
--}}
@php
    $mode  = $mode ?? 'jakarta';
    $aktif = strtoupper((string) ($aktif ?? ''));
    $opsi  = \App\Modules\Order\Http\Controllers\PenjualanController::opsi($mode);
@endphp
<div class="flex flex-wrap items-center gap-2 mb-5">
    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-1">
        {{ $mode === 'logistik' ? 'Vendor' : 'Rumah' }}
    </span>
    @foreach($opsi as $key => $label)
        <a href="{{ \App\Modules\Order\Http\Controllers\PenjualanController::url($mode, $key) }}"
           class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition
                  {{ $aktif === $key ? 'bg-[#162749] text-white border-[#162749]' : 'bg-white text-gray-700 border-gray-300 hover:border-[#E85D2A] hover:text-[#E85D2A]' }}">
            {{ $label }}
        </a>
    @endforeach

</div>
