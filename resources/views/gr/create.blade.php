@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('gr.store') }}" method="POST">
        @csrf

        <!-- PILIH PO & Gudang -->
        <select id="po" name="po_id" class="border p-2 mb-4 w-full rounded-lg">
            <option value="">Pilih PO</option>
            @foreach($pos as $po)
                <option value="{{ $po->id }}">PO #{{ $po->id }}</option>
            @endforeach
        </select>

        <select name="warehouse_id" class="border p-2 mb-4 w-full rounded-lg">
            <option value="">Pilih Gudang</option>
            @foreach($warehouses as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
            @endforeach
        </select>

        <!-- TABLE -->
        <table class="w-full border rounded-lg overflow-hidden mb-6">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Product</th>
                    <th class="p-3 text-left">Qty PO</th>
                    <th class="p-3 text-left">Harga</th>
                    <th class="p-3 text-left">Qty Diterima</th>
                </tr>
            </thead>
            <tbody id="gr-body"></tbody>
        </table>

        <!-- Checkbox -->
        <div class="mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="allow_over" value="0">
                <input type="checkbox" name="allow_over" value="1">
                <span>Izinkan kelebihan barang (Over Receive)</span>
            </label>
        </div>

        <!-- Tombol Aksi - Versi Kecil -->
        <div class="flex gap-3">
            <a href="{{ route('gr.index') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ← Back
            </a>    
            
            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Simpan
            </button>
        </div>

    </form>

</div>

<script>
document.getElementById('po').addEventListener('change', function() {
    let id = this.value;
    let tbody = document.getElementById('gr-body');
    tbody.innerHTML = '';

    if (!id) return;

    fetch(`/gr/po/${id}`)
        .then(res => res.json())
        .then(data => {
            data.items.forEach((item, index) => {
                let row = `
                    <tr class="border-b last:border-none">
                        <td class="p-3">${item.product.name}
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        </td>
                        <td class="p-3">${item.qty}</td>
                        <td class="p-3">
                            Rp ${parseInt(item.price).toLocaleString('id-ID')}
                            <input type="hidden" name="items[${index}][price]" value="${item.price}">
                        </td>
                        <td class="p-3">
                            <input type="number" name="items[${index}][qty]" 
                                   class="border p-2 w-24 rounded-lg focus:outline-none focus:border-green-500" 
                                   min="0">
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        });
});
</script>

@endsection