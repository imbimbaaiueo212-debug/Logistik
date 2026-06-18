<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Aktual</title>
    
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
        
        .printed-status {
            font-size: 0.8rem;
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

        <!-- Tombol Cetak PDF Semua (akan disembunyikan jika semua sudah dicetak) -->
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
                        <th colspan="12" class="main-title py-3 text-left pl-4">
                            Rekap Aktual
                        </th>
                    </tr>

                    <!-- Header Level 1 & 2 tetap sama -->
                    <tr class="header1">
                        <th colspan="2">TANGGAL</th>
                        <th colspan="3">PENGIRIMAN & BARANG</th>
                        <th colspan="2">Pembayaran</th>
                        <th>STOKIS</th>
                        <th colspan="2">ESTIMASI PERSIAPAN</th>
                        <th colspan="2">KET & STATUS</th>
                    </tr>

                    <tr class="header2">
                        <th>No PL</th>
                        <th>TGL TURUN PL</th>
                        <th>NAMA UNIT</th>
                        <th>PENGIRIMAN</th>
                        <th>NAMA BARANG</th>
                        <th>TGL BAYAR</th>
                        <th>JUMLAH BAYAR</th>
                        <th>NAMA STOKIS</th>
                        <th>TGL ESTIMASI</th>
                        <th>ESTIMASI HARI</th>
                        <th>KET</th>
                        <th class="bg-white-100">STATUS PRINT</th>
                        <th class="bg-white-100">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr class="hover:bg-blue-50">
                        <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
                        <td>{{ $item->tgl_turun_pl ? \Carbon\Carbon::parse($item->tgl_turun_pl)->format('d/m/Y') : '-' }}</td>
                        <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                        <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                        <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-right font-semibold">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                        <td class="text-left nama-stokis">{{ $item->nama_stokis ?? 'JAKARTA' }}</td>
                        <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                        <td class="font-medium estimasi-hari">{{ $item->estimasi_hari ?? '-' }} Hari</td>
                        <td class="text-left text-xs">{{ $item->ket ?? '-' }}</td>
                        
                        <!-- STATUS PRINT -->
                        <td class="printed-status">
                            @if($item->printed_at)
                                <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                    <i class="fa-solid fa-print"></i>
                                    <span class="text-xs leading-tight">Dicetak<br>{{ $item->printed_at->format('d/m/Y H:i') }}</span>
                                </span>
                            @else
                                <span class="text-amber-500 font-medium text-xs">Belum Dicetak</span>
                            @endif
                        </td>
                        
                        <!-- AKSI -->
                        <td class="text-center" data-real-id="{{ $item->id }}">
                            @if($item->printed_at)
                                <a href="{{ route('order.realisasi.print-single', $item->id) }}" 
                                   target="_blank"
                                   onclick="return confirm('Data ini sudah dicetak pada {{ $item->printed_at->format('d/m/Y H:i') }}.\n\nCetak ulang?')"
                                   class="text-amber-600 hover:text-amber-700 text-xl">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            @else
                                <form action="{{ route('order.realisasi.delete', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete text-xl"
                                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-20 text-gray-500">
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

<script>
function printAllAndMarkPrinted() {
    if (!confirm('Cetak SEMUA data dan tandai sebagai sudah dicetak?')) return;

    const printUrl = "{{ route('order.realisasi.print-pdf') }}?mark_printed=true";
    window.open(printUrl, '_blank');

    // Update tampilan
    let allPrinted = true;

    document.querySelectorAll('tbody tr').forEach(row => {
        const statusCell = row.querySelector('.printed-status');
        const actionCell = row.querySelector('td[data-real-id]');

        if (statusCell && actionCell) {
            const id = actionCell.getAttribute('data-real-id');

            // Update status
            const now = new Date();
            const formatted = `${now.getDate().toString().padStart(2,'0')}/${(now.getMonth()+1).toString().padStart(2,'0')} ${now.getHours().toString().padStart(2,'0')}:${now.getMinutes().toString().padStart(2,'0')}`;

            statusCell.innerHTML = `
                <div class="inline-flex flex-col items-center text-green-600">
                    <i class="fa-solid fa-print text-lg"></i>
                    <span class="text-[10px] font-medium mt-0.5">Dicetak</span>
                    <span class="text-[10px]">${formatted}</span>
                </div>
            `;

            // Update tombol aksi
            actionCell.innerHTML = `
                <a href="/order/realisasi/print-pdf/${id}" 
                   target="_blank"
                   onclick="return confirm('Data ini sudah dicetak. Cetak ulang?')"
                   class="text-amber-600 hover:text-amber-700 text-xl">
                    <i class="fa-solid fa-print"></i>
                </a>
            `;
        }
    });

    // Sembunyikan tombol "Cetak PDF Semua"
    const printContainer = document.getElementById('print-all-container');
    if (printContainer) printContainer.style.display = 'none';

    // Update database
    fetch("{{ route('order.realisasi.mark-printed-all') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
}
</script>
</body>
</html>