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
        font-size: 15px;
        border: 1px solid #374151;
    }
    

    th,
        td{
            border: 1px solid #37415171;
            padding-top:3px;
            padding-bottom:3px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:top;
            text-align:center;
            line-height:1;
        }

    /* ================== HEADER GROUP ================== */
    .header1 th, 
    .header2 th {
        background-color: #f1f5f9;
        border-bottom: 1px solid #374151;
        font-weight: 600;
    }

    .main-title {
        font-size: 1.05rem;
        font-weight: 700;
        background-color: #ffffff;
        border-bottom: 1px solid #374151;
        padding: 12px 10px;
    }

    /* Lebar Kolom yang dioptimalkan */
    .col-no       { width: 10px; }
    .col-id       { width: 50px; }
    .col-unit     { width: 100px; }
    .col-kategori { width: 50px; }
    .col-catatan  { width: 200px; }
    .col-picking  { width: 50px; }

    .text-left { text-align: left; }
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

            @if($allPickingPrinted && !$allPrinted)
                <button onclick="printAllAndMarkPrinted()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl flex items-center gap-2 transition">
                    <i class="fa-solid fa-file-pdf"></i> Cetak PDF Semua
                </button>
            @endif

            @if($allPickingPrinted)
                <div id="advanced-print-buttons" class="flex gap-2">
                    <button onclick="printAllAndMarkPrinted()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl flex items-center gap-2 transition">
                    <i class="fa-solid fa-file-pdf"></i> Cetak PDF Semua
                </button>
                    <button onclick="printQC()" class="bg-emerald-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-clipboard-check"></i> Print RA QC
                    </button>
                    <button onclick="printPemesanan()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-list-check"></i> Print RA PICKING
                    </button>
                    <button onclick="printPacking()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fas fa-box mr-1"></i> Print RA Packing
                    </button>
                    <button onclick="printEkspedisi()" class="bg-emerald-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-truck"></i> Print RA DISTRIBUSI
                    </button>
                </div>
            @endif
        </div>
    </div>      

    <!-- TABEL -->
    <div class="bg-white shadow-lg border-2 border-gray-800 overflow-x-auto">
        <table>
            <thead>
                <!-- BARIS JUDUL UTAMA + STATUS PRINT -->
                <tr>
                    <th colspan="9" class="main-title py-4 text-center">
                        <div class="flex justify-between items-center px-6">
                            
                            <!-- Status Print -->
                            <div class="flex items-center gap-3">
                                @php $allPrinted = $data->every(fn($item) => !is_null($item->printed_at)); @endphp
                                @if($allPrinted)
                                    <span class="text-green-600 text-2xl"><i class="fa-solid fa-circle"></i></span>
                                    <span class="text-green-600 font-semibold">Sudah Dicetak</span>
                                @else
                                    <span class="text-red-500 text-2xl"><i class="fa-solid fa-circle"></i></span>
                                    <span class="text-red-500 font-semibold">Belum Dicetak</span>
                                @endif
                            </div>

                            <!-- Judul -->
                            <div class="flex-1 text-center px-8">
                                Rekap Aktual Detail - 
                                {{ $data->first()?->nama_stokis ?? 'STOKIS JAKARTA AKTIF' }}
                                <span class="text-indigo-600 font-semibold">
                                    {{ $data->first()?->rekap_number ?? '#0001' }}
                                </span>
                            </div>

                            <!-- Waktu Serah Terima -->
                            @php $firstDate = $data->min('created_at'); @endphp
                            @if($firstDate)
                                <div class="text-right flex-shrink-0">
                                    <div class="text-gray-600 text-sm font-medium">Waktu Serah Terima</div>
                                    <div class="text-gray-700 text-base font-semibold">
                                        {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </th>
                </tr>

                <!-- HEADER GROUP -->
                <tr class="header1">
                    <th rowspan="2" class="col-no" style="
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">NO</th>
                    <th colspan="3">DETAIL ORDER</th>
                    <th rowspan="2" class="col-catatan" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">CATATAN</th>
                    <th rowspan="2" class="col-picking" style="
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">PICKING LIST</th>
                </tr>

                <tr class="header2">
                    <th class="col-id">ID ORDER</th>
                    <th class="col-unit">NAMA UNIT</th>
                    <th class="col-kategori">KATEGORI</th>
                </tr>
            </thead>
            <tbody>
            @forelse($data as $item)
            <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                
                <td class="font-medium text-center">{{ $loop->iteration }}</td>
                <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-center">{{ $item->nama_barang ?? '-' }}</td>
                
                <!-- CATATAN -->
                <td class="text-center text-xs py-3">
                    @php
                        $catatan = $item->jakartaAktif?->catatan ?? $item->ket ?? '';
                        $display = preg_replace('/^Di proses bulk pada .*?: /i', '', trim($catatan));
                    @endphp
                    @if($display)
                        <span class="inline-block font-bold text-gray-900 bg-gray-100 px-3 py-1 rounded-md">
                            {{ strtoupper($display) }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                
                <!-- PICKING LIST -->
                <!-- PICKING LIST - ICON DIBUAT LEBIH BESAR -->
<td class="text-center action-cell">
    <button onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
            class="action-btn {{ $item->picking_printed_at ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700' }} text-2xl">
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
                <td colspan="9" class="text-center py-20 text-gray-500">
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