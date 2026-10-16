@extends('layouts.panel')

@section('title', 'Detail DLC ' . $periode->edisi)

@push('styles')
<style>
    .data-table-wrap { overflow: auto; max-height: calc(100vh - 340px); }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 20;
        background: #162749; color: #fff; text-align: left;
        padding: 12px 16px; font-weight: 600; white-space: nowrap;
    }
    .data-table tbody td, .data-table tfoot td {
        padding: 11px 16px; border-bottom: 1px solid #eef0f5;
        background: #fff; white-space: nowrap;
    }
    .data-table tbody tr:hover td { background: #f7f8fb; }
    .data-table tfoot td { background: #f7f8fb; font-weight: 700; }
    .data-table th:first-child, .data-table td:first-child { position: sticky; left: 0; z-index: 10; }
    .data-table thead th:first-child { z-index: 30; }
    .data-table tfoot td:first-child { position: static; }
    .data-table th.col-aksi, .data-table td.col-aksi { position: sticky; right: 0; z-index: 10; }
    .data-table thead th.col-aksi { z-index: 30; }
    .cell-clip { display: block; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endpush

@section('content')
@php
    $kolom = [
        ['key' => 'no',        'label' => 'No'],
        ['key' => 'nama_unit', 'label' => 'Nama Unit', 'clip' => true, 'class' => 'font-medium text-gray-800'],
        ['key' => 'qty',       'label' => 'Qty',       'align' => 'right', 'class' => 'font-semibold'],
    ];
    $info = [
        ['Edisi',       $periode->edisi],
        ['Periode',     $periode->periode],
        ['Jumlah Unit', $periode->pesanan->count()],
        ['Total Qty',   number_format($total)],
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-[#162749]">{{ $periode->judul ?? $periode->edisi }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">Periode: {{ $periode->periode }}</p>
    </div>
    <a href="{{ route('import.dlc.index') }}"
       class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
        Kembali
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl mb-4">{{ session('success') }}</div>
@endif

{{-- Info header --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($info as [$label, $nilai])
            <div>
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="font-semibold text-base {{ $label === 'Total Qty' ? 'text-[#E85D2A]' : 'text-[#162749]' }}">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Tabel unit --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($kolom as $k)
                        <th style="text-align: {{ $k['align'] ?? 'left' }}">{{ $k['label'] }}</th>
                    @endforeach
                    <th class="col-aksi" style="text-align: center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periode->pesanan as $index => $item)
                    <tr>
                        @foreach($kolom as $k)
                            <td class="{{ $k['class'] ?? '' }}" style="text-align: {{ $k['align'] ?? 'left' }}">
                                @if($k['key'] === 'no')
                                    {{ $index + 1 }}
                                @elseif($k['key'] === 'qty')
                                    {{ number_format($item->qty) }}
                                @else
                                    <span class="cell-clip" title="{{ $item->{$k['key']} }}">{{ $item->{$k['key']} }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="col-aksi" style="text-align: center">
                            <button type="button" title="Edit qty" aria-label="Edit qty"
                                    data-id="{{ $item->id }}" data-nama="{{ $item->nama_unit }}" data-qty="{{ $item->qty }}"
                                    class="btn-edit-qty p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ count($kolom) - 1 }}" style="text-align: right">Jumlah</td>
                    <td style="text-align: right" class="text-[#E85D2A]">{{ number_format($total) }}</td>
                    <td class="col-aksi"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Modal edit qty --}}
<div id="editModal" class="fixed inset-0 bg-[#0F1B33]/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="text-lg font-bold text-[#162749]">Edit Qty</h3>
        <p class="text-sm text-gray-500 mb-4" id="modal-unit-name">-</p>

        <label for="modal-qty" class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
        <input type="number" id="modal-qty" min="0"
               class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]">
        <p id="modal-error" class="hidden text-xs text-red-600 mt-2"></p>

        <div class="flex justify-end gap-2 mt-5">
            <button type="button" id="modal-batal"
                    class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
            <button type="button" id="modal-simpan"
                    class="px-4 py-2.5 bg-[#E85D2A] hover:bg-[#D14E1F] text-white rounded-xl text-sm font-medium">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('editModal');
        var qtyEl = document.getElementById('modal-qty');
        var errEl = document.getElementById('modal-error');
        var currentId = null;
        var baseUrl = "{{ url('/import/dlc/pesanan') }}";

        function bukaModal(id, nama, qty) {
            currentId = id;
            document.getElementById('modal-unit-name').textContent = nama;
            qtyEl.value = qty;
            errEl.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            qtyEl.focus();
            qtyEl.select();
        }

        function tutupModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentId = null;
        }

        function tampilError(msg) {
            errEl.textContent = msg;
            errEl.classList.remove('hidden');
        }

        function simpanQty() {
            var qty = qtyEl.value;
            if (qty === '' || parseInt(qty, 10) < 0) { tampilError('Qty tidak valid.'); return; }

            fetch(baseUrl + '/' + currentId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qty: parseInt(qty, 10) })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) { location.reload(); }
                else { tampilError(data.message || 'Gagal mengupdate.'); }
            })
            .catch(function () { tampilError('Terjadi kesalahan. Coba lagi.'); });
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-edit-qty');
            if (btn) bukaModal(btn.dataset.id, btn.dataset.nama, btn.dataset.qty);
        });
        document.getElementById('modal-batal').addEventListener('click', tutupModal);
        document.getElementById('modal-simpan').addEventListener('click', simpanQty);
        modal.addEventListener('click', function (e) { if (e.target === modal) tutupModal(); });
        qtyEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); simpanQty(); }
        });
    })();
</script>
@endpush