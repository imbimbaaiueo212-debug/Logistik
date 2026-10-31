{{--
    <x-hold-barang :row="$item" /> — penanda "Barang belum datang" untuk Majalah/Kalender.
    Hanya penanda: pesanan tetap berjalan di Penjualan. Yang menahan adalah Picking.
--}}
@props(['row'])
@php
    $__belum = app(\App\Modules\Picking\Services\KetersediaanBarangService::class)->belumDatang($row);
@endphp
@if(!empty($__belum))
    <div class="mt-1">
        <span title="Menunggu barang datang. Picking tertahan sampai ditandai datang."
              style="display:inline-flex;align-items:center;background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:9999px;font-size:.72rem;font-weight:600;white-space:nowrap">
            Barang belum datang ({{ implode(', ', $__belum) }})
        </span>
    </div>
@endif
