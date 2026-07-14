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
        
        <!-- HEADER UTAMA -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Rekap Aktual</h1>
                <p class="text-gray-600">Data Realisasi yang sudah diproses</p>
            </div>

            <!-- Filter Kategori -->
            <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
                <a href="{{ route('order.jakarta-printed') }}" 
                   class="px-6 py-3 rounded-3xl font-medium transition-all {{ !request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    Semua
                </a>
                <a href="{{ route('order.jakarta-printed') }}?kategori=Modul" 
                   class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🟢 Modul
                </a>
                <a href="{{ route('order.jakarta-printed') }}?kategori=Majalah" 
                   class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🔵 Majalah
                </a>
                <a href="{{ route('order.jakarta-printed') }}?kategori=Sertifikat" 
                   class="px-6 py-3 rounded-3xl font-medium transition-all {{ request('kategori') === 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100' }}">
                    🔴 Sertifikat
                </a>
            </div>
        </div>

        <!-- ==================== ACCORDION PER TANGGAL ==================== -->
        @foreach($groupedData as $tanggal => $rows)
            @php
                $first = $rows->first();
                $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
                $allPickingDone = $rows->every(fn($item) => !is_null($item->picking_printed_at));
                $allPrinted     = $rows->every(fn($item) => !is_null($item->printed_at));
                $collapseId     = 'collapse_' . $loop->index;
            @endphp

            <div class="bg-white shadow-lg border-2 border-gray-800 mb-8 rounded-xl overflow-hidden" 
                 data-tanggal="{{ $tanggal }}">

                <!-- HEADER (CLICKABLE) -->
                <button onclick="toggleContent('{{ $collapseId }}')" 
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
                        <span class="font-bold text-lg">
                            Rekap Aktual Detail - {{ $first->nama_stokis ?? 'STOKIS JAKARTA AKTIF' }}
                        </span>
                        <span class="text-indigo-600 font-semibold ml-2">{{ $first->rekap_number ?? '#0001' }}</span>
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-500">Tanggal Turun PL</div>
                        <div class="font-semibold text-gray-800">{{ $tanggalFormatted }}</div>
                    </div>
                </button>

                <!-- CONTENT (TABEL + TOMBOL) -->
                <div id="{{ $collapseId }}" class="accordion-content">
                    <table class="w-full">
                        <thead>
                            <tr class="header1">
                                <th rowspan="2" class="col-no">NO</th>
                                <th colspan="3">DETAIL ORDER</th>
                                <th rowspan="2" class="col-distribusi">DISTRIBUSI</th>
                                <th rowspan="2" class="col-catatan">CATATAN</th>
                                <th rowspan="2" class="col-picking">PICKING LIST</th>
                            </tr>
                            <tr class="header2">
                                <th class="col-id">ID ORDER</th>
                                <th class="col-unit">NAMA UNIT</th>
                                <th class="col-kategori">KATEGORI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $item)
                            <tr class="hover:bg-blue-50" data-id="{{ $item->id }}" data-nopl="{{ $item->no_pl }}">
                                <td class="font-medium text-center">{{ $loop->iteration }}</td>
                                <td class="font-medium">{{ $item->no_pl ?? '-' }}</td>
                                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                                <td class="text-center text-sm">{{ $item->nama_barang ?? '-' }}</td>

                                <td class="text-center">
                                    <div>{{ $item->pengiriman ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->service_pengiriman ?? '-' }}</div>
                                </td>

                                <td class="text-center text-xs">
                                    @php
                                        $catatan = $item->jakartaAktif?->catatan ?? $item->ket ?? '';
                                        $display = preg_replace('/^Di proses bulk pada .*?: /i', '', trim($catatan));
                                    @endphp
                                    @if($display)
                                        <span class="inline-block bg-gray-100 px-3 py-1 rounded-md">{{ strtoupper($display) }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <button onclick="printPickingList(this, {{ $item->id }}, '{{ $item->no_pl }}')"
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

                    <!-- TOMBOL PER TANGGAL -->
                    @if($allPickingDone)
                    <div class="bg-gray-50 border-t p-4 flex flex-wrap gap-3 justify-end">
                        <button onclick="printPerDate('{{ $tanggal }}', 'prising')" 
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-file-pdf"></i> Cetak RA Prising
                        </button>

                        <button onclick="printPerDate('{{ $tanggal }}', 'pemesanan')" 
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-list-check"></i> RA PICKING
                        </button>

                        <button onclick="printPerDate('{{ $tanggal }}', 'qc')" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-clipboard-check"></i> RA QC
                        </button>

                        <button onclick="printPerDate('{{ $tanggal }}', 'packing')" 
                                class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fas fa-box"></i> RA PACKING
                        </button>

                        <button onclick="printPerDate('{{ $tanggal }}', 'ekspedisi')" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-truck"></i> RA EKSPEDISI
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if($data->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>Menampilkan <strong>{{ $data->count() }}</strong> data</div>
        </div>
        @endif
    </div>

    <!-- Modal Picking List -->
    <div id="pickingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-xl font-semibold mb-2">Print Picking List</h3>
            <p class="text-gray-600 mb-6" id="modalMessage"></p>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
                <button onclick="confirmPrintPicking()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleContent(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);

            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.textContent = '▼';
            } else {
                content.style.display = 'none';
                icon.textContent = '▶';
            }
        }

        // Print functions tetap sama
        function printPerDate(tanggal, type) {
            const container = document.querySelector(`[data-tanggal="${tanggal}"]`);
            const ids = Array.from(container.querySelectorAll('tr[data-id]'))
                            .map(row => row.dataset.id)
                            .join(',');

            let url = '';
            
            if (type === 'prising') {
                url = `{{ route('order.realisasi.print-pdf') }}?ids=${ids}&mark_printed=true`;
            } else if (type === 'pemesanan') {
                url = `{{ route('order.realisasi.print-pemesanan') }}?ids=${ids}`;
            } else if (type === 'qc') {
                url = `{{ route('order.realisasi.print-qc') }}?ids=${ids}`;
            } else if (type === 'packing') {
                url = `{{ route('order.realisasi.print-packing') }}?ids=${ids}`;
            } else if (type === 'ekspedisi') {
                url = `{{ route('order.realisasi.print-ekspedisi') }}?ids=${ids}`;
            }

            if (url) {
                window.open(url, '_blank');
            }
        }

        // Modal functions (tetap sama)
        let currentButton = null;

        function printPickingList(btn, id, noPL) {
            currentButton = btn;
            document.getElementById('modalMessage').innerHTML = `Cetak Picking List untuk No. PL <strong>${noPL}</strong>?`;
            document.getElementById('pickingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('pickingModal').classList.add('hidden');
        }

        async function confirmPrintPicking() {
            if (!currentButton) return;
            const row = currentButton.closest('tr');
            const id = row.dataset.id;
            window.open(`/order/realisasi/picking-list/${id}`, '_blank');
            closeModal();
            setTimeout(() => location.reload(), 800);
        }

        // Buka semua accordion saat pertama kali load (opsional)
        document.addEventListener('DOMContentLoaded', function () {
            // Kalau mau semua terbuka saat load, hapus baris di bawah ini
            document.querySelectorAll('.accordion-content').forEach(el => {
                el.style.display = 'none';
            });
        });
    </script>
</body>
</html>