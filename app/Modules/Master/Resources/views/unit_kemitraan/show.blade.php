@extends('layouts.panel')

@section('title', 'Detail Unit - ' . ($unitKemitraan->bimba_aiueo_unit ?? $unitKemitraan->no_cab))

@section('content')

    @php
        // Format aman: tanggal dipakai langsung, teks apa adanya, kosong jadi "-"
        $fmt = fn ($v, $f = 'd M Y') => $v instanceof \DateTimeInterface ? $v->format($f) : (filled($v) ? $v : '-');
        $uang = fn ($v) => filled($v) ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-';
        $val = fn ($v) => filled($v) ? $v : '-';

        $ops = trim((string) $unitKemitraan->ops);

        // Tiap seksi: judul => daftar [label, nilai]
        $seksi = [
            'Identitas Unit' => [
                ['ID Record', $val($unitKemitraan->id_record)],
                ['No Cab', $val($unitKemitraan->no_cab)],
                ['biMBA AIUEO Unit', $val($unitKemitraan->bimba_aiueo_unit)],
                ['Jenis Unit', $val($unitKemitraan->status)],
                ['Mitra Pengelolaan', $val($unitKemitraan->mitra_pengelolaan)],
                ['No Telp Unit', $val($unitKemitraan->no_telp_unit)],
                ['Email Unit', $val($unitKemitraan->email_unit)],
            ],
            'Alamat Unit' => [
                ['Alamat', $val($unitKemitraan->alamat_unit)],
                ['RT / RW', $val($unitKemitraan->rt) . ' / ' . $val($unitKemitraan->rw)],
                ['Kel/Desa', $val($unitKemitraan->kel_desa)],
                ['Kecamatan', $val($unitKemitraan->kecamatan)],
                ['Kab/Kota', $val($unitKemitraan->kab_kota)],
                ['Provinsi', $val($unitKemitraan->provinsi)],
                ['Kode Pos', $val($unitKemitraan->kode_pos)],
            ],
            'Data Mitra' => [
                ['No Induk Mitra', $val($unitKemitraan->no_induk_mitra)],
                ['Nama Mitra', $val($unitKemitraan->nama_mitra)],
                ['Email Mitra', $val($unitKemitraan->email ?: $unitKemitraan->email_mitra)],
                ['No HP Mitra', $val($unitKemitraan->no_hp ?: $unitKemitraan->no_hp_mitra)],
            ],
            'Kontrak & Lisensi' => [
                ['No Akta', $val($unitKemitraan->no_akta)],
                ['Tgl Akta', $fmt($unitKemitraan->tgl_akta)],
                ['Nilai Lisensi', $uang($unitKemitraan->nilai_lisensi)],
                ['% Mitra', $val($unitKemitraan->persen_mitra)],
                ['% YPAI', $val($unitKemitraan->persen_ypai)],
                ['Awal', $fmt($unitKemitraan->awal)],
                ['Akhir', $fmt($unitKemitraan->akhir)],
                ['Perpanjang', $fmt($unitKemitraan->perpanjang)],
                ['Tutup', $fmt($unitKemitraan->tutup)],
            ],
            'Rekening & Virtual Account' => [
                ['Bank', $val($unitKemitraan->bank)],
                ['No Rekening', $val($unitKemitraan->no_rekening)],
                ['Atas Nama', $val($unitKemitraan->atas_nama)],
                ['VA Mandiri Royalti', $val($unitKemitraan->va_mandiri_royalti)],
                ['VA Mandiri Lisensi', $val($unitKemitraan->va_mandiri_lisensi)],
            ],
            'Catatan' => [
                ['Marketing', $val($unitKemitraan->marketing)],
                ['Koorwil/KPK/Sos', $val($unitKemitraan->koorwil_kpk_sos)],
                ['Detail', $val($unitKemitraan->detail)],
                ['Note', $val($unitKemitraan->note)],
            ],
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('unit-kemitraan-user.index') }}"
               class="text-sm text-navy-950/55 hover:text-rust-600">&larr; Kembali</a>
            <h1 class="mt-1 text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">
                {{ $unitKemitraan->bimba_aiueo_unit ?? 'Detail Unit' }}
            </h1>
            <p class="mt-1 text-sm text-navy-950/55">No Cab {{ $val($unitKemitraan->no_cab) }} &middot; {{ $val($unitKemitraan->nama_mitra) }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($unitKemitraan->status_pengelolaan === 'Unit Aktif')
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Unit Aktif</span>
            @elseif ($unitKemitraan->status_pengelolaan === 'Unit Pasif')
                <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Unit Pasif</span>
            @endif
        </div>
    </div>

    {{-- ============ STATUS OPERASIONAL + AKUN BIMBA SHOP ============ --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
            <h2 class="text-sm font-semibold text-navy-950">Status Operasional</h2>
            <p class="mt-0.5 text-xs text-navy-950/50">Diambil langsung dari data unit mitra</p>
            <div class="mt-3">
                @if ($ops === '')
                    <span class="text-navy-950/40 text-sm">Belum diisi</span>
                @elseif (strcasecmp($ops, 'Active') === 0)
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">{{ $ops }}</span>
                @elseif (strcasecmp($ops, 'Closed') === 0)
                    <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-semibold">{{ $ops }}</span>
                @else
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-sm font-semibold">{{ $ops }}</span>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
            <h2 class="text-sm font-semibold text-navy-950">Akun biMBA Shop</h2>
            <p class="mt-0.5 text-xs text-navy-950/50">Dicocokkan lewat No Cab (billing last name)</p>

            @if ($akunShop)
                <dl class="mt-3 grid grid-cols-[110px_1fr] gap-y-1.5 text-sm">
                    <dt class="text-navy-950/50">First Name</dt><dd class="font-medium text-emerald-700">{{ $val($akunShop->first_name) }}</dd>
                    <dt class="text-navy-950/50">Display Name</dt><dd>{{ $val($akunShop->display_name) }}</dd>
                    <dt class="text-navy-950/50">User Login</dt><dd>{{ $val($akunShop->user_login) }}</dd>
                    <dt class="text-navy-950/50">Email</dt>
                    <dd>
                        @if ($akunShop->user_email)
                            <a href="mailto:{{ $akunShop->user_email }}" class="text-navy-700 hover:text-rust-600 hover:underline">{{ $akunShop->user_email }}</a>
                        @else - @endif
                    </dd>
                </dl>
            @else
                <div class="mt-3 rounded-xl border border-rust-500/30 bg-rust-500/10 px-4 py-3 text-sm text-rust-600">
                    Data tidak ditemukan di akun biMBA Shop. No Cab
                    <span class="font-semibold">{{ $val($unitKemitraan->no_cab) }}</span>
                    belum cocok dengan akun mana pun.
                </div>
            @endif
        </div>
    </div>

    {{-- ============ DETAIL PER SEKSI ============ --}}
    <div class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-4">
        @foreach ($seksi as $judul => $baris)
            <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
                <h2 class="text-sm font-semibold text-navy-950">{{ $judul }}</h2>
                <dl class="mt-3 grid grid-cols-[160px_1fr] gap-y-2 text-sm">
                    @foreach ($baris as [$label, $nilai])
                        <dt class="text-navy-950/50">{{ $label }}</dt>
                        <dd class="text-navy-950 break-words">{{ $nilai }}</dd>
                    @endforeach
                </dl>
            </div>
        @endforeach
    </div>

@endsection
