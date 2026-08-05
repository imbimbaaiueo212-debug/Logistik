<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Aktual Manual - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { font-family: 'Poppins', sans-serif; }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 15px;
            border: 1px solid #374151;
        }

        th, td {
            border: 1px solid #37415171;
            padding: 4px 6px;
            vertical-align: top;
            text-align: center;
            line-height: 1.3;
        }

        .header1 th, .header2 th {
            background-color: #f1f5f9;
            border-bottom: 1px solid #374151;
            font-weight: 600;
        }

        .accordion-header {
            transition: all 0.3s ease;
        }

        .accordion-header:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

        <div class="max-w-screen-2xl mx-auto px-6 py-6">

            {{-- FLASH --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">
                    {!! session('success') !!}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl">
                    {!! session('error') !!}
                </div>
            @endif

            {{-- HEADER UTAMA --}}
            <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Rekap Aktual Manual</h1>
        <p class="text-gray-600">Data Manual Realisasi yang sudah diproses</p>
    </div>

            <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border flex-wrap">
                <a href="{{ route('import.manual') }}"
                class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                    ← Kembali
                </a>

                <a href="{{ route('import.manual-printed') }}?kategori=Majalah"
                class="px-6 py-3 rounded-3xl font-medium transition-all bg-blue-600 text-white shadow-sm">
                    🔵 Majalah
                </a>
            </div>
        </div>
    </div>

    {{-- ACCORDION PER TANGGAL --}}
    @forelse($groupedData as $tanggal => $rows)
        @php
            $first           = $rows->first();
            $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
            $allPickingDone  = $rows->every(fn($item) => !is_null($item->picking_printed_at));
            $allPrinted      = $rows->every(fn($item) => !is_null($item->printed_at));
            $collapseId      = 'collapse_' . $loop->index;
            $totalOrder      = $rows->count();
        @endphp

        <div class="bg-white shadow-lg border-2 border-gray-800 mb-8 rounded-xl overflow-hidden"
             data-tanggal="{{ $tanggal }}">

            {{-- HEADER (CLICKABLE) --}}
            <button type="button"
                    onclick="toggleContent('{{ $collapseId }}')"
                    class="accordion-header w-full flex justify-between items-center px-6 py-5 bg-gray-100 hover:bg-gray-200 transition text-left">

                <div class="flex items-center gap-4">
                    <span id="icon-{{ $collapseId }}" class="text-2xl font-bold text-gray-600">▼</span>

                    @if($allPrinted)
                        <span class="text-green-600 text-2xl">✅</span>
                        <span class="font-semibold text-green-600">RA SUDAH DICETAK</span>
                    @else
                        <span class="text-red-500 text-2xl">❌</span>
                        <span class="font-semibold text-red-500">RA BELUM DICETAK</span>
                    @endif
                </div>

                <div class="flex-1 text-center px-6">
                    <div>
                        <span class="font-bold text-lg">
                            Rekap Aktual Manual - {{ $first->kategori_order ?? 'Majalah' }}
                            @php
                                // Ambil nama edisi (contoh: M159)
                                $edisi = $first->manualOrder?->product_sku
                                    ?? $first->product_sku
                                    ?? null;

                                // Kalau tidak ada, coba ekstrak dari nama_barang
                                if (!$edisi && !empty($first->nama_barang)) {
                                    // Ambil kata terakhir (biasanya kode edisi)
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
                <table class="w-full">
                    <thead>
                        <tr class="header1">
                            <th rowspan="2" class="col-no">NO</th>
                            <th colspan="3">DETAIL ORDER</th>
                            <th rowspan="2">KATEGORI PESANAN</th>
                            <th rowspan="2" class="col-distribusi">DISTRIBUSI</th>
                            <th rowspan="2" class="col-catatan">CATATAN</th>
                            <th rowspan="2" class="col-picking">PICKING LIST</th>
                        </tr>
                        <tr class="header2">
                            <th class="col-id">ID ORDER</th>
                            <th class="col-unit">NAMA UNIT</th>
                            <th class="col-kategori">GROUP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $item)
                            @php
                                $noCab     = trim($item->billing_last_name ?? $item->manualOrder?->billing_last_name ?? '');
                                $mismatch  = $mismatchMap[$noCab] ?? null;
                                $isMismatch = $mismatch
                                    || str_contains($item->ket ?? '', 'NAMA_MISMATCH')
                                    || str_contains($item->manualOrder?->catatan ?? '', 'NAMA_MISMATCH')
                                    || str_contains($item->manualOrder?->notes ?? '', 'NAMA_MISMATCH');

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
                            @endphp
                            <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                                <td class="font-medium text-center">{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>

                                {{-- NAMA UNIT + MISMATCH --}}
                                <td class="text-left">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-medium">{{ $item->nama_unit ?? '-' }}</span>

                                        @if($isMismatch && $mismatch)
                                            <div class="text-xs font-normal mt-0.5 space-y-0.5">
                                                <div class="text-orange-700">
                                                    <span class="text-gray-500">Excel:</span>
                                                    <span class="font-medium">{{ $mismatch['nama_excel'] }}</span>
                                                </div>
                                                <div class="text-emerald-700">
                                                    <span class="text-gray-500">Kemitraan:</span>
                                                    <span class="font-medium">{{ $mismatch['nama_master'] }}</span>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                                ⚠️ Mismatch
                                            </span>
                                        @elseif($isMismatch)
                                            <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                                ⚠️ Mismatch
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- GROUP --}}
                                <td class="text-center">
                                    @if($grup)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $grupClass }}">
                                            Group {{ $grup }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="text-center text-sm">
                                    <div class="font-medium">
                                        {{ $item->nama_barang ?? $item->kategori_order ?? 'Majalah' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div>{{ $item->pengiriman ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->service_pengiriman ?? '-' }}</div>
                                </td>
                                {{-- CATATAN (bisa diedit) --}}
                                    <td class="text-center text-xs relative" style="min-width: 180px;">
    @php
        $raw = $item->manualOrder?->catatan 
            ?? $item->manualOrder?->notes 
            ?? $item->ket 
            ?? '';

        // === Bersihkan catatan sistem ===
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Skip baris sistem
            if (preg_match('/^CP\s*:/i', $line)) continue;
            if (preg_match('/NAMA_MISMATCH/i', $line)) continue;
            if (preg_match('/Di\s+proses\s+bulk\s+pada/i', $line)) continue;
            if (preg_match('/^[\|\s\-]+$/', $line)) continue;

            // Jika ada "CP:" di tengah baris, potong dari situ
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

    <div class="catatan-display group relative" data-id="{{ $item->id }}">
        <div class="flex items-center justify-center gap-1">
            <span class="catatan-text inline-block {{ $display !== '' ? 'bg-gray-100 px-3 py-1 rounded-md' : 'text-gray-400' }}">
                {{ $display !== '' ? strtoupper($display) : '-' }}
            </span>
            <button type="button"
                    onclick="editCatatan(this)"
                    class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-600 hover:text-blue-800 p-1"
                    title="Edit catatan">
                <i class="fa-solid fa-pen-to-square text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Form edit - hanya isi catatan yang sudah bersih --}}
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

                                <td class="text-center">
                                    <button type="button"
                                            onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                                            class="action-btn text-2xl {{ $item->picking_printed_at ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700' }}">
                                        @if($item->picking_printed_at)
                                            <i class="fa-solid fa-file-pdf"></i>
                                        @else
                                            <i class="fa-solid fa-print"></i>
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- TOMBOL PER TANGGAL --}}
                @if($allPickingDone)
                    <div class="bg-gray-50 border-t p-4 flex flex-wrap gap-3 justify-end">
                        <button type="button"
                                onclick="printPerDate('{{ $tanggal }}', 'prising')"
                                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-file-pdf"></i> Cetak RA Prising
                        </button>

                        <button type="button"
                                onclick="printPerDate('{{ $tanggal }}', 'pemesanan')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-list-check"></i> RA PICKING
                        </button>

                        <button type="button"
                                onclick="printPerDate('{{ $tanggal }}', 'qc')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-clipboard-check"></i> RA QC
                        </button>

                        <button type="button"
                                onclick="printPerDate('{{ $tanggal }}', 'packing')"
                                class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fas fa-box"></i> RA PACKING
                        </button>

                        <button type="button"
                                onclick="printPerDate('{{ $tanggal }}', 'ekspedisi')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-truck"></i> RA EKSPEDISI
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
                <i class="fa-solid fa-print"></i> Cetak Sekarang
            </button>
        </div>
    </div>
</div>

<script>
function toggleContent(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.textContent = '▼';
        localStorage.setItem('openAccordionManual', id);
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
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
    // atau: window.open(`/import/manual-printed/picking-pdf/${id}`, '_blank');

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
            icon.textContent = '▼';
        }
    }
});
function editCatatan(btn) {
    const cell = btn.closest('td');
    const display = cell.querySelector('.catatan-display');
    const edit = cell.querySelector('.catatan-edit');

    display.classList.add('hidden');
    edit.classList.remove('hidden');

    // Focus ke textarea
    const textarea = edit.querySelector('textarea');
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function cancelEditCatatan(btn) {
    const cell = btn.closest('td');
    const display = cell.querySelector('.catatan-display');
    const edit = cell.querySelector('.catatan-edit');

    edit.classList.add('hidden');
    display.classList.remove('hidden');
}

function saveCatatan(btn, id) {
    const cell = btn.closest('td');
    const textarea = cell.querySelector('textarea');
    const catatan = textarea.value.trim();

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

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
            // Update tampilan
            const textSpan = cell.querySelector('.catatan-text');
            const cleaned = cleanCatatanDisplay(catatan);

            if (cleaned) {
                textSpan.className = 'catatan-text inline-block bg-gray-100 px-3 py-1 rounded-md';
                textSpan.textContent = cleaned.toUpperCase();
            } else {
                textSpan.className = 'catatan-text inline-block text-gray-400';
                textSpan.textContent = '-';
            }

            // Tutup mode edit
            cancelEditCatatan(btn);

            // Optional: toast sederhana
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

// Helper: bersihkan catatan untuk tampilan (sama logic PHP)
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
    // Toast sederhana
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg z-50 text-sm font-medium';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}
</script>
</body>
</html>