@extends('layouts.app')

@section('title', 'Purchase Order')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <form action="{{ route('po.store') }}" method="POST">
        @csrf

        <!-- Supplier -->
        <select id="supplier" name="supplier_id" class="border p-2 mb-4 w-full">
            <option value="">Pilih Supplier</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>

        <!-- Table -->
        <table class="w-full border mb-4">
            <thead>
                <tr>
                    <th class="p-2">Product</th>
                    <th class="p-2">Harga</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2">Subtotal</th>
                </tr>
            </thead>
            <tbody id="po-body"></tbody>
        </table>

        <h3 class="text-right font-bold mb-4">
            Total: Rp <span id="total">0</span>
        </h3>

        <button class="bg-blue-500 text-white px-4 py-2 rounded">
            Simpan PO
        </button>

    </form>

</div>

<script>
document.getElementById('supplier').addEventListener('change', function() {
    let supplierId = this.value;

    fetch(`/get-products/${supplierId}`)
        .then(res => res.json())
        .then(data => {

            let tbody = document.getElementById('po-body');
            tbody.innerHTML = '';

            data.forEach((p, index) => {

                let price = p.pivot.price;

                let row = `
                    <tr>
                        <td class="p-2">
                            ${p.name}
                            <input type="hidden" name="products[${index}][product_id]" value="${p.id}">
                        </td>

                        <td class="p-2">
                            ${price}
                            <input type="hidden" name="products[${index}][price]" value="${price}">
                        </td>

                        <td class="p-2">
                            <input type="number" name="products[${index}][qty]" 
                                   class="qty border p-1 w-20" data-price="${price}">
                        </td>

                        <td class="p-2 subtotal">0</td>
                    </tr>
                `;

                tbody.innerHTML += row;
            });

            attachEvents();
        });
});

function attachEvents() {
    document.querySelectorAll('.qty').forEach(input => {
        input.addEventListener('input', function() {

            let qty = this.value;
            let price = this.dataset.price;

            let subtotal = qty * price;
            this.closest('tr').querySelector('.subtotal').innerText = subtotal;

            calculateTotal();
        });
    });
}

function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.subtotal').forEach(el => {
        total += parseInt(el.innerText) || 0;
    });

    document.getElementById('total').innerText = total;
}
</script>

@endsection