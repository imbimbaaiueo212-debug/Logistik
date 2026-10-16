@extends('layouts.app')

@section('title', 'QC Barang')

@section('content')

<div class="p-6 bg-white rounded shadow">

    <h2 class="text-xl font-bold mb-4">
        QC - Goods Receipt #{{ $gr->id }}
    </h2>

    <p class="mb-4">
        Gudang: <b>{{ $gr->warehouse->name }}</b>
    </p>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-left">Produk</th>
                <th>Total</th>
                <th>OK</th>
                <th>Reject</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($gr->items as $item)
            <tr>
                <td class="p-2">{{ $item->product->name }}</td>

                <td class="text-center">
                    {{ $item->qty_received }}
                </td>

                <td class="text-center">
                    <input type="number"
                        name="qty_ok"
                        class="border px-2 py-1 w-20 text-center qty-ok"
                        data-id="{{ $item->id }}"
                        value="{{ $item->qty_ok }}">
                </td>

                <td class="text-center">
                    <input type="number"
                        name="qty_reject"
                        class="border px-2 py-1 w-20 text-center qty-reject"
                        data-id="{{ $item->id }}"
                        value="{{ $item->qty_reject }}">
                </td>

                <td class="text-center">
                    @if(!$item->is_qc_done)
                    <button
                        class="btn-qc bg-blue-600 text-white px-3 py-1 rounded"
                        data-id="{{ $item->id }}"
                        data-total="{{ $item->qty_received }}">
                        Simpan
                    </button>
                    @else
                    <span class="text-green-600">QC Done</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-qc').forEach(btn => {

        btn.addEventListener('click', function() {

            let id = this.dataset.id;
            let row = this.closest('tr');

            let qtyOk = parseInt(row.querySelector('.qty-ok').value || 0);
            let qtyReject = parseInt(row.querySelector('.qty-reject').value || 0);
            let total = parseInt(this.dataset.total);

            if ((qtyOk + qtyReject) !== total) {
                alert('Total QC harus sama dengan qty datang!');
                return;
            }

            fetch("{{ url('/gr/qc') }}/" + id, {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        qty_ok: qtyOk,
        qty_reject: qtyReject
    })
})
.then(async res => {

    let data;

    try {
        data = await res.json();
    } catch (e) {
        alert('Response bukan JSON (kemungkinan error server)');
        return;
    }

    if (!res.ok) {
        alert(data.error || 'Terjadi error');
        return;
    }

    alert('QC berhasil');
window.location.href = data.redirect;
})
.catch(err => {
    console.error(err);
    alert('Server error!');
});

        });

    });

});
</script>