@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded shadow">

    {{-- HEADER --}}
    <div class="flex justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold">{{ $so->code }}</h2>
            <p>Gudang: {{ $so->warehouse->name }}</p>

            {{-- STATUS BADGE --}}
            <p>
                Status:
                @if($so->status == 'draft')
                    <span class="bg-gray-200 px-2 py-1 rounded text-xs">Draft</span>
                @elseif($so->status == 'submitted')
                    <span class="bg-yellow-200 px-2 py-1 rounded text-xs">Submitted</span>
                @elseif($so->status == 'approved')
                    <span class="bg-green-200 px-2 py-1 rounded text-xs">Approved</span>
                @elseif($so->status == 'cancelled')
                    <span class="bg-red-200 px-2 py-1 rounded text-xs">Cancelled</span>
                @endif
            </p>
        </div>

        {{-- ACTION BUTTON --}}
        <div class="flex gap-2">

            @if($so->isDraft())
                <form method="POST" action="{{ route('stock-opname.submit', $so->id) }}">
                    @csrf
                    <button class="bg-blue-600 text-white px-4 py-2 rounded">
                        Submit
                    </button>
                </form>

                <form method="POST" action="{{ route('stock-opname.cancel', $so->id) }}">
                    @csrf
                    <button class="bg-red-600 text-white px-4 py-2 rounded">
                        Cancel
                    </button>
                </form>
            @endif

            @if($so->isSubmitted())
                <form method="POST" action="{{ route('stock-opname.approve', $so->id) }}">
                    @csrf
                    <button class="bg-green-600 text-white px-4 py-2 rounded">
                        Approve
                    </button>
                </form>
            @endif

        </div>
    </div>

    {{-- TABLE --}}
    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-left">Produk</th>
                <th>System</th>
                <th>Physical</th>
                <th>Selisih</th>
            </tr>
        </thead>

        <tbody>
            @foreach($so->items as $item)
            <tr>
                <td class="p-2">{{ $item->product->name }}</td>

                <td class="text-center">
                    {{ $item->system_qty }}
                </td>

                <td class="text-center">
                    <input type="number"
                        class="input-qty border px-2 py-1 w-20 text-center"
                        data-id="{{ $item->id }}"
                        data-system="{{ $item->system_qty }}"
                        value="{{ $item->physical_qty ?? '' }}"
                        {{ !$so->isDraft() ? 'disabled' : '' }}>
                </td>

                <td class="text-center selisih">
                    {{ $item->selisih }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

{{-- JS --}}
<script>
document.querySelectorAll('.input-qty').forEach(input => {

    // HITUNG SELISIH REALTIME
    input.addEventListener('input', function() {

        let system = parseInt(this.dataset.system);
        let physical = this.value === '' ? null : parseInt(this.value);

        let selisih = (physical ?? 0) - system;

        let row = this.closest('tr');
        let cell = row.querySelector('.selisih');

        cell.innerText = selisih;

        if (selisih !== 0) {
            cell.style.color = 'red';
            row.style.background = '#ffecec';
        } else {
            cell.style.color = 'black';
            row.style.background = '';
        }
    });

    // AJAX SAVE
    input.addEventListener('change', function() {

        fetch("{{ route('stock-opname.item.update') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id: this.dataset.id,
                physical_qty: this.value === '' ? null : this.value
            })
        });

    });

    // ENTER → NEXT INPUT
    input.addEventListener('keydown', function(e) {

        if (e.key === 'Enter') {
            e.preventDefault();

            let inputs = document.querySelectorAll('.input-qty');
            let index = [...inputs].indexOf(this);

            if (inputs[index + 1]) {
                inputs[index + 1].focus();
            }
        }
    });

});
</script>

@endsection