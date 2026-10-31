@extends('layouts.panel')

@section('title', 'Ketersediaan Barang - biMBA AIUEO Logistik')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Ketersediaan Barang (Majalah &amp; Kalender)</h1>
        <p class="text-sm text-gray-500 mt-1">
            Pesanan tetap berjalan sampai Picking. Picking Majalah/Kalender tertahan selama edisi/tahunnya belum ditandai
            <b>sudah datang</b>. Satu klik membuka semua Picking terkait (Jakarta Aktif, Jakarta Pasif, Logistik, Manual).
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <div class="bg-white rounded-3xl shadow p-5 mb-6">
        <div class="font-semibold text-gray-700 mb-3">Daftarkan edisi / tahun baru (belum datang)</div>
        <form method="POST" action="{{ route('picking.barang.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Jenis</label>
                <select name="jenis" class="border border-gray-300 rounded-xl px-4 py-2.5">
                    <option value="Majalah">Majalah</option>
                    <option value="Kalender">Kalender</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Edisi (M160) atau Tahun (2027)</label>
                <input type="text" name="kode" required maxlength="20" placeholder="M160 / 2027"
                       class="border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700">Daftarkan</button>
        </form>
        <p class="text-xs text-gray-500 mt-2">Edisi/tahun yang belum terdaftar pun otomatis dianggap belum datang saat pesanannya masuk; mendaftarkan hanya agar muncul di daftar ini lebih awal.</p>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="text-left px-4 py-3">Jenis</th>
                    <th class="text-left px-4 py-3">Edisi / Tahun</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Tanggal Datang</th>
                    <th class="text-center px-4 py-3">Picking Menunggu</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($daftar as $d)
                <tr>
                    <td class="px-4 py-3">{{ $d['jenis'] }}</td>
                    <td class="px-4 py-3 font-medium">{{ $d['kode'] }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($d['status'] === 'datang')
                            <span style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:9999px;font-size:.8rem">Sudah datang</span>
                        @else
                            <span style="background:#fee2e2;color:#991b1b;padding:2px 10px;border-radius:9999px;font-size:.8rem">Belum datang</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($d['status'] === 'datang')
                            {{ $d['tgl_datang'] ? \Carbon\Carbon::parse($d['tgl_datang'])->format('d/m/Y') : '(data lama)' }}
                        @else - @endif
                    </td>
                    <td class="px-4 py-3 text-center">{{ $d['status'] === 'datang' ? '-' : $d['jumlah'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('picking.barang.update') }}"
                              onsubmit="return confirm('{{ $d['status'] === 'datang' ? 'Kembalikan ke belum datang?' : 'Tandai sudah datang? Semua Picking terkait akan terbuka.' }}')">
                            @csrf
                            <input type="hidden" name="jenis" value="{{ $d['jenis'] }}">
                            <input type="hidden" name="kode" value="{{ $d['kode'] }}">
                            @if($d['status'] === 'datang')
                                <input type="hidden" name="status" value="belum_datang">
                                <button class="text-red-600 hover:underline">Batalkan</button>
                            @else
                                <input type="hidden" name="status" value="datang">
                                <button class="bg-green-600 text-white px-4 py-1.5 rounded-xl hover:bg-green-700">Barang Sudah Datang</button>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-12 text-gray-500">Belum ada edisi/tahun. Data muncul otomatis saat pesanan Majalah/Kalender masuk ke Picking.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
