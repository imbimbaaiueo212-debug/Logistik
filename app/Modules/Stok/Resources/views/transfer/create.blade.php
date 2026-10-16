@extends('layouts.app')

@section('title', 'Transfer Gudang')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<form method="POST" action="{{ route('transfer.store') }}">
@csrf

<label>Gudang Asal</label>
<select id="from" name="from_warehouse" class="border p-2 w-full mb-4">
    <option value="">Pilih Gudang</option>
    @foreach($warehouses as $w)
        <option value="{{ $w->id }}">{{ $w->name }}</option>
    @endforeach
</select>

<label>Gudang Tujuan</label>
<select name="to_warehouse" class="border p-2 w-full mb-4">
    @foreach($warehouses as $w)
        <option value="{{ $w->id }}">{{ $w->name }}</option>
    @endforeach
</select>

<table class="w-full border">
<thead>
<tr>
<th>Produk</th>
<th>Stok</th>
<th>Qty</th>
</tr>
</thead>
<tbody id="stock-body"></tbody>
</table>

<button class="bg-blue-500 text-white px-4 py-2 mt-4">Transfer</button>

</form>
</div>

<script>
document.getElementById('from').addEventListener('change', function() {
    let id = this.value;

    fetch(`/transfer/stock/${id}`)
    .then(res => res.json())
    .then(data => {

        let tbody = document.getElementById('stock-body');
        tbody.innerHTML = '';

        data.forEach((item, index) => {

            let row = `
            <tr>
                <td>
                    ${item.product.name}
                    <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                </td>
                <td>${item.qty}</td>
                <td>
                    <input type="number" name="items[${index}][qty]" max="${item.qty}" class="border p-1">
                </td>
            </tr>
            `;

            tbody.innerHTML += row;
        });
    });
});
</script>

@endsection