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
        }
        
        th, td {
            border: 1px solid #374151;
            padding: 8px 6px;
            text-align: center;
            vertical-align: middle;
        }
        
        .main-title {
            font-size: 1.1rem;
            font-weight: 700;
            background-color: #ffffff;
        }
        
        .header1 { background-color: #ffffff; font-weight: 700; }
        .header2 { background-color: #ffffff; font-weight: 600; }

        tr:hover { background-color: #f8fafc; }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .printed-status { font-size: 0.8rem; }
        .action-btn { 
            font-size: 1.5rem; 
            transition: all 0.2s; 
            padding: 6px 10px;
            border-radius: 8px;
        }
        .action-btn:hover { 
            transform: scale(1.2); 
            background-color: #f1f5f9;
        }
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

        <div id="print-all-container" class="flex items-center gap-3">
            <button onclick="printAllAndMarkPrinted()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl flex items-center gap-2 transition">
                <i class="fa-solid fa-file-pdf"></i> 
                Cetak PDF Semua
            </button>
        </div>
    </div>      

        <!-- TABEL -->
        <div class="bg-white shadow-lg border-2 border-gray-800 overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th colspan="14" class="main-title py-3 text-center">
                            Rekap Aktual Detail - {{ $data->first()?->nama_stokis ?? 'STOKIS JAKARTA AKTIF' }}
                        </th>
                    </tr>

                    <tr class="header1">
                        <th colspan="2">TANGGAL</th>
                        <th colspan="4">PENGIRIMAN & BARANG</th>
                        <th colspan="2">PEMBAYARAN</th>
                        <th colspan="2">BERAT biMBA SHOP | BERAT AKTUAL</th>
                        <th>STOKIS</th>
                        <th colspan="2">ESTIMASI PERSIAPAN</th>
                        <th colspan="1">KETERANGAN</th>
                        <th colspan="2">STATUS PRINT</th>
                    </tr>

                    <tr class="header2">
                        <th>No PL</th>
                        <th>Waktu Serah Terima</th>
                        <th>NAMA UNIT</th>
                        <th>PENGIRIMAN</th>
                        <th>SERVICE</th>
                        <th>KATEGORI</th>
                        <th>TGL BAYAR</th>
                        <th>JUMLAH BAYAR</th>
                        <th>BERAT biMBA SHOP</th>
                        <th>BERAT AKTUAL</th>
                        <th>NAMA STOKIS</th>
                        <th>TGL ESTIMASI</th>
                        <th>ESTIMASI HARI</th>
                        <th>KET</th>
                        <th class="bg-white-100">REKAP AKTUAL</th>
                        <th class="bg-white-100">PICKING LIST</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                        <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
                        <td>{{ $item->tgl_turun_pl ? \Carbon\Carbon::parse($item->tgl_turun_pl)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                        <td class="text-left">{{ $item->service_pengiriman ?? '-' }}</td>
                        <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                        <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-right font-semibold">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right font-semibold">{{ number_format($item->order_weight ?? 0, 0, ',', '.') }} gr</td>
                        <td class="text-right font-semibold">{{ null }}</td>
                        <td class="text-left">{{ $item->nama_stokis ?? '-' }}</td>
                        <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                        <td class="font-medium">{{ $item->estimasi_hari ?? '-' }} Hari</td>
                        <td class="text-left text-xs">{{ $item->ket ?? '-' }}</td>
                        
                        <!-- STATUS PRINT - Hanya Lingkaran -->
                        <td class="printed-status text-center">
                            @if($item->printed_at)
                                <span class="text-green-600 text-3xl">
                                    <i class="fa-solid fa-circle"></i>
                                </span>
                            @else
                                <span class="text-red-500 text-3xl">
                                    <i class="fa-solid fa-circle"></i>
                                </span>
                            @endif
                        </td>
                        
                        <!-- AKSI PICKING LIST -->
                        <td class="text-center action-cell">
                            <button onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
                                    class="action-btn
                                    {{ $item->picking_printed_at
                                        ? 'text-purple-600'
                                        : 'text-blue-600 hover:text-blue-700' }}">

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
                <button onclick="closeModal()" 
                        class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">
                    Batal
                </button>
                <button onclick="confirmPrintPicking()" 
                        class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>

<script>
let currentButton = null;

// ==================== PICKING LIST ====================
function printPickingList(btn, id, noPL) {
    currentButton = btn;

    const modal = document.getElementById('pickingModal');

    document.getElementById('modalMessage').innerHTML =
        `Cetak Picking List untuk No. PL <strong>${noPL}</strong>?`;

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('pickingModal').classList.add('hidden');
}

function confirmPrintPicking() {

    if (!currentButton) return;

    const row = currentButton.closest('tr');
    const id = row.dataset.id;

    // buka halaman picking list
    window.open(`/order/realisasi/picking-list/${id}`, '_blank');

    // ubah icon menjadi PDF
    currentButton.innerHTML =
        `<i class="fa-solid fa-file-pdf"></i>`;

    currentButton.classList.remove(
        'text-blue-600',
        'hover:text-blue-700'
    );

    currentButton.classList.add(
        'text-purple-600'
    );

    // tandai sudah print
    row.dataset.pickingPrinted = "1";

    closeModal();
}

// ==================== CETAK PDF SEMUA ====================
async function printAllAndMarkPrinted() {

    if (!confirm('Cetak SEMUA data dan tandai sebagai sudah dicetak?')) {
        return;
    }

    const printUrl =
        "{{ route('order.realisasi.print-pdf') }}?mark_printed=true";

    // buka PDF
    window.open(printUrl, '_blank');

    // update tampilan
    updateStatusOnly();

    // sembunyikan tombol
    hidePrintAllButton();

    try {

        const csrf = document.querySelector(
            'meta[name="csrf-token"]'
        );

        await fetch(
            "{{ route('order.realisasi.mark-printed-all') }}",
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                        ? csrf.getAttribute('content')
                        : ''
                }
            }
        );

    } catch (error) {

        console.error(
            'Gagal update printed status:',
            error
        );
    }
}

// ==================== UPDATE STATUS PRINT ====================
function updateStatusOnly() {

    document.querySelectorAll('tbody tr').forEach(row => {

        const statusCell =
            row.querySelector('.printed-status');

        if (!statusCell) return;

        statusCell.innerHTML = `
            <span class="text-green-600 text-3xl">
                <i class="fa-solid fa-circle"></i>
            </span>
        `;
    });
}

// ==================== HIDE BUTTON ====================
function hidePrintAllButton() {

    const printContainer =
        document.getElementById('print-all-container');

    if (printContainer) {
        printContainer.style.display = 'none';
    }
}

// ==================== PAGE LOAD ====================
document.addEventListener('DOMContentLoaded', function() {

    const rows =
        document.querySelectorAll('tbody tr');

    let allPrinted = true;

    rows.forEach(row => {

        const statusCell =
            row.querySelector('.printed-status');

        if (
            statusCell &&
            statusCell.querySelector('.text-red-500')
        ) {
            allPrinted = false;
        }
    });

    if (allPrinted && rows.length > 0) {
        hidePrintAllButton();
    }
});

// ==================== CLOSE MODAL OUTSIDE CLICK ====================
document.addEventListener('click', function(e) {

    const modal =
        document.getElementById('pickingModal');

    if (
        modal &&
        e.target === modal
    ) {
        closeModal();
    }
});

// ==================== ESC KEY CLOSE ====================
document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
</body>
</html>