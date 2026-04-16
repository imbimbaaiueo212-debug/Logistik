@extends('layouts.app')

@section('title', 'Buat Return Distribusi')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<h2 class="text-xl font-bold mb-4">Buat Return Distribusi</h2>

<form method="POST" action="{{ route('return-distribution.store') }}">
@csrf

<!-- Pilih Distribusi -->
<select id="distribution" name="distribution_id"
    class="border p-2 rounded w-full mb-3">
    <option value="">Pilih Distribusi</option>
    @foreach($distributions as $d)
        <option value="{{ $d->id }}">
            Distribusi #{{ $d->id }}
        </option>
    @endforeach
</select>

<!-- Gudang -->
<select name="warehouse_id" class="border p-2 rounded w-full mb-4">
    @foreach(\App\Models\Warehouse::all() as $w)
        <option value="{{ $w->id }}">{{ $w->name }}</option>
    @endforeach
</select>

<!-- Items -->
<table class="w-full border mb-4">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2">Produk</th>
            <th class="p-2">Qty Return</th>
            <th class="p-2">Alasan</th>
        </tr>
    </thead>

    <tbody id="items"></tbody>
</table>

<button class="bg-blue-500 text-white px-4 py-2 rounded">
    Simpan
</button>

</form>

</div>

<script>
document.getElementById('distribution').addEventListener('change', function() {

    let id = this.value;

    fetch('/return-distribution-items/' + id)
    .then(res => res.json())
    .then(data => {

        let html = '';

        data.forEach((item, i) => {

            html += `
            <tr class="border-t text-center">
                <td>${item.product.name}</td>

                <td>
                    <input type="number" name="items[${i}][qty]"
                        class="border p-1 w-20 text-center">
                    
                    <input type="hidden"
                        name="items[${i}][product_id]"
                        value="${item.product_id}">
                </td>

                <td>
                    <input type="text" name="items[${i}][reason]"
                        class="border p-1">
                </td>
            </tr>
            `;
        });

        document.getElementById('items').innerHTML = html;
    });
});
</script>

@endsection