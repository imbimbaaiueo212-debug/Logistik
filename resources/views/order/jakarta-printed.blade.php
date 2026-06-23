<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Aktual Detail - Stokis Jakarta</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
    body { font-family: 'Poppins', sans-serif; }
    
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        border: 3px solid #374151;
    }
    
    th, td {
        border: 1px solid #37415171;
        padding: 10px 8px;
        text-align: center;
        vertical-align: middle;
    }

    /* ================== HEADER GROUP & BORDER TEBAL ================== */
    .header1 th, 
    .header2 th {
        background-color: #f1f5f9;
        border-bottom: 3px solid #374151;
        font-weight: 600;
    }

    /* Border luar tabel */
    th:first-child, td:first-child { border-left: 3px solid #374151; }
    th:last-child,  td:last-child  { border-right: 3px solid #374151; }

    /* ================== GARIS TEBAL PEMBATAS GROUP ================== */
    
    /* Setelah NO */
    th:nth-child(1),
    td:nth-child(1) { border-right: 3px solid #374151; }

    /* Setelah WAKTU PRINT RA */
    th:nth-child(2),
    td:nth-child(2) { border-right: 3px solid #374151; }

    /* Setelah DETAIL ORDER (setelah KATEGORI) */
    th:nth-child(3),
    td:nth-child(5) { border-right: 3px solid #374151; }

    /* Setelah PENGIRIMAN & SERVICE (setelah SERVICE) */
    th:nth-child(5),
    th:nth-child(4),
    td:nth-child(7) { border-right: 3px solid #374151; }

    /* Setelah PEMBAYARAN (setelah JUMLAH BAYAR) */
    th:nth-child(9),
    th:nth-child(7),
    td:nth-child(9) { border-right: 3px solid #374151; }

    /* Setelah ESTIMASI PERSIAPAN (setelah HARI) */
    th:nth-child(6),
    td:nth-child(11) { border-right: 3px solid #374151; }

    /* Setelah CATATAN */
    th:nth-child(12),
    td:nth-child(12) { border-right: 3px solid #374151; }

    .main-title {
        font-size: 1.1rem;
        font-weight: 700;
        background-color: #ffffff;
        border-bottom: 3px solid #374151;
    }
    
    tr:hover { 
        background-color: #f8fafc; 
    }
    
    .text-left { text-align: left; }
    .text-right { text-align: right; }
</style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

   <div class="max-w-screen-2xl mx-auto px-6 py-6">
    
    <!-- HEADER UTAMA -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('order.jakarta-aktif') }}" 
               class="bg-gray-700 text-white px-6 py-3 rounded-xl hover:bg-gray-800 flex items-center gap-2">
                ← Kembali
            </a>
        </div>

                <div class="flex items-center gap-3" id="print-all-container">
            @php
                $allPrinted = $data->every(fn($item) => !is_null($item->printed_at));
                $allPickingPrinted = $data->every(fn($item) => !is_null($item->picking_printed_at));
            @endphp

            <!-- Tombol Cetak PDF Semua - Muncul HANYA jika SEMUA picking sudah di-print -->
            @if($allPickingPrinted && !$allPrinted)
                <button onclick="printAllAndMarkPrinted()" 
                        id="btnPrintAll"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl flex items-center gap-2 transition">
                    <i class="fa-solid fa-file-pdf"></i> 
                    Cetak PDF Semua
                </button>
            @endif

            <!-- 3 Tombol Advanced - Muncul HANYA jika SEMUA picking sudah di-print -->
            @if($allPickingPrinted)
                <div id="advanced-print-buttons" class="flex gap-2">
                    <button onclick="printQC()" 
                            class="bg-emerald-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-clipboard-check"></i> Print RA QC
                    </button>
                    <button onclick="printPemesanan()" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-list-check"></i> Print RA PICKING
                    </button>
                    <button onclick="printPacking()"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                            <i class="fas fa-box mr-1"></i> Print RA Packing
                    </button>
                    <button onclick="printEkspedisi()" 
                            class="bg-emerald-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-truck"></i> Print RA DISTRIBUSI
                    </button>
                </div>
            @endif
        </div>
    </div>      

        <!-- TABEL -->
        <!-- TABEL -->
<div class="bg-white shadow-lg border-2 border-gray-800 overflow-x-auto">
    <table>
        <thead>
            <!-- BARIS JUDUL UTAMA -->
            <tr>
                <th colspan="14" class="main-title py-4 text-center">
                    <div class="flex justify-between items-center px-6">
                        <div class="flex-1 text-center">
                            Rekap Aktual Detail - 
                            {{ $data->first()?->nama_stokis ?? 'STOKIS JAKARTA AKTIF' }}
                            <span class="text-indigo-600 font-semibold">
                                {{ $data->first()?->rekap_number ?? '#0001' }}
                            </span>
                        </div>

                        @php
                            $firstDate = $data->min('created_at');
                        @endphp
                        @if($firstDate)
                            <div class="text-right flex-shrink-0">
                                <div class="text-gray-600 text-sm font-medium leading-tight">
                                    Waktu Serah Terima
                                </div>
                                <div class="text-gray-700 text-base font-semibold leading-tight">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </th>
            </tr>

            <!-- HEADER GROUP (BARIS KE-2) -->
            <tr class="header1">
                <th rowspan="2" class="bg-white-100">NO</th>                    <!-- ← Merge dengan baris di bawahnya -->
                <th rowspan="2" class="bg-white-100">WAKTU PRINT RA</th>  
                <th colspan="3">DETAIL ORDER</th>
                <th colspan="2">PENGIRIMAN & SERVICE</th>
                <th colspan="2">PEMBAYARAN</th>
                <th colspan="2">ESTIMASI PERSIAPAN</th>
                <th rowspan="2" class="bg-white-100">CATATAN</th>
                <th colspan="2" class="bg-white-100">STATUS PRINT</th>
            </tr>

            <!-- HEADER KOLOM DETAIL (BARIS KE-3) -->
            <tr class="header2">
                
                <th>ID ORDER</th>
                <th>NAMA UNIT</th>
                <th>KATEGORI</th>
                <th>PENGIRIMAN</th>
                <th>SERVICE</th>
                <th>TGL BAYAR</th>
                <th>JUMLAH BAYAR</th>
                <th>TGL ESTIMASI</th>
                <th>HARI</th>
                <!-- CATATAN sudah di-merged di atas -->
                <th class="bg-white-100">REKAP AKTUAL</th>
                <th class="bg-white-100">PICKING LIST</th>
            </tr>
        </thead>
        <tbody>
        @forelse($data as $item)
        <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
            
            <!-- Kolom NO -->
            <td class="font-medium text-center">{{ $loop->iteration }}</td>
            
            <td>{{ $item->printed_at ? \Carbon\Carbon::parse($item->printed_at)->format('d/m/Y H:i:s') : '-' }}</td>
            <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
            
            <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
            <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
            <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
            <td class="text-left">{{ $item->service_pengiriman ?? '-' }}</td>
            
            <td>
                {{ $item->payment_date 
                    ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i') 
                    : ($item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-') }}
            </td>
            <td class="text-right font-semibold">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
            
            <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
            <td class="font-medium">{{ $item->estimasi_hari ?? '-' }} Hari</td>
            
            <!-- CATATAN -->
            <td class="text-center text-xs py-3">
                @php
                    $catatan = $item->jakartaAktif?->catatan ?? $item->ket ?? '';
                    $lines = array_filter(explode("\n", trim($catatan)));
                    $lastLine = !empty($lines) ? trim(end($lines)) : '';
                    $display = preg_replace('/^Di proses bulk pada .*?: /i', '', $lastLine);
                @endphp
                
                @if($display)
                    <span class="inline-block font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded-md">
                        {{ strtoupper($display) }}
                    </span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>
            
            <!-- STATUS PRINT -->
            <td class="printed-status text-center">
                @if($item->printed_at)
                    <span class="text-green-600 text-3xl"><i class="fa-solid fa-circle"></i></span>
                @else
                    <span class="text-red-500 text-3xl"><i class="fa-solid fa-circle"></i></span>
                @endif
            </td>
            
            <td class="text-center action-cell">
                <button onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                        class="action-btn {{ $item->picking_printed_at ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700' }}">
                    @if($item->picking_printed_at)
                        <i class="fa-solid fa-file-pdf"></i>
                    @else
                        <i class="fa-solid fa-print"></i>
                    @endif
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="14" class="text-center py-20 text-gray-500">
                Belum ada data Realisasi Aktif untuk {{ now()->format('F Y') }}
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

        @if($data->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>Menampilkan <strong>{{ $data->count() }}</strong> data</div>
            <div>{{ $data->links() }}</div>
        </div>
        @endif
    </div>

    <!-- MODAL -->
    <div id="pickingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-xl font-semibold mb-2">Print Picking List</h3>
            <p class="text-gray-600 mb-6" id="modalMessage"></p>
            
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">
                    Batal
                </button>
                <button onclick="confirmPrintPicking()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>

<script>
let currentButton = null;

// ==================== CEK SEMUA PICKING ====================
function checkAllPickingPrinted() {

    const rows = document.querySelectorAll('tbody tr[data-id]');

    const allPrinted = Array.from(rows).every(row => {

        const btn = row.querySelector('.action-btn');

        return btn &&
            btn.classList.contains('text-purple-600');

    });

    const advancedButtons =
        document.getElementById('advanced-print-buttons');

    if (advancedButtons) {
        advancedButtons.classList.toggle(
            'hidden',
            !allPrinted
        );
    }
}

// ==================== PRINT QC ====================
function printQC() {
    window.open(
        "{{ route('order.realisasi.print-qc') }}?ids={{ $data->pluck('id')->join(',') }}",
        '_blank'
    );
}

// ==================== PRINT PEMESANAN ====================
function printPemesanan() {
    window.open(
        "{{ route('order.realisasi.print-pemesanan') }}?ids={{ $data->pluck('id')->join(',') }}",
        '_blank'
    );
}

// ==================== PRINT EKSPEDISI ====================
function printEkspedisi() {
    window.open(
        "{{ route('order.realisasi.print-ekspedisi') }}?ids={{ $data->pluck('id')->join(',') }}",
        '_blank'
    );
}

// ==================== PRINT PACKING ====================
function printPacking() {
    window.open(
        "{{ route('order.realisasi.print-packing') }}?ids={{ $data->pluck('id')->join(',') }}",
        '_blank'
    );
}

// ==================== BUKA MODAL ====================
function printPickingList(btn, id, noPL) {

    currentButton = btn;

    document.getElementById('modalMessage').innerHTML =
        `Cetak Picking List untuk No. PL <strong>${noPL}</strong>?`;

    document.getElementById('pickingModal')
        .classList.remove('hidden');
}

// ==================== TUTUP MODAL ====================
function closeModal() {

    document.getElementById('pickingModal')
        .classList.add('hidden');
}

// ==================== KONFIRMASI PRINT PICKING ====================
async function confirmPrintPicking() {

    if (!currentButton) return;

    const row = currentButton.closest('tr');
    const id = row.dataset.id;

    try {

        // buka PDF
        window.open(
            `/order/realisasi/picking-list/${id}`,
            '_blank'
        );

        // ambil csrf
        const csrf =
            document.querySelector(
                'meta[name="csrf-token"]'
            );

        // update picking_printed_at
        await fetch(
            `/order/realisasi/${id}/mark-picking-printed`,
            {
                method: 'POST',
                headers: {
                    'Content-Type':
                        'application/json',
                    'X-CSRF-TOKEN':
                        csrf
                        ? csrf.getAttribute('content')
                        : ''
                }
            }
        );

        // ubah icon langsung
        currentButton.innerHTML =
            '<i class="fa-solid fa-file-pdf"></i>';

        currentButton.classList.remove(
            'text-blue-600'
        );

        currentButton.classList.add(
            'text-purple-600'
        );

        closeModal();

        // refresh otomatis
        setTimeout(() => {
            window.location.reload();
        }, 500);

    } catch (error) {

        console.error(
            'Gagal update picking_printed_at:',
            error
        );

        alert(
            'Gagal memperbarui status Picking List'
        );
    }
}

// ==================== CETAK PDF SEMUA ====================
async function printAllAndMarkPrinted() {

    if (!confirm(
        'Cetak SEMUA data dan tandai sebagai sudah dicetak?'
    )) {
        return;
    }

    try {

        window.open(
            "{{ route('order.realisasi.print-pdf') }}?mark_printed=true",
            '_blank'
        );

        const csrf =
            document.querySelector(
                'meta[name="csrf-token"]'
            );

        await fetch(
            "{{ route('order.realisasi.mark-printed-all') }}",
            {
                method: 'POST',
                headers: {
                    'Content-Type':
                        'application/json',
                    'X-CSRF-TOKEN':
                        csrf
                        ? csrf.getAttribute('content')
                        : ''
                }
            }
        );

        updateStatusOnly();

        setTimeout(() => {
            window.location.reload();
        }, 1000);

    } catch (error) {

        console.error(
            'Gagal update printed_at:',
            error
        );
    }
}

// ==================== UPDATE STATUS ====================
function updateStatusOnly() {

    document.querySelectorAll(
        '.printed-status'
    ).forEach(cell => {

        cell.innerHTML = `
            <span class="text-green-600 text-3xl">
                <i class="fa-solid fa-circle"></i>
            </span>
        `;
    });
}

// ==================== LOAD ====================
document.addEventListener(
    'DOMContentLoaded',
    function () {

        checkAllPickingPrinted();

        setInterval(() => {
            checkAllPickingPrinted();
        }, 3000);
    }
);
</script>
</body>
</html>