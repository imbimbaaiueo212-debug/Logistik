@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4">Edit Quotation</h2>

    <form method="POST" action="{{ route('quotation.update', $quotation->id) }}">
        @csrf
        @method('PUT')

        <!-- SUPPLIER -->
        <div class="mb-4">
            <label>Supplier</label>
            <select id="supplier" name="supplier_id"
                class="w-full border p-2 rounded" required>

                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}"
                        {{ optional($quotation->suppliers->first())->supplier_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach

            </select>
        </div>

        <!-- TANGGAL -->
        <div class="mb-4">
            <label>Tanggal</label>
            <input type="date" name="date"
                   value="{{ $quotation->date->format('Y-m-d') }}"
                   class="w-full border p-2 rounded" required>
        </div>

        <!-- ITEMS -->
        <h3 class="font-bold mt-4 mb-2">Items</h3>

        <table class="w-full border" id="items-table">
            <thead>
                <tr class="bg-gray-100">
                    <th>Produk</th>
                    <th width="120">Qty</th>
                    <th width="150">Harga</th>
                    <th width="150">Subtotal</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody>

                @foreach($quotation->items as $i)
                <tr>
                    <td>
                        <select name="items[][product_id]" class="product border p-2 w-full">
                            <option value="{{ $i->product_id }}">
                                {{ $i->product->name }}
                            </option>
                        </select>
                    </td>

                    <td>
                        <input type="number"
                               name="items[][qty]"
                               value="{{ $i->qty }}"
                               class="qty border p-2 w-full text-center">
                    </td>

                    <td>
                        <input type="number"
                               name="items[][price]"
                               value="{{ optional($i->supplierItem)->price ?? 0 }}"
                               class="price border p-2 w-full text-right" readonly>
                    </td>

                    <td>
                        <input type="text"
                               class="subtotal border p-2 w-full text-right bg-gray-100"
                               value="0" readonly>
                    </td>

                    <td class="text-center">
                        <button type="button" onclick="removeRow(this)">✕</button>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>

        <button type="button" onclick="addRow()"
            class="mt-3 bg-blue-500 text-white px-3 py-1 rounded">
            + Tambah Item
        </button>

        <!-- TOTAL -->
        <div class="mt-6 text-right">
            <h3 class="text-lg font-bold">
                Total: Rp <span id="grand-total">0</span>
            </h3>
        </div>

        <input type="hidden" name="total" id="total-input">

        <div class="mt-6 flex gap-2">
            <button class="bg-green-500 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('quotation.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
               ← Back
            </a>
        </div>

    </form>

</div>

<script>
let products = [];

// ================= LOAD PRODUCT =================
document.getElementById('supplier').addEventListener('change', function () {

    let supplierId = this.value;

    fetch('/get-products/' + supplierId)
        .then(res => res.json())
        .then(data => {
            products = data;
        });
});


// ================= ADD ROW =================
function addRow() {

    if (products.length === 0) {
        alert('Pilih supplier dulu!');
        return;
    }

    let options = '';

    products.forEach(p => {
        options += `<option value="${p.id}" data-price="${p.price}">
            ${p.name}
        </option>`;
    });

    let row = `
    <tr>
        <td>
            <select name="items[][product_id]" class="product border p-2 w-full">
                ${options}
            </select>
        </td>

        <td>
            <input type="number" name="items[][qty]" value="1"
                class="qty border p-2 w-full text-center">
        </td>

        <td>
            <input type="number" name="items[][price]"
                class="price border p-2 w-full text-right" readonly>
        </td>

        <td>
            <input type="text"
                class="subtotal border p-2 w-full text-right bg-gray-100"
                readonly>
        </td>

        <td>
            <button type="button" onclick="removeRow(this)">✕</button>
        </td>
    </tr>`;

    document.querySelector('#items-table tbody')
        .insertAdjacentHTML('beforeend', row);

    let lastRow = document.querySelector('#items-table tbody tr:last-child');

    setPrice(lastRow);
    calculateRow(lastRow);
    calculateTotal();
}


// ================= REMOVE =================
function removeRow(btn) {
    btn.closest('tr').remove();
    calculateTotal();
}


// ================= SET PRICE =================
function setPrice(row) {
    let select = row.querySelector('.product');
    let price = select.options[select.selectedIndex].dataset.price || 0;
    row.querySelector('.price').value = price;
}


// ================= CALCULATE =================
function calculateRow(row) {
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let price = parseFloat(row.querySelector('.price').value) || 0;

    let subtotal = qty * price;

    row.querySelector('.subtotal').value =
        new Intl.NumberFormat('id-ID').format(subtotal);
}


// ================= TOTAL =================
function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.subtotal').forEach(el => {
        let val = el.value.replace(/\./g, '');
        total += parseFloat(val) || 0;
    });

    document.getElementById('grand-total').innerText =
        new Intl.NumberFormat('id-ID').format(total);

    document.getElementById('total-input').value = total;
}


// ================= EVENTS =================
document.addEventListener('change', function(e) {

    let row = e.target.closest('tr');
    if (!row) return;

    if (e.target.classList.contains('product')) {
        setPrice(row);
        calculateRow(row);
        calculateTotal();
    }

    if (e.target.classList.contains('qty')) {
        calculateRow(row);
        calculateTotal();
    }
});


// ================= INIT =================
window.onload = function() {
    document.querySelectorAll('#items-table tbody tr').forEach(row => {
        calculateRow(row);
    });
    calculateTotal();
}
</script>

@endsection