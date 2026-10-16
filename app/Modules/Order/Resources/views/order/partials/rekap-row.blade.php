<tr class="{{ $item->is_processed ? 'processed-row' : '' }} hover:bg-gray-50">
    <td class="px-4 py-3 font-medium">{{ $item->id_pesan ?? '-' }}</td>
    <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
    <td class="px-4 py-3">{{ $item->billing_last_name ?? '-' }}</td>
    <td class="px-4 py-3">{{ $item->kirim ?? '-' }}</td>
    <td class="px-4 py-3">{{ $item->kab_kota_provinsi ?? '-' }}</td>
    <td class="px-4 py-3 text-sm">{{ Str::limit($item->nama_barang, 80) }}</td>
    <td class="px-4 py-3 text-center">{{ $item->item_qty ?? 0 }}</td>
    <!-- ... isi kolom lain sesuai Blade Anda ... -->
    <td class="text-center px-4 py-3">
        @if(!$item->is_processed)
            <a href="{{ route('order.jakarta-aktif.edit', $item->id) }}" class="text-blue-600 hover:text-blue-700">✏️</a>
        @else
            <span class="text-emerald-600">✅</span>
        @endif
    </td>
</tr>