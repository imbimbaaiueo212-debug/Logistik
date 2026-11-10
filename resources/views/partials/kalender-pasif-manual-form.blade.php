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
        ['name' => 'edisi',   'label' => 'Edisi',   'ph' => 'contoh: KLD27', 'req' => true],
        ['name' => 'judul',   'label' => 'Judul',   'ph' => 'Kalender KLD27'],
        ['name' => 'periode', 'label' => 'Periode',  'ph' => '08-2026'],
        ['name' => 'bulan',   'label' => 'Bulan',   'ph' => 'Agustus'],
        ['name' => 'tahun',   'label' => 'Tahun',   'default' => date('Y')],
        ['name' => 'no_ps',   'label' => 'No PS'],
    ];

    // Kolom teks / angka biasa. Status Unit, Nama Unit, Ekspedisi, Service dirender khusus (Select2).
    $kolomBiasa = [
        'id_pesan'   => ['label' => 'Id Pesan',   'type' => 'text',   'w' => 'w-28'],
        'tgl_pesan'  => ['label' => 'Tgl Pesan',  'type' => 'date',   'w' => 'w-36'],
        'label'      => ['label' => 'Label',      'type' => 'text',   'w' => 'w-24', 'ph' => 'KLD27'],
        'jumlah'     => ['label' => 'Jumlah',     'type' => 'number', 'w' => 'w-20', 'req' => true, 'default' => 1, 'min' => 1],
        'note'       => ['label' => 'Note',       'type' => 'text',   'w' => 'w-32'],
        'keterangan' => ['label' => 'Keterangan', 'type' => 'text',   'w' => 'w-36'],
    ];

    // Urutan kolom di tabel
    $urutan = [
        ['k' => 'id_pesan'], ['k' => 'tgl_pesan'],
        ['k' => 'status_unit', 'label' => 'Status Unit', 'req' => true],
        ['k' => 'nama_unit',   'label' => 'Nama Unit',   'req' => true],
        ['k' => 'label'], ['k' => 'jumlah'],
        ['k' => 'ekspedisi',   'label' => 'Ekspedisi'],
        ['k' => 'service_pengiriman', 'label' => 'Service'],
        ['k' => 'note'], ['k' => 'keterangan'],
    ];

    // ---- pilihan dropdown ----
    $unitOpsi  = \App\Modules\Kalender\Support\UnitManualOptions::daftar();
    $ekspOpsi  = \App\Modules\Kalender\Support\UnitManualOptions::EKSPEDISI;
    $svcOpsi   = \App\Modules\Kalender\Support\UnitManualOptions::SERVICE;

    // Nama unit yang sudah tersimpan tetapi tidak ada di master tetap ditampilkan (supaya tidak hilang saat edit)
    $namaTersedia = [];
    foreach ($unitOpsi as $grp) { foreach ($grp as $u) { $namaTersedia[mb_strtoupper($u['nama'])] = true; } }

    $nilai = function ($i, $name, $default = '') use ($items) {
        $item = $items->get($i);
        $v = $item ? $item->{$name} : $default;
        if ($name === 'tgl_pesan' && $v) {
            $v = \Carbon\Carbon::parse($v)->format('Y-m-d');
        }
        return old('items.' . $i . '.' . $name, $v);
    };

    // HTML <option> nama unit (sekali jalan, dipakai ulang di semua baris dan template)
    $opsiNamaHtml = function ($terpilih) use ($unitOpsi, $namaTersedia) {
        $h = '<option value=""></option>';
        if ($terpilih !== '' && $terpilih !== null && !isset($namaTersedia[mb_strtoupper($terpilih)])) {
            $h .= '<option value="' . e($terpilih) . '" selected>' . e($terpilih) . '</option>';
        }
        foreach ($unitOpsi as $grup => $daftar) {
            $h .= '<optgroup label="' . e($grup) . '">';
            foreach ($daftar as $u) {
                $sel = ($terpilih !== '' && mb_strtoupper($terpilih) === mb_strtoupper($u['nama'])) ? ' selected' : '';
                $h .= '<option value="' . e($u['nama']) . '"'
                    . ' data-status="' . e($u['status']) . '" data-nocab="' . e($u['no_cab']) . '"'
                    . ' data-alamat="' . e($u['alamat']) . '" data-telepon="' . e($u['telepon']) . '"' . $sel . '>'
                    . e($u['label']) . '</option>';
            }
            $h .= '</optgroup>';
        }
        return $h;
    };

    $opsiEkspHtml = function ($terpilih) use ($ekspOpsi) {
        $h = '<option value=""></option>';
        $daftar = $ekspOpsi;
        if ($terpilih !== '' && $terpilih !== null && !in_array($terpilih, $daftar, true)) { $daftar[] = $terpilih; }
        foreach ($daftar as $e) {
            $h .= '<option value="' . e($e) . '"' . ($terpilih === $e ? ' selected' : '') . '>' . e($e) . '</option>';
        }
        return $h;
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
                        @foreach($urutan as $c)
                            @php
                                $lbl = $c['label'] ?? ($kolomBiasa[$c['k']]['label'] ?? $c['k']);
                                $req = !empty($c['req']) || !empty($kolomBiasa[$c['k']]['req']);
                            @endphp
                            <th class="px-2 py-2.5 font-semibold whitespace-nowrap">{{ $lbl }} @if($req)<span class="text-red-500">*</span>@endif</th>
                        @endforeach
                        <th class="px-2 py-2.5 font-semibold rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody id="bodyUnit">
                    @for($i = 0; $i < $n; $i++)
                        <tr class="border-b border-gray-100">
                            <td class="row-no px-2 py-2 text-center text-gray-400">{{ $i + 1 }}</td>
                            @php
                                $curNama = (string) $nilai($i, 'nama_unit');
                                $curStat = strtolower((string) $nilai($i, 'status_unit'));
                                $curEksp = (string) $nilai($i, 'ekspedisi');
                                $curSvc  = (string) $nilai($i, 'service_pengiriman');
                            @endphp
                            @foreach($urutan as $c)
                                @php $k = $c['k']; @endphp
                                <td class="px-2 py-2">
                                @if($k === 'status_unit')
                                    <select name="items[{{ $i }}][status_unit]" required class="sel-status w-28 {{ $cell }}">
                                        <option value="">Pilih</option>
                                        <option value="aktif" {{ $curStat === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="pasif" {{ $curStat === 'pasif' ? 'selected' : '' }}>Pasif</option>
                                    </select>
                                @elseif($k === 'nama_unit')
                                    <select name="items[{{ $i }}][nama_unit]" required class="sel-nama" data-ph="Pilih unit / mitra / stokis">{!! $opsiNamaHtml($curNama) !!}</select>
                                    <input type="hidden" name="items[{{ $i }}][no_cab]"  class="h-nocab"   value="{{ $nilai($i, 'no_cab') }}">
                                    <input type="hidden" name="items[{{ $i }}][alamat]"  class="h-alamat"  value="{{ $nilai($i, 'alamat') }}">
                                    <input type="hidden" name="items[{{ $i }}][telepon]" class="h-telepon" value="{{ $nilai($i, 'telepon') }}">
                                @elseif($k === 'ekspedisi')
                                    <select name="items[{{ $i }}][ekspedisi]" class="sel-eksp" data-ph="Pilih ekspedisi">{!! $opsiEkspHtml($curEksp) !!}</select>
                                @elseif($k === 'service_pengiriman')
                                    <select name="items[{{ $i }}][service_pengiriman]" class="sel-svc" data-ph="Pilih service" data-selected="{{ $curSvc }}"></select>
                                @else
                                    @php $kb = $kolomBiasa[$k]; @endphp
                                    <input type="{{ $kb['type'] }}" name="items[{{ $i }}][{{ $k }}]"
                                           value="{{ $nilai($i, $k, $kb['default'] ?? '') }}" placeholder="{{ $kb['ph'] ?? '' }}"
                                           {{ !empty($kb['req']) ? 'required' : '' }} {!! isset($kb['min']) ? 'min="' . $kb['min'] . '"' : '' !!}
                                           class="{{ $kb['w'] }} {{ $cell }}">
                                @endif
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
        @foreach($urutan as $c)
            @php $k = $c['k']; @endphp
            <td class="px-2 py-2">
            @if($k === 'status_unit')
                <select name="items[__I__][status_unit]" required class="sel-status w-28 {{ $cell }}">
                    <option value="">Pilih</option>
                    <option value="aktif">Aktif</option>
                    <option value="pasif">Pasif</option>
                </select>
            @elseif($k === 'nama_unit')
                <select name="items[__I__][nama_unit]" required class="sel-nama" data-ph="Pilih unit / mitra / stokis">{!! $opsiNamaHtml('') !!}</select>
                <input type="hidden" name="items[__I__][no_cab]"  class="h-nocab">
                <input type="hidden" name="items[__I__][alamat]"  class="h-alamat">
                <input type="hidden" name="items[__I__][telepon]" class="h-telepon">
            @elseif($k === 'ekspedisi')
                <select name="items[__I__][ekspedisi]" class="sel-eksp" data-ph="Pilih ekspedisi">{!! $opsiEkspHtml('') !!}</select>
            @elseif($k === 'service_pengiriman')
                <select name="items[__I__][service_pengiriman]" class="sel-svc" data-ph="Pilih service" data-selected=""></select>
            @else
                @php $kb = $kolomBiasa[$k]; @endphp
                <input type="{{ $kb['type'] }}" name="items[__I__][{{ $k }}]"
                       value="{{ $kb['default'] ?? '' }}" placeholder="{{ $kb['ph'] ?? '' }}"
                       {{ !empty($kb['req']) ? 'required' : '' }} {!! isset($kb['min']) ? 'min="' . $kb['min'] . '"' : '' !!}
                       class="{{ $kb['w'] }} {{ $cell }}">
            @endif
            </td>
        @endforeach
        <td class="px-2 py-2 text-center">
            <button type="button" title="Hapus baris" aria-label="Hapus baris"
                    class="btn-hapus p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">{!! $trash !!}</button>
        </td>
    </tr>
</template>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 34px; border: 1px solid #d1d5db; border-radius: 0.5rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px; font-size: 0.875rem; padding-left: 10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px; }
    .select2-dropdown { border-radius: 0.75rem !important; border: 1px solid #e5e7eb !important; overflow: hidden; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #162749; }
    #bodyUnit td .select2-container { min-width: 150px; }
    #bodyUnit td:has(.sel-nama) .select2-container { min-width: 260px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    (function () {
        var $ = window.jQuery;
        var body = document.getElementById('bodyUnit');
        var tpl = document.getElementById('tpl-row').innerHTML;
        var idx = {{ $n }};
        var SERVICE = @json($svcOpsi);

        function nomori() {
            body.querySelectorAll('tr').forEach(function (tr, i) {
                tr.querySelector('.row-no').textContent = i + 1;
            });
        }

        // Isi dropdown Service sesuai Ekspedisi (Diambil Sendiri / kosong = tanpa service)
        function isiService($tr, pilih) {
            var eksp = $tr.find('.sel-eksp').val() || '';
            var $svc = $tr.find('.sel-svc');
            var daftar = SERVICE[eksp] || [];
            pilih = pilih !== undefined ? pilih : ($svc.val() || '');

            $svc.empty().append(new Option('', '', false, false));
            if (pilih && daftar.indexOf(pilih) === -1 && eksp !== '' && eksp !== 'Diambil Sendiri') {
                daftar = daftar.concat([pilih]);   // nilai lama yang tidak ada di daftar tetap tampil
            }
            daftar.forEach(function (v) { $svc.append(new Option(v, v, false, v === pilih)); });

            if (!pilih && daftar.length && eksp !== 'Diambil Sendiri') {
                $svc.val(daftar[0]);               // default: service pertama (REGPACK / REG)
            }
            $svc.prop('disabled', daftar.length === 0).trigger('change.select2');
        }

        function initRow($tr) {
            $tr.find('.sel-nama, .sel-eksp, .sel-svc').each(function () {
                var $s = $(this);
                if ($s.hasClass('select2-hidden-accessible')) return;
                $s.select2({ placeholder: $s.data('ph') || '', allowClear: true, width: '100%' });
            });
            $tr.find('.sel-status').select2({ minimumResultsForSearch: Infinity, width: '100%' });

            isiService($tr, $tr.find('.sel-svc').data('selected') || '');
        }

        // Pilih Nama Unit -> isi status unit & data pendukung otomatis (status tetap bisa diubah)
        $(body).on('select2:select', '.sel-nama', function (e) {
            var $tr = $(this).closest('tr');
            var $o  = $(e.params.data.element);
            var st  = ($o.data('status') || '').toString();
            if (st) { $tr.find('.sel-status').val(st).trigger('change'); }
            $tr.find('.h-nocab').val($o.data('nocab') || '');
            $tr.find('.h-alamat').val($o.data('alamat') || '');
            $tr.find('.h-telepon').val($o.data('telepon') || '');
        });
        $(body).on('select2:clear', '.sel-nama', function () {
            var $tr = $(this).closest('tr');
            $tr.find('.h-nocab, .h-alamat, .h-telepon').val('');
        });
        $(body).on('change', '.sel-eksp', function () {
            isiService($(this).closest('tr'), '');
        });

        $(body).find('tr').each(function () { initRow($(this)); });

        document.getElementById('btn-tambah').addEventListener('click', function () {
            body.insertAdjacentHTML('beforeend', tpl.replace(/__I__/g, idx++));
            nomori();
            initRow($(body).find('tr').last());
        });

        body.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-hapus');
            if (!btn) return;
            if (body.children.length <= 1) { alert('Minimal harus ada 1 baris.'); return; }
            var $tr = $(btn.closest('tr'));
            $tr.find('.select2-hidden-accessible').select2('destroy');
            $tr.remove();
            nomori();
        });
    })();
</script>
@endpush
