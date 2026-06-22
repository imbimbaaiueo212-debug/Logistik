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
            border: 1px solid #37415134;
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
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-clipboard-check"></i> Print QC
                    </button>
                    <button onclick="printPemesanan()" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-list-check"></i> Print Pemesanan
                    </button>
                    <button onclick="printEkspedisi()" 
                            class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl flex items-center gap-2 transition">
                        <i class="fa-solid fa-truck"></i> Print Ekspedisi
                    </button>
                </div>
            @endif
        </div>
    </div>      

        <!-- TABEL -->
        <div class="bg-white shadow-lg border-2 border-gray-800 overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th colspan="16" class="main-title py-3 text-center">
                            Rekap Aktual Detail - {{ $data->first()?->nama_stokis ?? 'STOKIS JAKARTA AKTIF' }}
                        </th>
                    </tr>

                    <tr class="header1">
                        <th colspan="2"></th>
                        <th colspan="3">DETAIL ORDER</th>
                        <th colspan="2">PENGIRIMAN & BARANG</th>
                        <th colspan="2">PEMBAYARAN</th>
                       
                        <th colspan="2">ESTIMASI PERSIAPAN</th>
                        
                        <th colspan="1">KETERANGAN</th>
                        <th colspan="2">STATUS PRINT</th>
                    </tr>

                    <tr class="header2">
                        <th class="bg-white-100">NO</th>                    <!-- ← Tambahkan ini -->
                        <th>WAKTU SERAH TERIMA</th>
                        <th>ID ORDER</th>
                        
                        <th>NAMA UNIT</th>
                        <th>KATEGORI</th>
                        <th>PENGIRIMAN</th>
                        <th>SERVICE</th>
                        
                        <th>TGL BAYAR</th>
                        <th>JUMLAH BAYAR</th>
                        
                        <th>TGL ESTIMASI</th>
                        <th>ESTIMASI HARI</th>
                        
                        <th>CATATAN</th>
                        <th class="bg-white-100">REKAP AKTUAL</th>
                        <th class="bg-white-100">PICKING LIST</th>
                    </tr>
                </thead>
                <tbody>
    @forelse($data as $item)
    <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
        
        <!-- KOLOM NOMOR BARU -->
        <td class="font-medium text-center">{{ $loop->iteration }}</td>
        <td>{{ $item->printed_at ? \Carbon\Carbon::parse($item->printed_at)->format('d/m/Y H:i') : '-' }}</td>
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
        
        <td class="text-left text-xs whitespace-pre-wrap">
    @php
        $catatan = $item->jakartaAktif?->catatan ?? $item->ket ?? '';
        $lines = array_filter(explode("\n", trim($catatan)));
        $lastLine = !empty($lines) ? trim(end($lines)) : '';
        $display = preg_replace('/^Di proses bulk pada .*?: /i', '', $lastLine);
    @endphp
    
    @if($display)
        <span class="font-bold text-orange-700">
            {{ $display }}
        </span>
    @else
        <span class="text-gray-400">-</span>
    @endif
</td>
        
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
        <td colspan="16" class="text-center py-20 text-gray-500">
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