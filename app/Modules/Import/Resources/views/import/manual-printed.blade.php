@extends('layouts.panel')

@section('title', 'Rekap Aktual Manual - Majalah')

@push('styles')
<style>
    .ram-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 15px;
        border: 1px solid #374151;
    }
    .ram-table th, .ram-table td {
        border: 1px solid #37415171;
        padding: 4px 6px;
        vertical-align: top;
        text-align: center;
        line-height: 1.3;
    }
    .ram-table .header1 th, .ram-table .header2 th {
        background-color: #f1f5f9;
        border-bottom: 1px solid #374151;
        font-weight: 600;
    }
    .accordion-header { transition: all 0.3s ease; }
    .accordion-header:hover { background-color: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="max-w-screen-2xl mx-auto px-6 py-6">

    @include('partials.flash')

    {{-- HEADER UTAMA --}}
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Rekap Aktual Majalah</h1>
            <p class="text-gray-600">Data Manual Realisasi yang sudah diproses</p>
        </div>

        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border flex-wrap">
            <a href="{{ route('import.manual') }}"
               class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>

            <a href="{{ route('import.manual-printed') }}?kategori=Majalah"
               class="px-6 py-3 rounded-3xl font-medium transition-all bg-blue-600 text-white shadow-sm flex items-center gap-2">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                Majalah
            </a>
        </div>
    </div>

    {{-- ACCORDION PER TANGGAL --}}
    @forelse($groupedData as $tanggal => $rows)
        @php
            $first            = $rows->first();
            $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
            $allPickingDone   = $rows->every(fn($item) => !is_null($item->picking_printed_at));
            $allPrinted       = $rows->every(fn($item) => !is_null($item->printed_at));
            $collapseId       = 'collapse_' . $loop->index;
            $totalOrder       = $rows->count();
        @endphp

        <div class="bg-white shadow-lg border-2 border-gray-800 mb-8 rounded-xl overflow-hidden"
             data-tanggal="{{ $tanggal }}">

            {{-- HEADER (CLICKABLE) --}}
            <button type="button"
                    onclick="toggleContent('{{ $collapseId }}')"
                    class="accordion-header w-full flex justify-between items-center px-6 py-5 bg-gray-100 hover:bg-gray-200 transition text-left">

                <div class="flex items-center gap-4">
                    <svg id="icon-{{ $collapseId }}" class="w-5 h-5 text-gray-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>

                    @if($allPrinted)
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-green-600">RA SUDAH DICETAK</span>
                    @else
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold text-red-500">RA BELUM DICETAK</span>
                    @endif
                </div>

                <div class="flex-1 text-center px-6">
                    <div>
                        <span class="font-bold text-lg">
                            Rekap Aktual Manual - {{ $first->kategori_order ?? 'Majalah' }}
                            @php
                                $edisi = $first->manualOrder?->product_sku
                                    ?? $first->product_sku
                                    ?? null;

                                if (!$edisi && !empty($first->nama_barang)) {
                                    $parts = explode(' ', trim($first->nama_barang));
                                    $edisi = end($parts);
                                }
                            @endphp

                            @if($edisi)
                                ({{ $edisi }})
                            @endif
                        </span>
                        <span class="text-indigo-600 font-semibold ml-2">
                            {{ $first->rekap_number ?? '#M0001' }}
                            @if($first->no_ps)
                                / {{ $first->no_ps }}
                            @endif
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Total Order :
                        <span class="font-bold text-blue-600">{{ $totalOrder }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal Order</div>
                    <div class="font-semibold text-gray-800">{{ $tanggalFormatted }}</div>
                </div>
            </button>

            {{-- CONTENT --}}
            <div id="{{ $collapseId }}" class="accordion-content">
                <div class="overflow-x-auto">
                <table class="ram-table">
                    <thead>
                        <tr class="header1">
                            <th rowspan="2">NO</th>
                            <th colspan="3">DETAIL ORDER</th>
                            <th rowspan="2">KATEGORI PESANAN</th>
                            <th rowspan="2">DISTRIBUSI</th>
                            <th rowspan="2">CATATAN</th>
                            <th rowspan="2">PICKING LIST</th>
                        </tr>
                        <tr class="header2">
                            <th>ID ORDER</th>
                            <th>NAMA UNIT</th>
                            <th>GROUP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $item)
                            @php
                                $grup = strtoupper(trim($item->grup ?? $item->manualOrder?->grup ?? ''));
                                $grupClass = match($grup) {
                                    'A'     => 'bg-blue-100 text-blue-700 border border-blue-200',
                                    'B'     => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    'C'     => 'bg-purple-100 text-purple-700 border border-purple-200',
                                    'D'     => 'bg-amber-100 text-amber-700 border border-amber-200',
                                    'E'     => 'bg-rose-100 text-rose-700 border border-rose-200',
                                    'F'     => 'bg-cyan-100 text-cyan-700 border border-cyan-200',
                                    default => 'bg-gray-100 text-gray-600 border border-gray-200',
                                };

                                $raw = $item->manualOrder?->catatan
                                    ?? $item->manualOrder?->notes
                                    ?? $item->ket
                                    ?? '';

                                $lines = preg_split('/\r\n|\r|\n/', $raw);
                                $cleanLines = [];

                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if ($line === '') continue;
                                    if (preg_match('/^CP\s*:/i', $line)) continue;
                                    if (preg_match('/NAMA_MISMATCH/i', $line)) continue;
                                    if (preg_match('/Di\s+proses\s+bulk\s+pada/i', $line)) continue;
                                    if (preg_match('/^[\|\s\-]+$/', $line)) continue;

                                    if (preg_match('/^(.*?)\s*\|?\s*CP\s*:.*$/i', $line, $m)) {
                                        $line = trim($m[1]);
                                        if ($line === '' || preg_match('/^[\|\s\-]+$/', $line)) continue;
                                    }

                                    $cleanLines[] = $line;
                                }

                                $display = implode(' ', $cleanLines);
                                $display = trim(preg_replace('/\s+/', ' ', $display));
                                $display = trim(preg_replace('/\s*\|\s*/', ' ', $display));
                            @endphp
                            <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                                <td class="font-medium">{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>

                                <td class="text-left">
                                    <span class="font-medium">{{ $item->nama_unit ?? '-' }}</span>
                                </td>

                                <td>
                                    @if($grup)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $grupClass }}">
                                            Group {{ $grup }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="text-sm">
                                    <div class="font-medium">
                                        {{ $item->nama_barang ?? $item->kategori_order ?? 'Majalah' }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $item->pengiriman ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->service_pengiriman ?? '-' }}</div>
                                </td>

                                {{-- CATATAN (bisa diedit) --}}
                                <td class="text-xs relative" style="min-width: 180px;">
                                    <div class="catatan-display group relative" data-id="{{ $item->id }}">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="catatan-text inline-block {{ $display !== '' ? 'bg-gray-100 px-3 py-1 rounded-md' : 'text-gray-400' }}">
                                                {{ $display !== '' ? strtoupper($display) : '-' }}
                                            </span>
                                            <button type="button"
                                                    onclick="editCatatan(this)"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-600 hover:text-blue-800 p-1"
                                                    title="Edit catatan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="catatan-edit hidden mt-1" data-id="{{ $item->id }}">
                                        <textarea rows="2"
                                                  class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                                  placeholder="Tulis catatan...">{{ $display }}</textarea>
                                        <div class="flex gap-1 mt-1.5 justify-center">
                                            <button type="button"
                                                    onclick="saveCatatan(this, {{ $item->id }})"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded-lg font-medium">
                                                Simpan
                                            </button>
                                            <button type="button"
                                                    onclick="cancelEditCatatan(this)"
                                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs px-3 py-1 rounded-lg">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <button type="button"
                                            onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                                            class="action-btn text-2xl {{ $item->picking_printed_at ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700' }}">
                                        @if($item->picking_printed_at)
                                            <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        @else
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                {{-- TOMBOL PER TANGGAL --}}
                @if($allPickingDone)
                    <div class="bg-gray-50 border-t p-4 flex flex-wrap gap-3 justify-end">
                        <button type="button" onclick="printPerDate('{{ $tanggal }}', 'prising')"
                                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>
                            Cetak RA Prising
                        </button>
                        <button type="button" onclick="printPerDate('{{ $tanggal }}', 'pemesanan')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            RA PICKING
                        </button>
                        <button type="button" onclick="printPerDate('{{ $tanggal }}', 'qc')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            RA QC
                        </button>
                        <button type="button" onclick="printPerDate('{{ $tanggal }}', 'packing')"
                                class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            RA PACKING
                        </button>
                        <button type="button" onclick="printPerDate('{{ $tanggal }}', 'ekspedisi')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16V6a1 1 0 011-1h6a1 1 0 011 1v10m-8 0h8m-8 0a2 2 0 11-4 0m4 0a2 2 0 104 0m4 0a2 2 0 104 0m0 0a2 2 0 10-4 0m4 0h1a1 1 0 001-1v-2.586a1 1 0 00-.293-.707l-2.414-2.414A1 1 0 0016.586 9H16"/></svg>
                            RA DISTRIBUSI
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl shadow p-12 text-center text-gray-500">
            Belum ada data Manual Realisasi.<br>
            Proses bulk di halaman <strong>Manual Pemesanan</strong> terlebih dahulu.
        </div>
    @endforelse
</div>

{{-- Modal Picking List --}}
<div id="pickingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-xl font-semibold mb-2">Print Picking List</h3>
        <p class="text-gray-600 mb-6" id="modalMessage"></p>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal()"
                    class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">
                Batal
            </button>
            <button type="button" onclick="confirmPrintPicking()"
                    class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
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

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.classList.remove('-rotate-90');
        localStorage.setItem('openAccordionManual', id);
    } else {
        content.style.display = 'none';
        icon.classList.add('-rotate-90');
        localStorage.removeItem('openAccordionManual');
    }
}

function printPerDate(tanggal, type) {
    const container = document.querySelector(`[data-tanggal="${tanggal}"]`);
    if (!container) return;

    const ids = Array.from(container.querySelectorAll('tr[data-id]'))
        .map(r => r.dataset.id)
        .join(',');

    let url = '';

    switch (type) {
        case 'prising':
            url = `{{ route('import.manual-printed.pdf') }}?ids=${ids}&mark_printed=true`;
            break;
        case 'pemesanan':
            url = `{{ route('import.manual-print-pemesanan') }}?ids=${ids}`;
            break;
        case 'qc':
            url = `{{ route('import.manual-print-qc') }}?ids=${ids}`;
            break;
        case 'packing':
            url = `{{ route('import.manual-print-packing') }}?ids=${ids}`;
            break;
        case 'ekspedisi':
            url = `{{ route('import.manual-print-ekspedisi') }}?ids=${ids}`;
            break;
    }
    if (url) {
        window.open(url, '_blank');
        setTimeout(() => location.reload(), 1200);
    }
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
    const id = row.dataset.id;

    window.open(`{{ url('/import/manual-printed/picking') }}/${id}`, '_blank');

    closeModal();
    setTimeout(() => location.reload(), 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-content').forEach(el => {
        el.style.display = 'none';
    });

    const openedId = localStorage.getItem('openAccordionManual');
    if (openedId) {
        const content = document.getElementById(openedId);
        const icon = document.getElementById('icon-' + openedId);
        if (content && icon) {
            content.style.display = 'block';
        }
    } else {
        document.querySelectorAll('[id^="icon-collapse_"]').forEach(el => el.classList.add('-rotate-90'));
    }
});

function editCatatan(btn) {
    const cell = btn.closest('td');
    cell.querySelector('.catatan-display').classList.add('hidden');
    const edit = cell.querySelector('.catatan-edit');
    edit.classList.remove('hidden');
    const textarea = edit.querySelector('textarea');
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function cancelEditCatatan(btn) {
    const cell = btn.closest('td');
    cell.querySelector('.catatan-display').classList.remove('hidden');
    cell.querySelector('.catatan-edit').classList.add('hidden');
}

function saveCatatan(btn, id) {
    const cell = btn.closest('td');
    const textarea = cell.querySelector('textarea');
    const catatan = textarea.value.trim();

    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    fetch(`{{ url('/import/manual-printed') }}/${id}/catatan`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ catatan: catatan })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const textSpan = cell.querySelector('.catatan-text');
            const cleaned = cleanCatatanDisplay(catatan);

            if (cleaned) {
                textSpan.className = 'catatan-text inline-block bg-gray-100 px-3 py-1 rounded-md';
                textSpan.textContent = cleaned.toUpperCase();
            } else {
                textSpan.className = 'catatan-text inline-block text-gray-400';
                textSpan.textContent = '-';
            }
            cancelEditCatatan(btn);
            showToast('Catatan berhasil disimpan');
        } else {
            alert(data.message || 'Gagal menyimpan catatan');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Simpan';
    });
}

function cleanCatatanDisplay(text) {
    if (!text) return '';
    let display = text;
    display = display.replace(/^CP:.*$/gmi, '');
    display = display.replace(/^NAMA_MISMATCH.*$/gmi, '');
    display = display.replace(/Di\s+proses\s+bulk\s+pada\s+[\d\/:\s]+[:\s]*/gi, '');
    display = display.replace(/[\r\n]+/g, ' ');
    display = display.replace(/\s*\|\s*/g, ' ');
    display = display.replace(/\s+/g, ' ').trim();
    return display;
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg z-50 text-sm font-medium';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}
</script>
@endpush