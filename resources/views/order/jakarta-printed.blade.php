<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realisasi Order Unit Aktif - JUNI 2026</title>
    
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

        tr:hover { background-color: #ffffff; }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .btn-delete {
            color: #ef4444;
            transition: all 0.2s;
        }
        .btn-delete:hover {
            color: #b91c1c;
            transform: scale(1.1);
        }

        .estimasi-hari {
            font-weight: 700;
            color: #166534;
        }
        
        .nama-stokis {
            font-weight: 600;
            color: #1e40af;
        }
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

   <div class="max-w-screen-2xl mx-auto px-6 py-6">
    
    <!-- HEADER UTAMA -->
    <div class="flex justify-between items-center mb-6">
        
        <!-- KIRI -->
        <div>
           
            <a href="{{ route('order.jakarta-aktif') }}" 
               class="bg-gray-700 text-white px-6 py-3 rounded-xl hover:bg-gray-800 flex items-center gap-2">
                ← Kembali
            </a>
        </div>

        <!-- KANAN -->
        <div class="flex items-center gap-3">
           <a href="{{ route('order.realisasi.print-pdf') }}" 
               target="_blank"
               class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-file-pdf"></i> 
                Cetak PDF
            </a>
                        
        </div>
    </div>      


        <!-- TABEL -->
        <div class="bg-white shadow-lg border-2 border-gray-800 overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th colspan="11" class="main-title py-3 text-left pl-4">
                            Rekap Aktual
                        </th>
                    </tr>

                    <!-- Header Level 1 -->
                    <tr class="header1">
                        <th colspan="2">TANGGAL</th>
                        <th colspan="3">PENGIRIMAN & BARANG</th>
                        <th colspan="2">Pembayaran</th>
                        <th>STOKIS</th>
                        <th colspan="2">ESTIMASI PERSIAPAN</th>
                        <th colspan="2">KET</th>
                    </tr>

                    <!-- Header Level 2 -->
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
                        <th class="bg-white-100">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr class="hover:bg-blue-50">
                        <!-- No PL -->
                        <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
                        
                        <!-- TGL TURUN PL -->
                        <td>{{ $item->tgl_turun_pl ? \Carbon\Carbon::parse($item->tgl_turun_pl)->format('d/m/Y') : '-' }}</td>
                        
                        <!-- NAMA UNIT -->
                        <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                        
                        <!-- PENGIRIMAN -->
                        <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                        
                        <!-- NAMA BARANG -->
                        <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                        
                        <!-- TGL BAYAR -->
                        <td>
                            {{ $item->tgl_bayar 
                                ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') 
                                : '-' }}
                        </td>
                        
                        <!-- JUMLAH BAYAR -->
                        <td class="text-right font-semibold">
                            Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}
                        </td>
                        
                        <!-- NAMA STOKIS -->
                        <td class="text-left nama-stokis">{{ $item->nama_stokis ?? 'JAKARTA' }}</td>
                        
                        <!-- TGL ESTIMASI -->
                        <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                        
                        <!-- ESTIMASI HARI -->
                        <td class="font-medium estimasi-hari">
                            {{ $item->estimasi_hari ?? '-' }} Hari
                        </td>
                        
                        <!-- KET -->
                        <td class="text-left text-xs">{{ $item->ket ?? '-' }}</td>
                        
                        <!-- AKSI -->
                        <td>
                            <form action="{{ route('order.realisasi.delete', $item->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini? Data tidak bisa dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-20 text-gray-500">
                            Belum ada data Realisasi Aktif untuk Juni 2026
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
</body>
</html>