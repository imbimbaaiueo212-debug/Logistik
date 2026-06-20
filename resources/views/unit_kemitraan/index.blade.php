<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Unit Kemitraan - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 10px 6px; font-size: 0.8rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; position: sticky; top: 0; z-index: 10; }
        tr:hover { background-color: #f8fafc; }
        .truncate { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .small-text { font-size: 0.75rem; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data Unit Kemitraan</h1>
                <p class="text-gray-600">Semua kolom dari tabel unit_kemitraan</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('unit-kemitraan.create') }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700">
                    ➕ Tambah Unit Baru
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                        class="bg-green-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-green-700 flex items-center gap-2">
                    📤 Import dari Excel
                </button>
            </div>
        </div>

        <!-- ==================== FORM IMPORT ==================== -->
        <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Import Data Unit Kemitraan</h2>
            <form action="{{ route('unit-kemitraan.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel / CSV</label>
                        <input type="file" name="import_file" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 
                                      file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                               accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" 
                            class="bg-green-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-green-700">
                        🚀 Import Sekarang
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    Format yang didukung: .xlsx, .xls, .csv (maksimal 10MB)
                </p>
            </form>
        </div>
        <!-- ==================== END FORM IMPORT ==================== -->

        <!-- Tabel Utama -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th>ID Record</th>
                        <th>No Cab</th>
                        <th>BiMBA AIUEO Unit</th>
                        <th>Status</th>
                        <th>Ops</th>
                        <th>No Telp Unit</th>
                        <th>Email Unit</th>
                        <th>Alamat Unit</th>
                        <th>RT</th>
                        <th>RW</th>
                        <th>Provinsi</th>
                        <th>Kab/Kota</th>
                        <th>Kecamatan</th>
                        <th>Kel/Desa</th>
                        <th>Kode Pos</th>
                        <th>No Induk Mitra</th>
                        <th>Nama Mitra</th>
                        <th>Email Mitra</th>
                        <th>No HP Mitra</th>
                        <th>Bank</th>
                        <th>No Rekening</th>
                        <th>Atas Nama</th>
                        <th>No Akta</th>
                        <th>Tgl Akta</th>
                        <th>Nilai Lisensi</th>
                        <th>% Mitra</th>
                        <th>% YPAI</th>
                        <th>Awal</th>
                        <th>Akhir</th>
                        <th>Perpanjang</th>
                        <th>Tutup</th>
                        <th>JMP</th>
                        <th>LPM</th>
                        <th>Pengembalian</th>
                        <th>Tanggal VA BCA</th>
                        <th>VA Mandiri Royalti</th>
                        <th>VA Mandiri Lisensi</th>
                        <th>Marketing</th>
                        <th>Koorwil/KPK/Sos</th>
                        <th>Detail</th>
                        <th>Note</th>
                        <th>Updated By</th>
                        <th>Last Updated</th>
                        <th>Sisa 3</th>
                        <th>Sisa 1</th>
                        <th>Sisa 2</th>
                        <th>Sisa 4</th>
                        <th>Sisa F</th>
                        <th>Masa Kontrak</th>
                        <th>Sisa</th>
                        <th>Sisa RR</th>
                        <th>No Lokasi</th>
                        <th>Kategori Perubahan</th>
                        <th>PDF</th>
                        <th>Update PDF</th>
                        <th>Vendor Stokis 1</th>
                        <th>Vendor Stokis 2</th>
                        <th>Alamat Saat Ini</th>
                        <th>Alamat Mitra</th>
                        <th>No Cab BiMBA Unit</th>
                        <th>LEN Perubahan Unit</th>
                        <th>Kirim Email Lisensi</th>
                        <th>Jakarta</th>
                        <th>Tanggal Update</th>
                        <th>Akun Facebook</th>
                        <th>Akun Instagram</th>
                        <th>Akun Media Sosial</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($unitKemitraans as $unit)
                    <tr class="hover:bg-gray-50">
                        <!-- ... (semua kolom tetap sama seperti sebelumnya) ... -->
                        <td class="font-medium">{{ $unit->id_record }}</td>
                        <td>{{ $unit->no_cab ?? '-' }}</td>
                        <td>{{ $unit->bimba_aiueo_unit ?? '-' }}</td>
                        <td>{{ $unit->status ?? '-' }}</td>
                        <td>{{ $unit->ops ?? '-' }}</td>
                        <td>{{ $unit->no_telp_unit ?? '-' }}</td>
                        <td class="truncate">{{ $unit->email_unit ?? '-' }}</td>
                        <td class="truncate">{{ $unit->alamat_unit ?? '-' }}</td>
                        <td>{{ $unit->rt ?? '-' }}</td>
                        <td>{{ $unit->rw ?? '-' }}</td>
                        <td>{{ $unit->provinsi ?? '-' }}</td>
                        <td>{{ $unit->kab_kota ?? '-' }}</td>
                        <td>{{ $unit->kecamatan ?? '-' }}</td>
                        <td>{{ $unit->kel_desa ?? '-' }}</td>
                        <td>{{ $unit->kode_pos ?? '-' }}</td>
                        <td>{{ $unit->no_induk_mitra ?? '-' }}</td>
                        <td class="truncate">{{ $unit->nama_mitra ?? '-' }}</td>
                        <td class="truncate">{{ $unit->email ?? $unit->email_mitra ?? '-' }}</td>
                        <td>{{ $unit->no_hp ?? $unit->no_hp_mitra ?? '-' }}</td>
                        <td>{{ $unit->bank ?? '-' }}</td>
                        <td>{{ $unit->no_rekening ?? '-' }}</td>
                        <td>{{ $unit->atas_nama ?? '-' }}</td>
                        <td>{{ $unit->no_akta ?? '-' }}</td>
                        <td>{{ $unit->tgl_akta ? $unit->tgl_akta->format('d/m/Y') : '-' }}</td>
                        <td class="text-right">{{ number_format($unit->nilai_lisensi ?? 0, 2) }}</td>
                        <td class="text-right">{{ $unit->persen_mitra ?? '-' }}</td>
                        <td class="text-right">{{ $unit->persen_ypai ?? '-' }}</td>
                        <td>{{ $unit->awal ? $unit->awal->format('d/m/Y') : '-' }}</td>
                        <td>{{ $unit->akhir ? $unit->akhir->format('d/m/Y') : '-' }}</td>
                        <td>{{ $unit->perpanjang ? $unit->perpanjang->format('d/m/Y') : '-' }}</td>
                        <td>{{ $unit->tutup ? $unit->tutup->format('d/m/Y') : '-' }}</td>
                        <td>{{ $unit->jmp ?? '-' }}</td>
                        <td>{{ $unit->lpm ?? '-' }}</td>
                        <td>{{ $unit->pengembalian ?? '-' }}</td>
                        <td>{{ $unit->tanggal ? $unit->tanggal->format('d/m/Y') : '-' }}</td>
                        <td>{{ $unit->va_mandiri_royalti ?? '-' }}</td>
                        <td>{{ $unit->va_mandiri_lisensi ?? '-' }}</td>
                        <td>{{ $unit->marketing ?? '-' }}</td>
                        <td>{{ $unit->koorwil_kpk_sos ?? '-' }}</td>
                        <td class="truncate">{{ $unit->detail ?? '-' }}</td>
                        <td class="truncate">{{ $unit->note ?? '-' }}</td>
                        <td>{{ $unit->updated_by ?? '-' }}</td>
                        <td class="small-text">{{ $unit->last_updated ? $unit->last_updated->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $unit->sisa_3 ?? '-' }}</td>
                        <td>{{ $unit->sisa_1 ?? '-' }}</td>
                        <td>{{ $unit->sisa_2 ?? '-' }}</td>
                        <td>{{ $unit->sisa_4 ?? '-' }}</td>
                        <td>{{ $unit->sisa_f ?? '-' }}</td>
                        <td>{{ $unit->masa_kontrak ?? '-' }}</td>
                        <td>{{ $unit->sisa ?? '-' }}</td>
                        <td>{{ $unit->sisa_rr ?? '-' }}</td>
                        <td>{{ $unit->no_lokasi ?? '-' }}</td>
                        <td>{{ $unit->kategori_perubahan ?? '-' }}</td>
                        <td class="truncate">{{ $unit->pdf ?? '-' }}</td>
                        <td class="truncate">{{ $unit->update_pdf ?? '-' }}</td>
                        <td>{{ $unit->vendor_stokis_1 ?? '-' }}</td>
                        <td>{{ $unit->vendor_stokis_2 ?? '-' }}</td>
                        <td class="truncate">{{ $unit->alamat_saat_ini ?? '-' }}</td>
                        <td class="truncate">{{ $unit->alamat_mitra ?? '-' }}</td>
                        <td>{{ $unit->no_cab_bimba_unit ?? '-' }}</td>
                        <td>{{ $unit->len_perubahan_unit ?? '-' }}</td>
                        <td>{{ $unit->kirim_email_lisensi ?? '-' }}</td>
                        <td>{{ $unit->jakarta ?? '-' }}</td>
                        <td class="small-text">{{ $unit->tanggal_update ? $unit->tanggal_update->format('d/m/Y H:i') : '-' }}</td>
                        <td class="truncate">{{ $unit->akun_facebook ?? '-' }}</td>
                        <td class="truncate">{{ $unit->akun_instagram ?? '-' }}</td>
                        <td class="truncate">{{ $unit->akun_media_sosial_unit_bimba_aiueo ?? '-' }}</td>
                        <td class="text-center whitespace-nowrap">
                            <a href="{{ route('unit-kemitraan.show', $unit) }}" class="text-blue-600 hover:text-blue-700 mx-1">👁</a>
                            <a href="{{ route('unit-kemitraan.edit', $unit) }}" class="text-amber-600 hover:text-amber-700 mx-1">✏</a>
                            <button onclick="if(confirm('Yakin hapus?')) document.getElementById('delete-{{ $unit->id_record }}').submit()" 
                                    class="text-red-600 hover:text-red-700 mx-1">🗑</button>
                            <form id="delete-{{ $unit->id_record }}" action="{{ route('unit-kemitraan.destroy', $unit) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="70" class="text-center py-20 text-gray-500">
                            Belum ada data unit kemitraan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($unitKemitraans->count() > 0)
        <div class="mt-6 flex justify-between text-sm text-gray-600">
            <div>Menampilkan <strong>{{ $unitKemitraans->count() }}</strong> dari {{ $unitKemitraans->total() }} data</div>
            <div>{{ $unitKemitraans->links() }}</div>
        </div>
        @endif

    </div>
</body>
</html>