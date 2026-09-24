@extends('layouts.panel')

@section('title', 'Rekap Aktual Manual Sertifikat')

@push('styles')
<style>
    .rekap-table { border-collapse: collapse; width: 100%; font-size: 13.5px; border: 1px solid #0F1B331A; }
    .rekap-table th, .rekap-table td {
        border: 1px solid #0F1B331A; padding: 8px 10px; vertical-align: top;
        text-align: center; line-height: 1.35;
    }
    .rekap-table .header1 th, .rekap-table .header2 th { background-color: #F8F9FB; font-weight: 600; }
    .accordion-header:hover { background-color: #F8F9FB; }
</style>
@endpush

@section('content')

    @include('partials.flash')

    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Rekap Aktual Manual Sertifikat</h1>
            <p class="mt-1 text-sm text-navy-950/55">Data Realisasi Sertifikat yang sudah diproses</p>
        </div>

        <div class="flex items-center gap-2 bg-white rounded-2xl p-1.5 shadow-card border border-navy-950/10 flex-wrap">
            <a href="{{ route('order-manual-sertifikat.manual') }}"
               class="inline-flex items-center gap-2 bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>

            <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold bg-rust-600 text-white text-sm">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                Sertifikat
            </span>
        </div>
    </div>

    @forelse($groupedData ?? [] as $tanggal => $rows)
        @php
            $first = $rows->first();
            $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
            $allPickingDone = $rows->every(fn($item) => !is_null($item->picking_printed_at ?? null) || !is_null($item->picking_printed_at_p ?? null));
            $allPrinted     = $rows->every(fn($item) => !is_null($item->printed_at ?? null));
            $collapseId     = 'collapse_' . $loop->index;
            $totalOrder     = $rows->count();
        @endphp

        <div class="bg-white shadow-card border border-navy-950/10 mb-6 rounded-2xl overflow-hidden"
             data-tanggal="{{ $tanggal }}">

            <button type="button" onclick="toggleContent('{{ $collapseId }}')"
                    class="accordion-header w-full flex justify-between items-center px-6 py-5 bg-navy-950/[0.03] hover:bg-navy-950/[0.06] transition-colors text-left">

                <div class="flex items-center gap-4">
                    <svg id="icon-{{ $collapseId }}" class="w-5 h-5 text-navy-950/50 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                    @if($allPrinted)
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold text-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
                            RA SUDAH DICETAK
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-rust-600 font-semibold text-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                            RA BELUM DICETAK
                        </span>
                    @endif
                </div>

                <div class="flex-1 text-center px-6">
                    <div>
                        <span class="font-bold text-lg text-navy-950">Rekap Aktual Manual Sertifikat</span>
                        <span class="text-navy-700 font-semibold ml-2">{{ $first->rekap_number ?? '#0001' }}</span>
                    </div>
                    <div class="text-sm text-navy-950/50 mt-1">
                        Total Order : <span class="font-bold text-navy-700">{{ $totalOrder }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-navy-950/50">Tanggal Order</div>
                    <div class="font-semibold text-navy-950">{{ $tanggalFormatted }}</div>
                </div>
            </button>

            <div id="{{ $collapseId }}" class="accordion-content">
                <table class="rekap-table">
                    <thead>
                        <tr class="header1">
                            <th rowspan="2">NO</th>
                            <th colspan="3">DETAIL ORDER</th>
                            <th rowspan="2">DISTRIBUSI</th>
                            <th rowspan="2">CATATAN</th>
                            <th rowspan="2">PICKING LIST</th>
                        </tr>
                        <tr class="header2">
                            <th>ID ORDER / NO PL</th>
                            <th>NAMA UNIT</th>
                            <th>KATEGORI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $item)
                        <tr class="hover:bg-navy-950/[0.02]" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                            <td class="font-medium">{{ $loop->iteration }}</td>
                            <td class="font-medium text-navy-700">{{ $item->no_pl ?? '-' }}</td>
                            <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                            <td>
                                <span class="font-medium">{{ $item->kategori_order ?? '-' }}</span>
                                <div class="text-xs text-navy-950/50">{{ $item->nama_barang ?? '' }}</div>
                            </td>
                            <td>
                                <div>{{ $item->status_kirim ?? $item->ekspedisi ?? '-' }}</div>
                                <div class="text-xs text-navy-950/50">{{ $item->service_pengiriman ?? '-' }}</div>
                            </td>
                            <td class="text-xs">
                                @php
                                    $catatan = $item->catatan ?? $item->order_catatan ?? '';
                                    $display = preg_replace('/^Di proses bulk pada .*?: /i', '', trim($catatan));
                                @endphp
                                @if($display)
                                    <span class="inline-block bg-navy-950/5 px-2 py-1 rounded">{{ strtoupper(\Illuminate\Support\Str::limit($display, 40)) }}</span>
                                @else
                                    <span class="text-navy-950/30">-</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                                        class="inline-flex {{ (($item->picking_printed_at ?? null) || ($item->picking_printed_at_p ?? null)) ? 'text-purple-600' : 'text-navy-700 hover:text-rust-600' }} transition-colors">
                                    @if(($item->picking_printed_at ?? null) || ($item->picking_printed_at_p ?? null))
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h1M9 12h1M14 12h1M14 15h1"/></svg>
                                    @else
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    @endif
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($allPickingDone)
                <div class="bg-navy-950/[0.02] border-t border-navy-950/10 p-4 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'prising')"
                        class="inline-flex items-center gap-2 bg-rust-600 hover:bg-rust-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                        Cetak RA Prising
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'pemesanan')"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        RA Picking
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'qc')"
                        class="inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
                        RA QC
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'packing')"
                        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                        RA Packing
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'ekspedisi')"
                        class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5v8h1"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                        RA Ekspedisi
                    </button>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-card p-16 text-center text-navy-950/40">
            Belum ada data Realisasi Manual Sertifikat.
        </div>
    @endforelse

    {{-- Modal Picking --}}
    <div id="pickingModal" class="hidden fixed inset-0 bg-navy-950/60 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h3 class="text-xl font-semibold text-navy-950 mb-2">Print Picking List</h3>
            <p class="text-navy-950/60 mb-6" id="modalMessage"></p>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 border border-navy-950/10 rounded-xl hover:bg-navy-950/5 font-medium text-navy-950/70 transition-colors">Batal</button>
                <button type="button" onclick="confirmPrintPicking()" class="flex-1 py-3 bg-navy-700 hover:bg-navy-800 text-white rounded-xl font-medium flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Cetak Sekarang
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function toggleContent(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (!content || !icon) return;

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(0deg)';
        localStorage.setItem('openAccordionSertifikat', id);
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(-90deg)';
        localStorage.removeItem('openAccordionSertifikat');
    }
}

function printPerDate(tanggal, type) {
    const container = document.querySelector(`[data-tanggal="${tanggal}"]`);
    if (!container) return;

    const ids = Array.from(container.querySelectorAll("tr[data-id]"))
        .map(r => r.dataset.id)
        .filter(Boolean)
        .join(",");

    if (!ids) {
        alert('Tidak ada data untuk dicetak.');
        return;
    }

    let url = "";
    switch (type) {
        case "prising":
            url = `{{ route('order-manual-sertifikat.realisasi.print-prising') }}?ids=${ids}&mark_printed=1`;
            break;
        case "pemesanan":
            url = `{{ route('order-manual-sertifikat.realisasi.print-pemesanan') }}?ids=${ids}`;
            break;
        case "qc":
            url = `{{ route('order-manual-sertifikat.realisasi.print-qc') }}?ids=${ids}`;
            break;
        case "packing":
            url = `{{ route('order-manual-sertifikat.realisasi.print-packing') }}?ids=${ids}`;
            break;
        case "ekspedisi":
            url = `{{ route('order-manual-sertifikat.realisasi.print-ekspedisi') }}?ids=${ids}`;
            break;
        default:
            alert('Tipe cetak tidak dikenal.');
            return;
    }

    window.open(url, "_blank");
    setTimeout(() => location.reload(), 1200);
}

let currentButton = null;

function printPickingList(btn, id, noPL) {
    currentButton = btn;
    document.getElementById('modalMessage').innerHTML =
        `Cetak Picking List untuk No. PL <strong>${noPL}</strong>?`;
    document.getElementById('pickingModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('pickingModal').classList.add('hidden');
}

function confirmPrintPicking() {
    if (!currentButton) return;
    const row = currentButton.closest('tr');
    const id = row ? row.dataset.id : null;
    if (!id) return;

    window.open(`{{ url('/order-manual-sertifikat/realisasi/picking-list') }}/${id}`, '_blank');
    closeModal();
    setTimeout(() => location.reload(), 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-content').forEach(el => {
        el.style.display = 'none';
    });

    const openedId = localStorage.getItem('openAccordionSertifikat');
    if (openedId) {
        const content = document.getElementById(openedId);
        const icon = document.getElementById('icon-' + openedId);
        if (content && icon) {
            content.style.display = 'block';
            icon.style.transform = 'rotate(0deg)';
        }
    }
});
</script>
@endpush