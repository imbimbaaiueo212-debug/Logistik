@extends('layouts.app')

@section('title', 'Buat Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<h2 class="text-xl font-bold mb-4">Buat Distribusi</h2>

<form method="POST" action="{{ route('distribution.store') }}">
@csrf

<!-- Gudang -->
<select name="warehouse_id" id="warehouse"
    class="border p-2 rounded w-full mb-3">
    <option value="">Pilih Gudang</option>
    @foreach($warehouses as $w)
        <option value="{{ $w->id }}">{{ $w->name }}</option>
    @endforeach
</select>

<!-- Tujuan -->
<input type="text" name="destination"
    placeholder="Tujuan (contoh: Unit A)"
    class="border p-2 rounded w-full mb-4">

<!-- Items -->
<table class="w-full border mb-4">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2">Produk</th>
            <th class="p-2">Stok</th>
            <th class="p-2">Qty</th>
        </tr>
    </thead>
    <tbody id="items"></tbody>
</table>

        <div class="justify-between mb-5 gap-3">
            <a href="{{ route('distribution.index') }}" 
               class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition flex-1 text-center">
                Kembali
            </a>
            
            <button type="submit" 
                    class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition flex-1">
                Simpan
            </button>
        </div>

</form>

</div>

<script>
document.getElementById('warehouse').addEventListener('change', function() {

    let id = this.value;

    fetch('/distribution-stock/' + id)
    .then(res => res.json())
    .then(data => {

        let html = '';

        data.forEach((item, index) => {

            html += `
            <tr class="text-center border-t">
                <td>${item.product.name}</td>
                <td>${item.qty}</td>
                <td>
                    <input type="number" name="items[${index}][qty]" 
                        class="border p-1 w-20 text-center">

                    <input type="hidden" 
                        name="items[${index}][product_id]" 
                        value="${item.product_id}">
                </td>
            </tr>
            `;
        });

        document.getElementById('items').innerHTML = html;
    });
});
</script>

@endsection