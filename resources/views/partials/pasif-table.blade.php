{{--
    Tabel data Unit Pasif (dipakai halaman daftar dan detail).
    Param  : $kolom, $rows (collection / paginator)
    Opsi   : $detailRoute, $editRoute, $destroyRoute (nama route, param id), $noPsUrl, $kosong, $kosongLink = ['url'=>, 'label'=>]
    Kolom  : key, label, align, class, clip, type (badge|pill|number|input), value (closure), suffix, pillClass, decimals
             key 'no' = nomor urut otomatis
--}}
@php
    $noPsUrl      = $noPsUrl ?? url('/import/pasif');
    $kosong       = $kosong ?? 'Belum ada data.';
    $kosongLink   = $kosongLink ?? null;
    $detailRoute  = $detailRoute ?? null;
    $editRoute    = $editRoute ?? null;
    $destroyRoute = $destroyRoute ?? null;
    $adaAksi      = $detailRoute || $editRoute || $destroyRoute;
    $adaInput     = collect($kolom)->contains('type', 'input');
    $paginated    = method_exists($rows, 'hasPages');
@endphp

@push('styles')
<style>
    .data-table-wrap { overflow: auto; max-height: calc(100vh - 250px); }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 20;
        background: #162749; color: #fff; text-align: left;
        padding: 12px 16px; font-weight: 600; white-space: nowrap;
    }
    .data-table tbody td {
        padding: 10px 16px; border-bottom: 1px solid #eef0f5;
        background: #fff; white-space: nowrap;
    }
    .data-table tbody tr:hover td { background: #f7f8fb; }
    .data-table th:first-child, .data-table td:first-child { position: sticky; left: 0; z-index: 10; }
    .data-table thead th:first-child { z-index: 30; }
    .data-table th.col-aksi, .data-table td.col-aksi { position: sticky; right: 0; z-index: 10; }
    .data-table thead th.col-aksi { z-index: 30; }
    .cell-clip { display: block; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pager nav p { display: none; }

    .btn-save-nops { padding: 6px; border-radius: 8px; background: rgba(40,68,127,.10); color: #28447F; transition: background-color .15s; }
    .btn-save-nops:hover { background: rgba(40,68,127,.18); }
    .btn-save-nops:disabled { opacity: .5; cursor: wait; }
    .btn-save-nops.is-ok  { background: #dcfce7; color: #15803d; }
    .btn-save-nops.is-err { background: #fee2e2; color: #b91c1c; }
</style>
@endpush

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($kolom as $k)
                        <th style="text-align: {{ $k['align'] ?? 'left' }}">{{ $k['label'] }}</th>
                    @endforeach
                    @if($adaAksi)
                        <th class="col-aksi" style="text-align: center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php $no = $paginated ? $rows->firstItem() + $loop->index : $loop->iteration; @endphp
                    <tr>
                        @foreach($kolom as $k)
                            @php
                                $tipe = $k['type'] ?? null;
                                if (($k['key'] ?? null) === 'no') {
                                    $v = $no;
                                } elseif (isset($k['value'])) {
                                    $v = $k['value']($row);
                                } else {
                                    $v = data_get($row, $k['key']);
                                }
                            @endphp
                            <td class="{{ $k['class'] ?? '' }}" style="text-align: {{ $k['align'] ?? 'left' }}">
                                @if($tipe === 'badge')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $v === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $v === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @elseif($tipe === 'pill')
                                    @if(filled($v))
                                        <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $k['pillClass'] ?? 'bg-[#28447F]/10 text-[#28447F]' }}">{{ $v }}{{ $k['suffix'] ?? '' }}</span>
                                    @else
                                        -
                                    @endif
                                @elseif($tipe === 'number')
                                    {{ number_format((float) $v, $k['decimals'] ?? 0, '.', ',') }}
                                @elseif($tipe === 'input')
                                    <div class="nops-wrap inline-flex items-center gap-1.5">
                                        <input type="text" value="{{ $v }}" data-id="{{ $row->id }}"
                                               placeholder="Isi No PS" aria-label="No PS {{ $row->edisi ?? '' }}"
                                               class="no-ps-input w-32 border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]">
                                        <button type="button" title="Simpan No PS" aria-label="Simpan No PS" class="btn-save-nops">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11l3 3v13H5Z"/><path d="M8 4v5h7V4M8 20v-6h8v6"/></svg>
                                        </button>
                                    </div>
                                @elseif(!empty($k['clip']))
                                    <span class="cell-clip" title="{{ $v }}">{{ filled($v) ? $v : '-' }}</span>
                                @else
                                    {{ filled($v) ? $v : '-' }}
                                @endif
                            </td>
                        @endforeach

                        @if($adaAksi)
                            <td class="col-aksi" style="text-align: center">
                                <div class="inline-flex items-center gap-1">
                                    @if($detailRoute)
                                        <a href="{{ route($detailRoute, $row->id) }}" title="Detail" aria-label="Detail"
                                           class="p-1.5 rounded-lg text-[#28447F] hover:bg-[#28447F]/10 transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @endif
                                    @if($editRoute)
                                        <a href="{{ route($editRoute, $row->id) }}" title="Edit" aria-label="Edit"
                                           class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>
                                        </a>
                                    @endif
                                    @if($destroyRoute)
                                        <form action="{{ route($destroyRoute, $row->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" aria-label="Hapus"
                                                    class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($kolom) + ($adaAksi ? 1 : 0) }}" style="text-align: center" class="!py-14 text-gray-400">
                            {{ $kosong }}
                            @if($kosongLink)
                                <a href="{{ $kosongLink['url'] }}" class="text-[#E85D2A] hover:underline ml-1">{{ $kosongLink['label'] }}</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($paginated && $rows->hasPages())
    <div class="pager mt-4">
        {{ $rows->links('pagination::tailwind') }}
    </div>
@endif

@if($adaInput)
@push('scripts')
<script>
    (function () {
        var baseUrl = "{{ $noPsUrl }}";
        var csrf = "{{ csrf_token() }}";
        var ICON_SAVE = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11l3 3v13H5Z"/><path d="M8 4v5h7V4M8 20v-6h8v6"/></svg>';
        var ICON_OK   = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4.5 4.5L19 7"/></svg>';
        var ICON_ERR  = '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>';

        function pulihkan(btn) {
            btn.classList.remove('is-ok', 'is-err');
            btn.innerHTML = ICON_SAVE;
            btn.title = 'Simpan No PS';
            btn.disabled = false;
        }

        function tandai(btn, ok, pesan) {
            btn.classList.add(ok ? 'is-ok' : 'is-err');
            btn.innerHTML = ok ? ICON_OK : ICON_ERR;
            btn.title = pesan;
            setTimeout(function () { pulihkan(btn); }, 1800);
        }

        function simpan(btn) {
            var input = btn.closest('.nops-wrap').querySelector('.no-ps-input');
            btn.disabled = true;

            fetch(baseUrl + '/' + input.dataset.id + '/no-ps', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ no_ps: input.value.trim() })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.disabled = false;
                tandai(btn, !!data.success, data.success ? 'Tersimpan' : (data.message || 'Gagal menyimpan'));
            })
            .catch(function () {
                btn.disabled = false;
                tandai(btn, false, 'Terjadi kesalahan');
            });
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-save-nops');
            if (btn) simpan(btn);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target.classList.contains('no-ps-input')) {
                e.preventDefault();
                simpan(e.target.closest('.nops-wrap').querySelector('.btn-save-nops'));
            }
        });
    })();
</script>
@endpush
@endif
