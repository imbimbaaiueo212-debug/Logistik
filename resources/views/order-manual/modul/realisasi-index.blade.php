<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Aktual Manual Modul - biMBA AIUEO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; width: 100%; font-size: 14px; border: 1px solid #374151; }
        th, td { border: 1px solid #37415171; padding: 6px 8px; vertical-align: top; text-align: center; line-height: 1.3; }
        .header1 th, .header2 th { background-color: #f1f5f9; font-weight: 600; }
        .accordion-header:hover { background-color: #f1f5f9; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Rekap Aktual Manual Modul</h1>
            <p class="text-gray-600">Data Realisasi Modul yang sudah diproses</p>
        </div>

        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border flex-wrap">
            <a href="{{ route('order-manual-modul.manual') }}"
               class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">kembali</a>

            <a href="{{ route('order-manual-modul.realisasi') }}"
               class="px-5 py-3 rounded-3xl font-medium {{ !request('kategori') ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' }}">Semua</a>
            <a href="{{ route('order-manual-modul.realisasi', ['kategori' => 'Modul']) }}"
               class="px-5 py-3 rounded-3xl font-medium {{ request('kategori') === 'Modul' ? 'bg-green-600 text-white' : 'hover:bg-gray-100' }}">🟢 Modul</a>
            <a href="{{ route('order-manual-modul.realisasi', ['kategori' => 'Majalah']) }}"
               class="px-5 py-3 rounded-3xl font-medium {{ request('kategori') === 'Majalah' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' }}">🔵 Majalah</a>
            <a href="{{ route('order-manual-modul.realisasi', ['kategori' => 'Sertifikat']) }}"
               class="px-5 py-3 rounded-3xl font-medium {{ request('kategori') === 'Sertifikat' ? 'bg-red-600 text-white' : 'hover:bg-gray-100' }}">🔴 Sertifikat</a>
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

        <div class="bg-white shadow-lg border-2 border-gray-800 mb-8 rounded-xl overflow-hidden"
             data-tanggal="{{ $tanggal }}">

            <button type="button" onclick="toggleContent('{{ $collapseId }}')"
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
                        <span class="font-bold text-lg">Rekap Aktual Manual Modul</span>
                        <span class="text-indigo-600 font-semibold ml-2">{{ $first->rekap_number ?? '#0001' }}</span>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Total Order : <span class="font-bold text-blue-600">{{ $totalOrder }}</span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal Order</div>
                    <div class="font-semibold text-gray-800">{{ $tanggalFormatted }}</div>
                </div>
            </button>

            <div id="{{ $collapseId }}" class="accordion-content">
                <table class="w-full">
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
                        <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                            <td class="font-medium">{{ $loop->iteration }}</td>
                            <td class="font-medium text-indigo-700">{{ $item->no_pl ?? '-' }}</td>
                            <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                            <td>
                                <span class="font-medium">{{ $item->kategori_order ?? '-' }}</span>
                                <div class="text-xs text-gray-500">{{ $item->nama_barang ?? '' }}</div>
                            </td>
                            <td>
                                <div>{{ $item->status_kirim ?? $item->ekspedisi ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->service_pengiriman ?? '-' }}</div>
                            </td>
                            <td class="text-xs">
                                @php
                                    $catatan = $item->catatan ?? $item->order_catatan ?? '';
                                    $display = preg_replace('/^Di proses bulk pada .*?: /i', '', trim($catatan));
                                @endphp
                                @if($display)
                                    <span class="inline-block bg-gray-100 px-2 py-1 rounded">{{ strtoupper(\Illuminate\Support\Str::limit($display, 40)) }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                                        class="text-2xl {{ (($item->picking_printed_at ?? null) || ($item->picking_printed_at_p ?? null)) ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700' }}">
                                    @if(($item->picking_printed_at ?? null) || ($item->picking_printed_at_p ?? null))
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

                @if($allPickingDone)
                <div class="bg-gray-50 border-t p-4 flex flex-wrap gap-3 justify-end">
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'prising')"
                        class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-file-pdf"></i> Cetak RA Prising
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'pemesanan')"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-list-check"></i> RA PICKING
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'qc')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-clipboard-check"></i> RA QC
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'packing')"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fas fa-box"></i> RA PACKING
                    </button>
                    <button type="button" onclick="printPerDate('{{ $tanggal }}', 'ekspedisi')"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-truck"></i> RA EKSPEDISI
                    </button>
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl shadow p-16 text-center text-gray-500">
            Belum ada data Realisasi Manual Modul.
        </div>
    @endforelse
</div>

{{-- Modal Picking --}}
<div id="pickingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-xl font-semibold mb-2">Print Picking List</h3>
        <p class="text-gray-600 mb-6" id="modalMessage"></p>
        <div class="flex gap-3">
            <button type="button" onclick="closeModal()" class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
            <button type="button" onclick="confirmPrintPicking()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Sekarang
            </button>
        </div>
    </div>
</div>

<script>
function toggleContent(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (!content || !icon) return;

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.textContent = '▼';
        localStorage.setItem('openAccordionModul', id);
    } else {
        content.style.display = 'none';
        icon.textContent = '▶';
        localStorage.removeItem('openAccordionModul');
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
            url = `{{ route('order-manual-modul.realisasi.print-prising') }}?ids=${ids}&mark_printed=1`;
            break;
        case "pemesanan":
            url = `{{ route('order-manual-modul.realisasi.print-pemesanan') }}?ids=${ids}`;
            break;
        case "qc":
            url = `{{ route('order-manual-modul.realisasi.print-qc') }}?ids=${ids}`;
            break;
        case "packing":
            url = `{{ route('order-manual-modul.realisasi.print-packing') }}?ids=${ids}`;
            break;
        case "ekspedisi":
            url = `{{ route('order-manual-modul.realisasi.print-ekspedisi') }}?ids=${ids}`;
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

    window.open(`{{ url('/order-manual-modul/realisasi/picking-list') }}/${id}`, '_blank');
    closeModal();
    setTimeout(() => location.reload(), 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-content').forEach(el => {
        el.style.display = 'none';
    });

    const openedId = localStorage.getItem('openAccordionModul');
    if (openedId) {
        const content = document.getElementById(openedId);
        const icon = document.getElementById('icon-' + openedId);
        if (content && icon) {
            content.style.display = 'block';
            icon.textContent = '▼';
        }
    }
});
</script>
</body>
</html>