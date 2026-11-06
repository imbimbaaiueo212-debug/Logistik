@extends('layouts.panel')

@section('title', 'Kalender PUW')

@push('styles')
<style>
    .puw-wrap { overflow: auto; max-height: 68vh; }
    .puw-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 13px; }
    .puw-table th { position: sticky; top: 0; z-index: 2; background: #162749; color: #fff; text-align: left; padding: 10px 12px; white-space: nowrap; font-weight: 600; }
    .puw-table td { padding: 9px 12px; border-bottom: 1px solid #eef0f4; white-space: nowrap; background: #fff; }
    .puw-table tr:hover td { background: #f7f8fb; }
    .puw-table .c-alamat { white-space: normal; min-width: 260px; max-width: 360px; }
    .puw-table th:first-child, .puw-table td:first-child { position: sticky; left: 0; z-index: 1; }
    .puw-table th:first-child { z-index: 3; }
    .puw-table th.col-qty, .puw-table td.col-qty { text-align: center; }
    .puw-table th.col-qty { background: #E85D2A; }
    .pager nav p { display: none; }
</style>
@endpush

@section('content')
@include('partials.flash')

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

@include('partials.page-header', [
    'title'    => 'Kalender PUW',
    'subtitle' => $periode ? 'Daftar unit untuk souvenir kalender ' . $periode->edisi . ' (tahun ' . $periode->tahun . ')' : 'Belum ada edisi. Buat edisi pertama.',
    'primary'  => ['url' => '#', 'label' => 'Buat Edisi Baru', 'icon' => 'plus'],
])

{{-- Tombol "Buat Edisi Baru" dari page-header membuka modal --}}
@if($periode)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex flex-wrap items-end gap-4">
    <form method="GET" action="{{ route('kalender.puw') }}" class="flex flex-wrap items-end gap-4 flex-1">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Edisi</label>
            <select name="periode" class="border border-gray-300 rounded-xl px-4 py-2.5 bg-white" onchange="this.form.submit()">
                @foreach($daftarPeriode as $p)
                    <option value="{{ $p->id }}" {{ $periode->id === $p->id ? 'selected' : '' }}>{{ $p->edisi }} (kalender {{ $p->tahun }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[220px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama unit, No Cab, Kab/Kota, Kecamatan"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tampilkan</label>
            <select name="per_page" class="border border-gray-300 rounded-xl px-4 py-2.5 bg-white">
                @foreach([25, 50, 100, 200, 500] as $n)
                    <option value="{{ $n }}" {{ $perPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#162749] text-white font-semibold hover:bg-[#1D3361]">Terapkan</button>
        <a href="{{ route('kalender.puw', ['periode' => $periode->id]) }}" class="py-2.5 text-gray-500 hover:text-gray-700">Reset</a>
    </form>

    {{-- Qty berlaku untuk semua unit pada edisi ini dan tersimpan --}}
    <form method="POST" action="{{ route('kalender.puw.qty', $periode->id) }}">
        @csrf @method('PUT')
        <label class="block text-sm font-medium text-gray-700 mb-1">Qty {{ $periode->edisi }} (semua unit)</label>
        <select name="qty" class="border border-gray-300 rounded-xl px-4 py-2.5 bg-white" onchange="this.form.submit()">
            @foreach($qtyPilihan as $q)
                <option value="{{ $q }}" {{ (int) $periode->qty === $q ? 'selected' : '' }}>{{ $q }}</option>
            @endforeach
        </select>
    </form>

    <form method="POST" action="{{ route('kalender.puw.refresh', $periode->id) }}"
          onsubmit="return confirm('Tarik ulang data unit {{ $periode->edisi }} dari Database Unit? Data tabel edisi ini diganti.')">
        @csrf
        <button type="submit" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">Tarik Ulang Data</button>
    </form>

    <form method="POST" action="{{ route('kalender.puw.destroy', $periode->id) }}"
          onsubmit="return confirm('Hapus edisi {{ $periode->edisi }} beserta datanya?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-4 py-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50">Hapus Edisi</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-sm text-gray-500">Jumlah unit</p>
        <p class="text-2xl font-bold text-[#162749]">{{ number_format($totalUnit, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-sm text-gray-500">Qty per unit</p>
        <p class="text-2xl font-bold text-[#162749]">{{ $periode->qty }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-sm text-gray-500">Total souvenir {{ $periode->edisi }} untuk PUW</p>
        <p class="text-2xl font-bold text-[#E85D2A]">{{ number_format($totalQty, 0, ',', '.') }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="puw-wrap rounded-2xl">
        <table class="puw-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Cab</th>
                    <th>biMBA AIUEO Unit</th>
                    <th>Status</th>
                    <th>Ops</th>
                    <th>No Telp Unit</th>
                    <th>Alamat Unit</th>
                    <th>Rt</th>
                    <th>Rw</th>
                    <th>Provinsi</th>
                    <th>Kab/Kota</th>
                    <th>Kecamatan</th>
                    <th>Kel/Desa</th>
                    <th>Kode Pos</th>
                    <th class="col-qty">Qty {{ $periode->edisi }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $u)
                    <tr>
                        <td>{{ $u->no }}</td>
                        <td>{{ $u->no_cab }}</td>
                        <td class="font-medium">{{ $u->nama_unit }}</td>
                        <td>{{ $u->status }}</td>
                        <td>{{ $u->ops }}</td>
                        <td>{{ $u->no_telp_unit }}</td>
                        <td class="c-alamat">{{ $u->alamat_unit }}</td>
                        <td>{{ $u->rt }}</td>
                        <td>{{ $u->rw }}</td>
                        <td>{{ $u->provinsi }}</td>
                        <td>{{ $u->kab_kota }}</td>
                        <td>{{ $u->kecamatan }}</td>
                        <td>{{ $u->kel_desa }}</td>
                        <td>{{ $u->kode_pos }}</td>
                        <td class="col-qty font-semibold">{{ $u->qty }}</td>
                    </tr>
                @empty
                    <tr><td colspan="15" class="text-center text-gray-500 py-10">Tidak ada unit yang cocok. Cek Jenis Unit dan No Induk Mitra, lalu klik Tarik Ulang Data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 pager">{{ $units->links('pagination::tailwind') }}</div>
@endif

{{-- Modal: Buat Edisi Baru --}}
<div id="modalEdisi" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-auto">
        <form method="POST" action="{{ route('kalender.puw.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-[#162749]">Buat Edisi Baru</h2>
                <button type="button" data-tutup class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-gray-500">Data unit ditarik dari Database Unit lalu disimpan untuk edisi ini. Edisi lama tidak ikut berubah.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Edisi</label>
                    <input type="text" name="edisi" value="{{ old('edisi', 'KLD' . substr((string) $tahunUsulan, -2)) }}" maxlength="5"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 uppercase" placeholder="KLD27">
                    <p class="text-xs text-gray-400 mt-1">KLD27 = kalender 2027</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qty per unit</label>
                    <select name="qty" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white">
                        @foreach($qtyPilihan as $q)
                            <option value="{{ $q }}" {{ $q === 2 ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No Induk Mitra</label>
                <select name="no_induk_mitra" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-white">
                    @foreach($opsiMitra as $m)
                        <option value="{{ $m['nilai'] }}" {{ $m['pilih'] ? 'selected' : '' }}>{{ $m['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Unit</label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-auto border border-gray-200 rounded-xl p-3">
                    @foreach($opsiJenisUnit as $j)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="jenis_unit[]" value="{{ $j['nilai'] }}" {{ $j['pilih'] ? 'checked' : '' }}>
                            {{ $j['nilai'] }}
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-1">Yang mengandung angka 1 sudah tercentang.</p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="hanya_aktif" value="1" checked> Hanya unit dengan Ops Active
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" data-tutup class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#E85D2A] text-white font-semibold hover:bg-[#D14E1F]">Buat dan Tarik Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('modalEdisi');
        const buka  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
        const tutup = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };

        // Tombol "Buat Edisi Baru" dari page-header
        document.querySelectorAll('a').forEach(a => {
            if (a.textContent.trim() === 'Buat Edisi Baru') {
                a.addEventListener('click', e => { e.preventDefault(); buka(); });
            }
        });
        modal.querySelectorAll('[data-tutup]').forEach(b => b.addEventListener('click', tutup));
        modal.addEventListener('click', e => { if (e.target === modal) tutup(); });

        @if($errors->any() || !$periode)
            buka();
        @endif
    })();
</script>
@endpush
