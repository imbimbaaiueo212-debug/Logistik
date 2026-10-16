@extends('layouts.panel')

@section('title', 'Unit + User Matching')

@push('styles')
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 1500px;
        font-size: 0.8125rem;
    }
    .data-table th,
    .data-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
        border-bottom: 1px solid rgba(15, 27, 51, 0.06);
    }
    .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #F4F6FA;
        color: rgba(15, 27, 51, 0.6);
        font-weight: 600;
        font-size: 0.75rem;
        border-bottom: 1px solid rgba(15, 27, 51, 0.1);
    }
    .data-table tbody tr:hover td { background-color: #F7F9FC; }
    .data-table .ctr { text-align: center; }

    /* Teks panjang dipotong "...", isi lengkap muncul saat kursor diarahkan (atribut title) */
    .cell-clip { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }

    /* Kolom pertama dan Aksi tetap terlihat saat tabel digeser ke samping */
    .data-table .sticky-l { position: sticky; left: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-l { z-index: 30; background: #F4F6FA; }
    .data-table .sticky-r { position: sticky; right: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-r { z-index: 30; background: #F4F6FA; }
    .data-table tbody tr:hover .sticky-l,
    .data-table tbody tr:hover .sticky-r { background-color: #F7F9FC; }

    /* Sembunyikan teks "Showing ..." bawaan pagination Laravel */
    .pager nav p { display: none; }
</style>
@endpush

@section('content')

    @php
        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-xs font-medium text-navy-950/60 mb-1.5';
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Unit + User Matching</h1>
            <p class="mt-1 text-sm text-navy-950/55">Mencocokkan No Cab unit kemitraan dengan data user biMBA Shop</p>
        </div>

        <form action="{{ route('unit-kemitraan-user.generate-match') }}" method="POST"
              onsubmit="return confirm('Generate matching untuk semua unit yang belum match?')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 20 11-11"/><path d="m13.5 4.5 1 2 2 1-2 1-1 2-1-2-2-1 2-1 1-2Z"/><path d="M18.5 14l.6 1.4 1.4.6-1.4.6-.6 1.4-.6-1.4-1.4-.6 1.4-.6.6-1.4Z"/></svg>
                Generate Match Otomatis
            </button>
        </form>
    </div>

    {{-- ============ NOTIFIKASI ============ --}}
    @if (session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

            <div>
                <label for="f_no_cab" class="{{ $lbl }}">No Cab</label>
                <input id="f_no_cab" type="text" name="no_cab" value="{{ request('no_cab') }}" placeholder="Cari No Cab" class="{{ $inp }}">
            </div>

            <div>
                <label for="f_nim" class="{{ $lbl }}">NIM / No Induk Mitra</label>
                <input id="f_nim" type="text" name="no_induk_mitra" value="{{ request('no_induk_mitra') }}" placeholder="Cari NIM" class="{{ $inp }}">
            </div>

            <div>
                <label for="f_status_pengelolaan" class="{{ $lbl }}">Status Pengelolaan</label>
                <select id="f_status_pengelolaan" name="status_pengelolaan" class="{{ $inp }}">
                    <option value="">Semua</option>
                    @foreach (['Unit Aktif' => 'Unit Aktif', 'Unit Pasif' => 'Unit Pasif', 'all' => 'Tampilkan semua'] as $nilai => $teks)
                        <option value="{{ $nilai }}" {{ request('status_pengelolaan') == $nilai ? 'selected' : '' }}>{{ $teks }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_mitra_pengelolaan" class="{{ $lbl }}">Mitra Pengelolaan</label>
                <select id="f_mitra_pengelolaan" name="mitra_pengelolaan" class="{{ $inp }}">
                    <option value="">Semua</option>
                    @foreach (['YPAI', 'PUW1 | OPS1'] as $nilai)
                        <option value="{{ $nilai }}" {{ request('mitra_pengelolaan') == $nilai ? 'selected' : '' }}>{{ $nilai }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_status" class="{{ $lbl }}">Status</label>
                <select id="f_status" name="status" class="{{ $inp }}">
                    <option value="">Semua status</option>
                    @foreach (['MM', 'MM 1', 'Aktif 1', 'MK 1', 'MK', 'MK Rinda', 'MKU', 'MKU 1', 'E-biMBA Aktif'] as $nilai)
                        <option value="{{ $nilai }}" {{ request('status') == $nilai ? 'selected' : '' }}>{{ $nilai }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_matching" class="{{ $lbl }}">Matching First Name</label>
                <select id="f_matching" name="matching_status" class="{{ $inp }}">
                    <option value="">Semua</option>
                    <option value="ditemukan" {{ request('matching_status') == 'ditemukan' ? 'selected' : '' }}>Ditemukan</option>
                    <option value="tidak_ditemukan" {{ request('matching_status') == 'tidak_ditemukan' ? 'selected' : '' }}>Tidak ditemukan</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-6 flex items-center gap-2">
                <button type="submit"
                        class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                    Terapkan filter
                </button>
                <a href="{{ route('unit-kemitraan-user.index') }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-4 py-2.5">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="sticky-l">No Cab</th>
                        <th>biMBA AIUEO Unit</th>
                        <th>Nama Mitra</th>
                        <th>Jenis Unit</th>
                        <th class="ctr">Status Pengelolaan</th>
                        <th>Mitra Pengelolaan</th>
                        <th>No Induk Mitra</th>
                        <th>No HP</th>
                        <th>Matching First Name</th>
                        <th>User Email</th>
                        <th>Display Name</th>
                        <th class="ctr sticky-r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitKemitraans as $unit)
                        @php
                            $noCab = trim((string) $unit->no_cab);

                            // Cari user yang nama belakang billing / nama depan / nama belakangnya memuat No Cab.
                            // No Cab kosong tidak dicocokkan (str_contains dengan teks kosong selalu true).
                            $matched = $noCab === '' ? null : $userExports->first(function ($u) use ($noCab) {
                                return str_contains(trim((string) ($u->billing_last_name ?? '')), $noCab)
                                    || str_contains(trim((string) ($u->first_name ?? '')), $noCab)
                                    || str_contains(trim((string) ($u->last_name ?? '')), $noCab);
                            });
                        @endphp
                        <tr>
                            <td class="sticky-l font-medium">{{ $unit->no_cab ?? '-' }}</td>
                            <td>{{ $unit->bimba_aiueo_unit ?? '-' }}</td>
                            <td class="cell-clip" title="{{ $unit->nama_mitra }}">{{ $unit->nama_mitra ?? '-' }}</td>
                            <td>{{ $unit->status ?? '-' }}</td>
                            <td class="ctr">
                                @if ($unit->status_pengelolaan === 'Unit Aktif')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Unit Aktif</span>
                                @elseif ($unit->status_pengelolaan === 'Unit Pasif')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Unit Pasif</span>
                                @else
                                    <span class="text-navy-950/35">-</span>
                                @endif
                            </td>
                            <td>{{ $unit->mitra_pengelolaan ?? '-' }}</td>
                            <td>{{ $unit->no_induk_mitra ?? '-' }}</td>
                            <td>{{ $unit->no_hp ?? '-' }}</td>
                            <td class="cell-clip" title="{{ $matched?->first_name }}">
                                @if ($matched)
                                    <span class="font-medium text-emerald-700">{{ $matched->first_name }}</span>
                                @else
                                    <span class="text-rust-600">Tidak ditemukan</span>
                                @endif
                            </td>
                            <td class="cell-clip" title="{{ $matched?->user_email }}">
                                @if ($matched && $matched->user_email)
                                    <a href="mailto:{{ $matched->user_email }}" class="text-navy-700 hover:text-rust-600 hover:underline">{{ $matched->user_email }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="cell-clip" title="{{ $matched?->display_name }}">{{ $matched?->display_name ?? '-' }}</td>
                            <td class="ctr sticky-r">
                                <a href="{{ route('unit-kemitraan.show', $unit) }}" title="Lihat" aria-label="Lihat unit"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-navy-700 hover:bg-navy-700/10 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="!text-center py-16 text-navy-950/50">
                                Tidak ada unit yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ PAGINATION ============ --}}
    <div class="mt-5 flex flex-col lg:flex-row items-center justify-between gap-4">
        <p class="text-sm text-navy-950/60">
            Menampilkan
            <span class="font-semibold text-navy-950">{{ number_format($unitKemitraans->firstItem() ?? 0, 0, ',', '.') }}</span>
            sampai
            <span class="font-semibold text-navy-950">{{ number_format($unitKemitraans->lastItem() ?? 0, 0, ',', '.') }}</span>
            dari
            <span class="font-semibold text-navy-950">{{ number_format($unitKemitraans->total(), 0, ',', '.') }}</span>
            data
        </p>
        <div class="pager max-w-full overflow-x-auto">
            {{ $unitKemitraans->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>

@endsection