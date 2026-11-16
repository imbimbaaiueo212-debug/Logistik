@extends('layouts.panel')

@section('title', 'Report Pengeluaran Logistik')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    /* ===== Report Pengeluaran Logistik - tampilan simpel (scoped di .fx) ===== */
    .fx {
        --tx: #162749; --mut: #64718F; --dim: #98A2BC;
        --line: #E4E9F2; --line-2: #D3DAE8; --soft: #F5F7FB;
        --cy: #0E7490; --or: #E85D2A; --navy: #162749;
        --ok: #0F9D6B; --bad: #D93A55;
        --mono: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
        --disp: 'Space Grotesk', 'Poppins', sans-serif;
        color: var(--tx);
    }
    .fx .mono { font-family: var(--mono); font-variant-numeric: tabular-nums; }
    .fx .disp { font-family: var(--disp); }

    .fx-eyebrow { display: inline-flex; align-items: center; gap: .45rem; font: 500 .68rem var(--mono); letter-spacing: .16em; text-transform: uppercase; color: var(--mut); }
    .fx-dot { width: .4rem; height: .4rem; border-radius: 9999px; background: var(--ok); }
    .fx-title { font: 700 clamp(1.5rem, 2.3vw, 1.9rem)/1.15 var(--disp); letter-spacing: -.02em; color: var(--navy); }

    .fx-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; border-radius: .7rem; padding: .55rem 1.05rem; font: 600 .82rem var(--disp); cursor: pointer; transition: background .18s, border-color .18s, color .18s; }
    .fx-btn-primary { color: #fff; background: var(--or); }
    .fx-btn-primary:hover { background: #D14E1F; }
    .fx-btn-cy { color: #fff; background: var(--navy); }
    .fx-btn-cy:hover { background: #1D3361; }
    .fx-btn-ghost { color: var(--mut); background: transparent; }
    .fx-btn-ghost:hover { color: var(--tx); background: var(--soft); }

    .fx-label { display: block; margin-bottom: .3rem; font: 500 .64rem var(--mono); letter-spacing: .12em; text-transform: uppercase; color: var(--mut); }
    .fx-input { width: 100%; color: var(--tx); background: #fff; border: 1px solid var(--line-2); border-radius: .7rem; padding: .5rem .75rem; font-size: .85rem; }
    .fx-input:focus { outline: none; border-color: var(--or); box-shadow: 0 0 0 3px rgba(232, 93, 42, .15); }
    .fx-input::placeholder { color: var(--dim); }

    .fx-chip { display: inline-flex; align-items: center; border-radius: 9999px; padding: .25rem .8rem; font: 500 .72rem var(--mono); color: var(--mut); border: 1px solid var(--line-2); background: #fff; }
    .fx-chip:hover { color: var(--tx); border-color: var(--navy); }
    .fx-chip.on { color: #fff; background: var(--navy); border-color: var(--navy); }

    .fx-card { background: #fff; border: 1px solid var(--line); border-radius: 1rem; box-shadow: 0 1px 2px rgba(22, 39, 73, .04); }
    .fx-sec-title { font: 600 1rem var(--disp); color: var(--navy); }
    .fx-sub { font-size: .75rem; color: var(--mut); }

    /* KPI: garis aksen tipis di atas */
    .fx-kpi { position: relative; padding: 1.05rem 1.2rem; overflow: hidden; }
    .fx-kpi::before { content: ""; position: absolute; left: 0; top: 0; right: 0; height: 2px; background: var(--acc, var(--line-2)); }
    .fx-kpi .k-label { font: 500 .64rem var(--mono); letter-spacing: .13em; text-transform: uppercase; color: var(--mut); }
    .fx-kpi .k-val { margin-top: .35rem; font: 600 clamp(1.5rem, 2.1vw, 1.95rem)/1.05 var(--mono); letter-spacing: -.02em; color: var(--navy); }
    .fx-kpi .k-note { margin-top: .4rem; font-size: .72rem; color: var(--dim); }
    .fx-kpi-main { background: var(--navy); border-color: var(--navy); }
    .fx-kpi-main .k-label, .fx-kpi-main .k-note { color: rgba(255, 255, 255, .65); }
    .fx-kpi-main .k-val { color: #fff; }

    .fx-track { height: .28rem; border-radius: 9999px; background: var(--soft); overflow: hidden; }
    .fx-bar { height: 100%; border-radius: 9999px; }
    .fx-bar.cy { background: var(--navy); }
    .fx-bar.or { background: var(--or); }

    .fx-badge { display: inline-flex; border-radius: 9999px; padding: .1rem .55rem; font: 600 .7rem var(--mono); border: 1px solid; }
    .fx-badge.neg { color: var(--bad); border-color: #F6C6CF; background: #FFF4F6; }
    .fx-badge.pos { color: var(--ok);  border-color: #BDEBD9; background: #F1FBF6; }
    .fx-badge.nol { color: var(--mut); border-color: var(--line-2); background: var(--soft); }
    .fx-neg { color: var(--bad); }

    .fx table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .fx thead th { position: sticky; top: 0; z-index: 10; background: var(--soft); color: var(--mut); font: 500 .64rem var(--mono); letter-spacing: .1em; text-transform: uppercase; border-bottom: 1px solid var(--line); }
    .fx tbody td { border-top: 1px solid var(--line); }
    .fx tbody tr:hover td { background: #FAFBFE; }
    .fx tfoot td { position: sticky; bottom: 0; background: var(--navy); color: #fff; font-weight: 600; }
    .fx .sticky-l { position: sticky; left: 0; background: #fff; z-index: 1; }
    .fx thead .sticky-l { background: var(--soft); z-index: 11; }
    .fx tfoot .sticky-l { background: var(--navy); }
    .fx tbody tr:hover td.sticky-l { background: #FAFBFE; }

    .fx-head { background: var(--soft); border-bottom: 1px solid var(--line); font: 500 .64rem var(--mono); letter-spacing: .1em; text-transform: uppercase; color: var(--mut); }
    .fx-grup { background: var(--soft); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
    .fx-item { border-top: 1px solid var(--line); }
    .fx-item > summary:hover { background: #FAFBFE; }
    .fx-item[open] { box-shadow: inset 2px 0 0 var(--or); }
    .fx-item[open] > summary .nm { color: var(--or); }
    .fx-panel { border-top: 1px solid var(--line); background: var(--soft); }
    .fx-mini { border: 1px solid var(--line); border-radius: .8rem; background: #fff; overflow: auto; }

    .fx-mode { display: inline-flex; padding: .15rem; border-radius: .7rem; border: 1px solid var(--line-2); background: #fff; }
    .fx-mode button { padding: .3rem .75rem; border-radius: .5rem; font: 500 .72rem var(--mono); color: var(--mut); cursor: pointer; }
    .fx-mode button:hover { color: var(--tx); }
    .fx-mode button.on { color: #fff; background: var(--navy); }

    .fx-tag { display: inline-flex; align-items: center; gap: .35rem; border-radius: 9999px; padding: .1rem .6rem; font-size: .72rem; color: var(--mut); background: #fff; border: 1px solid var(--line); }
    .fx-tag b { color: var(--navy); font-family: var(--mono); font-weight: 600; }

    .fx-warn { border: 1px solid #F3D9A0; background: #FFFAEE; color: #7A5200; border-radius: 1rem; }
    .fx-info { border: 1px solid #BFE0EA; background: #F0FAFD; color: #0E5A70; }

    /* dropdown bagian */
    .fx-dd > summary, .fx-dd2 > summary { cursor: pointer; list-style: none; }
    .fx-dd > summary::-webkit-details-marker, .fx-dd2 > summary::-webkit-details-marker { display: none; }
    .fx-dd[open] > summary { border-bottom: 1px solid var(--line); }
    .fx-dd > summary:hover, .fx-dd2 > summary:hover { background: #FAFBFE; }
    .fx-dd[open] > summary .dd-chev, .fx-dd2[open] > summary .dd-chev { transform: rotate(90deg); }
    .fx-dd2 > summary { padding: .45rem .6rem; margin-bottom: .5rem; border: 1px solid var(--line); border-radius: .7rem; background: #fff; }
    .fx a:focus-visible, .fx button:focus-visible, .fx summary:focus-visible, .fx input:focus-visible, .fx select:focus-visible { outline: 2px solid var(--or); outline-offset: 2px; }
</style>
@endpush

@section('content')
@include('partials.flash')

@php
    $fmt   = fn ($n) => number_format((int) $n);
    $tanda = fn ($v) => $v > 0 ? '+' . number_format($v) : number_format($v);
    $badge = fn ($v) => $v < 0 ? 'neg' : ($v > 0 ? 'pos' : 'nol');
    $kolom = 'md:grid-cols-[minmax(0,1fr)_7.5rem_7.5rem_7.5rem]';

    // Pilihan periode cepat (tetap membawa filter stokis dan kategori)
    $linkPeriode = fn ($d, $s) => route('pengeluaran-logistik.index', array_filter([
        'dari' => $d, 'sampai' => $s, 'stokis' => $filter['stokis'], 'kategori' => $filter['kategori'],
    ], 'filled'));
    $bulanLalu = now()->subMonthNoOverflow();
    $periodeCepat = [
        'Bulan ini'        => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        'Bulan lalu'       => [$bulanLalu->copy()->startOfMonth()->toDateString(), $bulanLalu->copy()->endOfMonth()->toDateString()],
        '30 hari terakhir' => [now()->subDays(29)->toDateString(), now()->toDateString()],
    ];
    $semuaTgl = blank($filter['dari']) && blank($filter['sampai']);

    // Ringkasan kategori: urut dari yang terbesar
    $kategoriUrut = collect($report['kategori_semua'])->sortByDesc(fn ($v) => max($v['lg'] ?? 0, $v['unit'] ?? 0));
    $maxLg   = max(1, (int) $kategoriUrut->max(fn ($v) => $v['lg'] ?? 0));
    $maxUnit = max(1, (int) $kategoriUrut->max(fn ($v) => $v['unit'] ?? 0));
    $totalSelisih = $report['total_lg'] - $report['total_unit'];

    // Rasio terjual ke unit terhadap yang dipesan dari Logistik (maks 100 untuk cincin)
    $rasio = $report['total_lg'] > 0 ? min(100, round($report['total_unit'] / $report['total_lg'] * 100)) : 0;
@endphp

<div class="fx">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="min-w-0">
            <span class="fx-eyebrow"><span class="fx-dot"></span>Logistik / Report</span>
            <h2 class="fx-title mt-1.5">Report Pengeluaran Logistik</h2>
            <p class="mt-2 text-sm max-w-3xl" style="color: var(--mut)">Stokis yang order ke Logistik, isi ordernya per kategori, dan unit yang membeli ke stokis (di luar Kalender dan Majalah)</p>
        </div>
        <a href="{{ route('pengeluaran-logistik.transfer.index') }}" class="fx-btn fx-btn-primary">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Transfer Gudang
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="fx-card p-4 md:p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.2fr_1.2fr_auto] gap-3 items-end">
            <div>
                <label for="f-dari" class="fx-label">Tanggal order dari</label>
                <input id="f-dari" type="date" name="dari" value="{{ $filter['dari'] }}" class="fx-input">
            </div>
            <div>
                <label for="f-sampai" class="fx-label">Sampai</label>
                <input id="f-sampai" type="date" name="sampai" value="{{ $filter['sampai'] }}" class="fx-input">
            </div>
            <div>
                <label for="f-stokis" class="fx-label">Stokis</label>
                <select id="f-stokis" name="stokis" class="fx-input">
                    <option value="">Semua stokis</option>
                    @foreach($daftarStokis as $kode => $nama)
                        <option value="{{ $kode }}" {{ $filter['stokis'] === $kode ? 'selected' : '' }}>{{ $nama }} ({{ $kode }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-kategori" class="fx-label">Kategori produk</label>
                <select id="f-kategori" name="kategori" class="fx-input">
                    <option value="">Semua kategori</option>
                    @foreach($daftarKategori as $k)
                        <option value="{{ $k }}" {{ $filter['kategori'] === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                    <option value="Tanpa Kategori" {{ $filter['kategori'] === 'Tanpa Kategori' ? 'selected' : '' }}>Tanpa Kategori</option>
                </select>
            </div>
            <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="fx-btn fx-btn-cy flex-1 lg:flex-none">Terapkan</button>
                <a href="{{ route('pengeluaran-logistik.index') }}" class="fx-btn fx-btn-ghost">Reset</a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-4 pt-4" style="border-top: 1px solid var(--line)">
            <span class="fx-label !mb-0 mr-1">Periode cepat</span>
            <a href="{{ $linkPeriode(null, null) }}" class="fx-chip {{ $semuaTgl ? 'on' : '' }}">Semua tanggal</a>
            @foreach($periodeCepat as $label => [$d, $s])
                <a href="{{ $linkPeriode($d, $s) }}" class="fx-chip {{ ($filter['dari'] === $d && $filter['sampai'] === $s) ? 'on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </form>

    {{-- Angka utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <div class="fx-card fx-kpi" style="--acc: var(--navy)">
            <p class="k-label">Logistik ke stokis</p>
            <p class="k-val mono">{{ $fmt($report['total_lg']) }}</p>
            <p class="k-note">qty dari order LG, {{ $fmt($report['jumlah_stokis']) }} stokis</p>
        </div>

        <div class="fx-card fx-kpi" style="--acc: var(--cy)">
            <div>
                <div class="min-w-0">
                    <p class="k-label">Stokis ke unit</p>
                    <p class="k-val mono">{{ $fmt($report['total_unit']) }}</p>
                </div>
            </div>
            <p class="k-note">penjualan stokis, tidak dijumlahkan ke total</p>
        </div>

        <div class="fx-card fx-kpi" style="--acc: var(--or)">
            <p class="k-label">Transfer gudang</p>
            <p class="k-val mono">{{ $fmt($report['total_transfer']) }}</p>
            <p class="k-note">input manual, hanya qty</p>
        </div>

        <div class="fx-card fx-kpi fx-kpi-main">
            <p class="k-label" >Total pengeluaran logistik</p>
            <p class="k-val mono">{{ $fmt($report['total_keluar']) }}</p>
            <p class="k-note" >logistik ke stokis ditambah transfer gudang</p>
        </div>
    </div>

    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <details class="text-xs max-w-2xl" style="color: var(--mut)">
            <summary class="cursor-pointer font-medium" style="color: var(--cy)">Cara membaca report ini</summary>
            <ul class="mt-2 space-y-1 list-disc ml-5 leading-relaxed">
                <li>Hanya order berstatus processing atau completed yang dihitung.</li>
                <li>Total pengeluaran logistik = logistik ke stokis + transfer gudang.</li>
                <li>Stokis ke unit adalah penjualan stokis, jadi tidak dijumlahkan ke pengeluaran logistik.</li>
                <li>Selisih = qty dari logistik dikurangi qty terjual ke unit pada rentang tanggal yang sama. Selisih minus berarti stokis menjual lebih banyak daripada yang dipesan pada periode ini, misalnya dari stok awal.</li>
            </ul>
        </details>
        @if($dinaikkan > 0)
            <p class="fx-info text-xs rounded-full px-3 py-1">{{ $dinaikkan }} order on-hold otomatis dinaikkan ke processing</p>
        @endif
    </div>

    @if(count($report['tidak_dikenal']) > 0)
        <div class="fx-warn mb-5 px-4 py-3 text-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $fmt(count($report['tidak_dikenal'])) }} order LG belum dihitung</p>
                    <p class="text-xs mt-0.5 opacity-80">Nama stokis tidak terbaca dari nama billing. Perbaiki nama billing atau tambahkan stokis ke daftar agar ordernya masuk.</p>
                    <details class="mt-2 text-xs">
                        <summary class="cursor-pointer font-medium">Lihat contoh order</summary>
                        <ul class="mt-2 list-disc ml-5 space-y-0.5 mono">
                            @foreach(array_slice($report['tidak_dikenal'], 0, 10) as $x)
                                <li>#{{ $x['order_id'] }} - {{ $x['billing'] ?: '(billing kosong)' }} - {{ $x['sku'] }} - {{ number_format($x['qty']) }}</li>
                            @endforeach
                        </ul>
                        @if(count($report['tidak_dikenal']) > 10)
                            <p class="mt-1">dan {{ number_format(count($report['tidak_dikenal']) - 10) }} order lainnya.</p>
                        @endif
                    </details>
                </div>
            </div>
        </div>
    @endif

    {{-- Ringkasan per kategori --}}
    <section class="fx-card overflow-hidden mb-6">
      <details class="fx-dd" open>
        <summary class="px-5 py-4 flex flex-wrap items-center justify-between gap-2">
            <span class="flex items-center gap-2"><svg class="dd-chev w-4 h-4 shrink-0 transition-transform" style="color: var(--mut)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg><h3 class="fx-sec-title">Ringkasan per kategori produk</h3></span>
            <p class="fx-sub">{{ $fmt($kategoriUrut->count()) }} kategori, urut dari qty terbesar</p>
        </summary>
        <div class="overflow-auto max-h-[26rem] fx-scroll">
            <table class="text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-2.5">Kategori</th>
                        <th class="text-right px-4 py-2.5 w-56"><span class="inline-flex items-center gap-1.5"><i class="inline-block w-2 h-2 rounded-full" style="background:var(--cy)"></i>Logistik ke stokis</span></th>
                        <th class="text-right px-4 py-2.5 w-56"><span class="inline-flex items-center gap-1.5"><i class="inline-block w-2 h-2 rounded-full" style="background:var(--or)"></i>Stokis ke unit</span></th>
                        <th class="text-right px-5 py-2.5 w-28">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoriUrut as $kat => $v)
                        @php $lg = (int) ($v['lg'] ?? 0); $un = (int) ($v['unit'] ?? 0); $sel = $lg - $un; @endphp
                        <tr>
                            <td class="px-5 py-2.5" style="color: var(--tx)">{{ $kat }}</td>
                            <td class="px-4 py-2.5">
                                <div class="text-right mono">{{ $fmt($lg) }}</div>
                                <div class="mt-1.5 fx-track"><div class="fx-bar cy" style="width: {{ round($lg / $maxLg * 100) }}%"></div></div>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="text-right mono">{{ $fmt($un) }}</div>
                                <div class="mt-1.5 fx-track"><div class="fx-bar or" style="width: {{ round($un / $maxUnit * 100) }}%"></div></div>
                            </td>
                            <td class="px-5 py-2.5 text-right"><span class="fx-badge {{ $badge($sel) }}">{{ $tanda($sel) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center" style="color: var(--dim)">Belum ada data pada filter ini. Coba ganti periode atau pilih Semua tanggal.</td></tr>
                    @endforelse
                </tbody>
                @if($kategoriUrut->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td class="px-5 py-2.5">Total</td>
                            <td class="px-4 py-2.5 text-right mono">{{ $fmt($report['total_lg']) }}</td>
                            <td class="px-4 py-2.5 text-right mono">{{ $fmt($report['total_unit']) }}</td>
                            <td class="px-5 py-2.5 text-right mono">{{ $tanda($totalSelisih) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
      </details>
    </section>

    {{-- Per stokis --}}
    <section class="mb-6">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-3">
            <div>
                <h3 class="fx-sec-title">Per stokis</h3>
                <p class="fx-sub mono" id="stokis-hitung">{{ $fmt($report['jumlah_stokis']) }} stokis</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2" style="color: var(--dim)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <label for="stokis-cari" class="sr-only">Cari stokis</label>
                    <input id="stokis-cari" type="search" placeholder="Cari nama atau kode stokis" class="fx-input !pl-9 w-64">
                </div>
                <div class="fx-mode" role="group" aria-label="Filter selisih">
                    <button type="button" data-mode="semua" class="mode-btn">Semua</button>
                    <button type="button" data-mode="minus" class="mode-btn">Selisih minus</button>
                    <button type="button" data-mode="plus" class="mode-btn">Selisih plus</button>
                </div>
                <button type="button" id="buka-semua" class="fx-btn fx-btn-ghost !px-3 !py-1.5 !text-xs">Buka semua</button>
                <button type="button" id="tutup-semua" class="fx-btn fx-btn-ghost !px-3 !py-1.5 !text-xs">Tutup semua</button>
            </div>
        </div>

        @if(count($report['stokis']) > 0)
            <div class="fx-card overflow-hidden">
                <div class="hidden md:grid {{ $kolom }} gap-x-3 px-4 py-2.5 fx-head">
                    <span class="pl-6">Stokis</span>
                    <span class="text-right">Dari Logistik</span>
                    <span class="text-right">Ke Unit</span>
                    <span class="text-right">Selisih</span>
                </div>

                @foreach(collect($report['stokis'])->groupBy('kelompok') as $kelompok => $daftar)
                    @php
                        $sumLg = $daftar->sum('qty_lg'); $sumUn = $daftar->sum('qty_unit'); $sumSel = $sumLg - $sumUn;
                    @endphp
                    <div class="stokis-grup">
                        <div class="grid grid-cols-3 {{ $kolom }} gap-x-3 gap-y-1 px-4 py-2 fx-grup text-sm items-center">
                            <div class="col-span-3 md:col-span-1 flex items-center gap-2">
                                <span class="w-1.5 h-4 rounded-full" style="background: var(--or)"></span>
                                <span class="font-semibold disp" style="color: var(--tx)">{{ $kelompok }}</span>
                                <span class="fx-tag">{{ $daftar->count() }} stokis</span>
                            </div>
                            <span class="md:text-right mono font-medium" style="color: var(--tx)">{{ $fmt($sumLg) }}</span>
                            <span class="md:text-right mono font-medium" style="color: var(--tx)">{{ $fmt($sumUn) }}</span>
                            <span class="md:text-right mono font-medium {{ $sumSel < 0 ? 'fx-neg' : '' }}" style="{{ $sumSel < 0 ? '' : 'color: var(--tx)' }}">{{ $tanda($sumSel) }}</span>
                        </div>

                        @foreach($daftar as $s)
                            @php
                                $katBanding = collect(array_keys($s['kategori']))
                                    ->merge(collect($s['unit'])->flatMap(fn ($u) => array_keys($u['kategori'])))
                                    ->unique()->sort()->values();
                                $kolomKat = collect($s['unit'])->flatMap(fn ($u) => array_keys($u['kategori']))->unique()->sort()->values();
                            @endphp
                            <details class="stokis-item fx-item group"
                                     data-nama="{{ strtolower($s['nama'] . ' ' . $s['kode']) }}" data-selisih="{{ $s['selisih'] }}">
                                <summary class="cursor-pointer list-none grid grid-cols-3 {{ $kolom }} gap-x-3 gap-y-1 items-center px-4 py-3 focus:outline-none [&::-webkit-details-marker]:hidden">
                                    <div class="col-span-3 md:col-span-1 flex items-center gap-2 min-w-0">
                                        <svg class="w-4 h-4 shrink-0 transition-transform group-open:rotate-90" style="color: var(--cy)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                                        <span class="nm font-medium truncate transition-colors" style="color: var(--tx)">{{ $s['nama'] }}</span>
                                        <span class="mono text-[11px] shrink-0" style="color: var(--dim)">{{ $s['kode'] }}</span>
                                    </div>
                                    <div class="md:text-right mono">
                                        <span class="md:hidden block text-[11px]" style="color: var(--dim)">Dari Logistik</span>
                                        <span class="text-sm" style="color: var(--navy); font-weight:600">{{ $fmt($s['qty_lg']) }}</span>
                                    </div>
                                    <div class="md:text-right mono">
                                        <span class="md:hidden block text-[11px]" style="color: var(--dim)">Ke Unit</span>
                                        <span class="text-sm" style="color: var(--tx)">{{ $fmt($s['qty_unit']) }}</span>
                                    </div>
                                    <div class="md:text-right">
                                        <span class="md:hidden block text-[11px]" style="color: var(--dim)">Selisih</span>
                                        <span class="fx-badge {{ $badge($s['selisih']) }}">{{ $tanda($s['selisih']) }}</span>
                                    </div>
                                </summary>

                                <div class="fx-panel px-4 md:px-6 py-5 space-y-6">
                                    {{-- Perbandingan per kategori --}}
                                    <details class="fx-dd2" open>
                                        <summary class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="flex items-center gap-2"><svg class="dd-chev w-4 h-4 shrink-0 transition-transform" style="color: var(--mut)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg><h5 class="text-sm font-semibold disp" style="color: var(--tx)">Perbandingan per kategori</h5></span>
                                            <p class="fx-sub">{{ $fmt($s['order_lg']) }} order dari Logistik. Klik kategori untuk melihat produknya.</p>
                                        </summary>
                                        @if($katBanding->isEmpty())
                                            <p class="text-xs" style="color: var(--dim)">Belum ada data pada filter ini.</p>
                                        @else
                                            <div class="fx-mini fx-scroll">
                                                <table class="text-xs">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-left px-3 py-2">Kategori</th>
                                                            <th class="text-right px-3 py-2">Dari Logistik</th>
                                                            <th class="text-right px-3 py-2">Ke Unit</th>
                                                            <th class="text-right px-3 py-2">Selisih</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($katBanding as $kat)
                                                            @php
                                                                $qLg = $s['kategori'][$kat]['qty'] ?? 0;
                                                                $qUn = collect($s['unit'])->sum(fn ($u) => $u['kategori'][$kat] ?? 0);
                                                                $sel = $qLg - $qUn;
                                                                $rid = 'p-' . $s['kode'] . '-' . $loop->index;
                                                                $produk = $s['kategori'][$kat]['produk'] ?? [];
                                                            @endphp
                                                            <tr>
                                                                <td class="px-3 py-1.5" style="color: var(--tx)">
                                                                    @if(count($produk) > 0)
                                                                        <button type="button" class="baris-toggle inline-flex items-center gap-1.5 text-left hover:text-[var(--cy)] focus:outline-none focus-visible:underline" data-target="{{ $rid }}" aria-expanded="false">
                                                                            <svg class="w-3 h-3 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                                                                            {{ $kat }} <span style="color: var(--dim)">({{ count($produk) }} produk)</span>
                                                                        </button>
                                                                    @else
                                                                        <span class="pl-[18px]">{{ $kat }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-3 py-1.5 text-right mono">{{ $fmt($qLg) }}</td>
                                                                <td class="px-3 py-1.5 text-right mono">{{ $fmt($qUn) }}</td>
                                                                <td class="px-3 py-1.5 text-right"><span class="fx-badge {{ $badge($sel) }}">{{ $tanda($sel) }}</span></td>
                                                            </tr>
                                                            @if(count($produk) > 0)
                                                                <tr id="{{ $rid }}" class="hidden">
                                                                    <td colspan="4" class="px-3 py-2" style="background: #fff">
                                                                        <table>
                                                                            @foreach($produk as $p)
                                                                                <tr>
                                                                                    <td class="!border-0 py-0.5 pl-[18px]" style="color: var(--mut)">{{ $p['nama'] }} <span class="mono" style="color: var(--dim)">{{ $p['sku'] }}</span></td>
                                                                                    <td class="!border-0 py-0.5 text-right mono" style="color: var(--tx)">{{ $fmt($p['qty']) }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td class="px-3 py-2">Total</td>
                                                            <td class="px-3 py-2 text-right mono">{{ $fmt($s['qty_lg']) }}</td>
                                                            <td class="px-3 py-2 text-right mono">{{ $fmt($s['qty_unit']) }}</td>
                                                            <td class="px-3 py-2 text-right mono">{{ $tanda($s['selisih']) }}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif
                                    </details>

                                    {{-- Unit yang membeli --}}
                                    <details class="fx-dd2">
                                        <summary class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="flex items-center gap-2"><svg class="dd-chev w-4 h-4 shrink-0 transition-transform" style="color: var(--mut)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg><h5 class="text-sm font-semibold disp" style="color: var(--tx)">Unit yang membeli ke stokis ini</h5></span>
                                            <p class="fx-sub">{{ $fmt(count($s['unit'])) }} unit, total {{ $fmt($s['qty_unit']) }}. Terurut dari qty terbesar.</p>
                                        </summary>
                                        @if(empty($s['unit']))
                                            <p class="text-xs" style="color: var(--dim)">Belum ada unit yang membeli pada filter ini.</p>
                                        @else
                                            <div class="fx-mini fx-scroll max-h-96">
                                                <table class="text-xs">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-left px-3 py-2 sticky-l">Unit</th>
                                                            <th class="text-left px-3 py-2">Ref</th>
                                                            @foreach($kolomKat as $kat)
                                                                <th class="text-right px-3 py-2 whitespace-nowrap">{{ $kat }}</th>
                                                            @endforeach
                                                            <th class="text-right px-3 py-2" style="color: var(--navy)">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($s['unit'] as $u)
                                                            <tr>
                                                                <td class="px-3 py-1.5 whitespace-nowrap sticky-l" style="color: var(--tx)">{{ $u['nama'] }}</td>
                                                                <td class="px-3 py-1.5 mono" style="color: var(--dim)">{{ $u['ref'] ?: '-' }}</td>
                                                                @foreach($kolomKat as $kat)
                                                                    <td class="px-3 py-1.5 text-right mono" style="{{ isset($u['kategori'][$kat]) ? '' : 'color: var(--dim); opacity:.5' }}">{{ isset($u['kategori'][$kat]) ? $fmt($u['kategori'][$kat]) : '-' }}</td>
                                                                @endforeach
                                                                <td class="px-3 py-1.5 text-right font-semibold mono" style="color: var(--navy)">{{ $fmt($u['qty']) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td class="px-3 py-2 sticky-l" colspan="2">Total ke unit</td>
                                                            @foreach($kolomKat as $kat)
                                                                <td class="px-3 py-2 text-right mono">{{ $fmt(collect($s['unit'])->sum(fn ($u) => $u['kategori'][$kat] ?? 0)) }}</td>
                                                            @endforeach
                                                            <td class="px-3 py-2 text-right mono">{{ $fmt($s['qty_unit']) }}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @endif
                                    </details>
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endforeach

                <p id="stokis-kosong" class="hidden px-5 py-10 text-center text-sm" style="color: var(--dim)">Tidak ada stokis yang cocok. Ubah kata pencarian atau filter selisih.</p>
            </div>
        @else
            <div class="fx-card py-12 px-6 text-center text-sm" style="border-style: dashed; color: var(--dim)">
                Belum ada order stokis pada filter ini. Coba ganti periode atau pilih Semua tanggal.
            </div>
        @endif
    </section>

    {{-- Transfer gudang --}}
    <section class="fx-card overflow-hidden">
      <details class="fx-dd" open>
        <summary class="px-5 py-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2"><svg class="dd-chev w-4 h-4 shrink-0 transition-transform" style="color: var(--mut)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                <div>
                    <h3 class="fx-sec-title">Transfer gudang</h3>
                    <p class="fx-sub">Input manual, hanya qty tanpa nominal uang.</p>
                </div>
            </div>
            <a href="{{ route('pengeluaran-logistik.transfer.index') }}" class="text-sm font-semibold hover:underline" style="color: var(--or)">Input transfer</a>
        </summary>

        @forelse($report['transfer']['tujuan'] as $tujuan => $t)
            <div class="px-5 py-3 flex flex-wrap items-start justify-between gap-3" style="border-top: 1px solid var(--line)">
                <div class="min-w-0">
                    <p class="font-medium disp" style="color: var(--tx)">{{ $tujuan }}</p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach($t['kategori'] as $kat => $qty)
                            <span class="fx-tag">{{ $kat }} <b>{{ $fmt($qty) }}</b></span>
                        @endforeach
                    </div>
                </div>
                <p class="text-xl font-semibold mono" style="color: var(--navy)">{{ $fmt($t['qty']) }}</p>
            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <p class="text-sm" style="color: var(--mut)">Belum ada transfer gudang pada filter ini.</p>
                <a href="{{ route('pengeluaran-logistik.transfer.index') }}" class="fx-btn fx-btn-primary mt-3">Input transfer</a>
            </div>
        @endforelse
      </details>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var items  = Array.prototype.slice.call(document.querySelectorAll('.stokis-item'));
    var cari   = document.getElementById('stokis-cari');
    var hitung = document.getElementById('stokis-hitung');
    var kosong = document.getElementById('stokis-kosong');
    var btns   = Array.prototype.slice.call(document.querySelectorAll('.mode-btn'));
    var mode   = 'semua';

    function gayaTombol() {
        btns.forEach(function (b) {
            var aktif = b.getAttribute('data-mode') === mode;
            b.classList.toggle('on', aktif);
            b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
        });
    }

    function terapkan() {
        var q = (cari ? cari.value : '').trim().toLowerCase();
        var tampil = 0;
        items.forEach(function (el) {
            var sel = parseInt(el.getAttribute('data-selisih'), 10) || 0;
            var cocokNama = q === '' || el.getAttribute('data-nama').indexOf(q) !== -1;
            var cocokMode = mode === 'semua' || (mode === 'minus' && sel < 0) || (mode === 'plus' && sel > 0);
            var ok = cocokNama && cocokMode;
            el.classList.toggle('hidden', !ok);
            if (ok) { tampil++; }
        });
        document.querySelectorAll('.stokis-grup').forEach(function (g) {
            var ada = g.querySelector('.stokis-item:not(.hidden)');
            g.classList.toggle('hidden', !ada);
        });
        if (hitung) { hitung.textContent = tampil + ' dari ' + items.length + ' stokis'; }
        if (kosong) { kosong.classList.toggle('hidden', tampil !== 0 || items.length === 0); }
    }

    if (cari) { cari.addEventListener('input', terapkan); }
    btns.forEach(function (b) {
        b.addEventListener('click', function () { mode = b.getAttribute('data-mode'); gayaTombol(); terapkan(); });
    });

    var buka = document.getElementById('buka-semua');
    var tutup = document.getElementById('tutup-semua');
    if (buka)  { buka.addEventListener('click',  function () { items.forEach(function (el) { if (!el.classList.contains('hidden')) { el.open = true; } }); }); }
    if (tutup) { tutup.addEventListener('click', function () { items.forEach(function (el) { el.open = false; }); }); }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('.baris-toggle');
        if (!b) { return; }
        var tr = document.getElementById(b.getAttribute('data-target'));
        if (!tr) { return; }
        var terbuka = tr.classList.toggle('hidden') === false;
        b.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        var ikon = b.querySelector('svg');
        if (ikon) { ikon.classList.toggle('rotate-90', terbuka); }
    });

    gayaTombol();
    terapkan();
})();
</script>
@endpush