@if(!empty($item->barang_belum_datang))
<div class="mt-1.5">
    <span class="badge-lock" style="background:#fee2e2;color:#991b1b;display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:.72rem;font-weight:600;white-space:nowrap"
          title="Menunggu barang datang: {{ implode(', ', $item->barang_belum_datang) }}">
        Barang belum datang ({{ implode(', ', $item->barang_belum_datang) }})
    </span>
</div>
@endif
