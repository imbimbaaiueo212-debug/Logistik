{{--
    Form Pasif Manual (dipakai create & edit).
    Param: $action, $cancel (url), $submit (label), [$periode] -> ada = mode edit
--}}
@php
    $edit  = !empty($periode);
    $items = $edit ? $periode->transaksis->values() : collect();
    $n     = old('items') ? count(old('items')) : ($edit ? $items->count() : 1);
    $n     = max($n, 1);

    $ctl  = 'w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
    $cell = 'border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';

    $head = [
        ['name' => 'edisi',   'label' => 'Edisi',   'ph' => 'contoh: 161', 'req' => true],
        ['name' => 'judul',   'label' => 'Judul',   'ph' => 'Majalah Edisi 161'],
        ['name' => 'periode', 'label' => 'Periode', 'ph' => '08-2026'],
        ['name' => 'bulan',   'label' => 'Bulan',   'ph' => 'Agustus'],
        ['name' => 'tahun',   'label' => 'Tahun',   'default' => date('Y')],
        ['name' => 'no_ps',   'label' => 'No PS'],
    ];

    $cols = [
        ['name' => 'id_pesan',            'label' => 'Id Pesan',   'type' => 'text',   'w' => 'w-28'],
        ['name' => 'tgl_pesan',           'label' => 'Tgl Pesan',  'type' => 'date',   'w' => 'w-36'],
        ['name' => 'nama_unit',           'label' => 'Nama Unit',  'type' => 'text',   'w' => 'w-48', 'req' => true],
        ['name' => 'label',               'label' => 'Label',      'type' => 'text',   'w' => 'w-24', 'ph' => 'M161'],
        ['name' => 'jumlah',              'label' => 'Jumlah',     'type' => 'number', 'w' => 'w-20', 'req' => true, 'default' => 1, 'min' => 1],
        ['name' => 'ekspedisi',           'label' => 'Ekspedisi',  'type' => 'text',   'w' => 'w-32', 'ph' => 'Lion Parcel'],
        ['name' => 'service_pengiriman',  'label' => 'Service',    'type' => 'text',   'w' => 'w-28', 'ph' => 'REGPACK'],
        ['name' => 'note',                'label' => 'Note',       'type' => 'text',   'w' => 'w-32'],
        ['name' => 'keterangan',          'label' => 'Keterangan', 'type' => 'text',   'w' => 'w-36'],
    ];

    $nilai = function ($i, $c) use ($items) {
        $item = $items->get($i);
        $v = $item ? $item->{$c['name']} : ($c['default'] ?? '');
        if ($c['name'] === 'tgl_pesan' && $v) {
            $v = \Carbon\Carbon::parse($v)->format('Y-m-d');
        }
        return old('items.' . $i . '.' . $c['name'], $v);
    };

    $trash = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>';
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if($edit)
        @method('PUT')
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi periode --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h3 class="text-base font-semibold text-[#162749] mb-4">Informasi Periode</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($head as $h)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $h['label'] }} @if(!empty($h['req']))<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" name="{{ $h['name'] }}"
                           value="{{ old($h['name'], $edit ? $periode->{$h['name']} : ($h['default'] ?? '')) }}"
                           placeholder="{{ $h['ph'] ?? '' }}" {{ !empty($h['req']) ? 'required' : '' }}
                           class="{{ $ctl }}">
                </div>
            @endforeach

            @if($edit)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="{{ $ctl }}">
                        @foreach(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('status', $periode->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    {{-- Daftar unit --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-[#162749]">Daftar Unit (Transaksi)</h3>
            <button type="button" id="btn-tambah"
                    class="inline-flex items-center gap-1.5 bg-[#162749] hover:bg-[#1D3361] text-white px-3.5 py-2 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Unit
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600">
                        <th class="px-2 py-2.5 font-semibold whitespace-nowrap rounded-l-lg">No</th>
                        @foreach($cols as $c)
                            <th class="px-2 py-2.5 font-semibold whitespace-nowrap">{{ $c['label'] }} @if(!empty($c['req']))<span class="text-red-500">*</span>@endif</th>
                        @endforeach
                        <th class="px-2 py-2.5 font-semibold rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bodyUnit">
                    @for($i = 0; $i < $n; $i++)
                        <tr class="border-b border-gray-100">
                            <td class="row-no px-2 py-2 text-center text-gray-400">{{ $i + 1 }}</td>
                            @foreach($cols as $c)
                                <td class="px-2 py-2">
                                    <input type="{{ $c['type'] }}" name="items[{{ $i }}][{{ $c['name'] }}]"
                                           value="{{ $nilai($i, $c) }}" placeholder="{{ $c['ph'] ?? '' }}"
                                           {{ !empty($c['req']) ? 'required' : '' }} {!! isset($c['min']) ? 'min="' . $c['min'] . '"' : '' !!}
                                           class="{{ $c['w'] }} {{ $cell }}">
                                </td>
                            @endforeach
                            <td class="px-2 py-2 text-center">
                                <button type="button" title="Hapus baris" aria-label="Hapus baris"
                                        class="btn-hapus p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">{!! $trash !!}</button>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="submit"
                    class="bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
                {{ $submit }}
            </button>
            <a href="{{ $cancel }}"
               class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </div>
</form>

{{-- Template baris baru --}}
<template id="tpl-row">
    <tr class="border-b border-gray-100">
        <td class="row-no px-2 py-2 text-center text-gray-400"></td>
        @foreach($cols as $c)
            <td class="px-2 py-2">
                <input type="{{ $c['type'] }}" name="items[__I__][{{ $c['name'] }}]"
                       value="{{ $c['default'] ?? '' }}" placeholder="{{ $c['ph'] ?? '' }}"
                       {{ !empty($c['req']) ? 'required' : '' }} {!! isset($c['min']) ? 'min="' . $c['min'] . '"' : '' !!}
                       class="{{ $c['w'] }} {{ $cell }}">
            </td>
        @endforeach
        <td class="px-2 py-2 text-center">
            <button type="button" title="Hapus baris" aria-label="Hapus baris"
                    class="btn-hapus p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">{!! $trash !!}</button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    (function () {
        var body = document.getElementById('bodyUnit');
        var tpl = document.getElementById('tpl-row').innerHTML;
        var idx = {{ $n }};

        function nomori() {
            body.querySelectorAll('tr').forEach(function (tr, i) {
                tr.querySelector('.row-no').textContent = i + 1;
            });
        }

        document.getElementById('btn-tambah').addEventListener('click', function () {
            body.insertAdjacentHTML('beforeend', tpl.replace(/__I__/g, idx++));
            nomori();
        });

        body.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-hapus');
            if (!btn) return;
            if (body.children.length <= 1) { alert('Minimal harus ada 1 baris.'); return; }
            btn.closest('tr').remove();
            nomori();
        });
    })();
</script>
@endpush
