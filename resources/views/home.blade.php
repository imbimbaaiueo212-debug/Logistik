<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>biMBA Logistik Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            950: '#0F1B33',
                            900: '#162749',
                            800: '#1D3361',
                            700: '#28447F',
                            600: '#3A548A',
                        },
                        rust: {
                            500: '#E85D2A',
                            600: '#D14E1F',
                        },
                        canvas: '#EEF1F6',
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(15, 27, 51, 0.05), 0 10px 26px -14px rgba(15, 27, 51, 0.16)',
                    },
                }
            }
        }
    </script>
    <style>
        body { background: #EEF1F6; }

        .hero-grid {
            background-image: radial-gradient(rgba(255,255,255,0.55) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .hero-glow {
            background: radial-gradient(circle, rgba(232,93,42,0.35), transparent 70%);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-fade-up { opacity: 0; animation: fadeInUp 0.65s cubic-bezier(.22,.9,.32,1) forwards; }

        .reveal {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .reveal.is-visible { opacity: 1; transform: translateY(0); }

        @media (prefers-reduced-motion: reduce) {
            .anim-fade-up { animation: none !important; opacity: 1; }
            .reveal { opacity: 1; transform: none; transition: none; }
        }

        a:focus-visible { outline: 2px solid #E85D2A; outline-offset: 2px; }

        /* ===== Alur kerja: kartu tahap + panah penghubung ===== */
        .stage-card {
            position: relative;
            display: block;
            background: #fff;
            border-radius: 16px;
            padding: 16px 16px 14px;
            border-top: 3px solid #28447F;
            box-shadow: 0 1px 2px rgba(15, 27, 51, 0.05), 0 10px 26px -14px rgba(15, 27, 51, 0.16);
        }
        .stage-card.is-hot { border-top-color: #E85D2A; }
        .stage-card .stage-go { color: rgba(15, 27, 51, 0.25); transition: color .15s ease, transform .15s ease; }
        .stage-card:hover .stage-go { color: #E85D2A; transform: translateX(2px); }
        @media (min-width: 1280px) {
            .stage-card:not(:last-child)::after {
                content: '';
                position: absolute; right: -11px; top: 50%;
                width: 8px; height: 8px;
                border-top: 2px solid rgba(15, 27, 51, 0.28);
                border-right: 2px solid rgba(15, 27, 51, 0.28);
                transform: translateY(-50%) rotate(45deg);
            }
        }

        /* ===== Baris daftar (perlu perhatian, rincian sumber) ===== */
        .row-link { transition: background-color .15s ease; }
        .row-link:hover { background-color: rgba(15, 27, 51, 0.03); }

        /* ===== Ubin angka (gudang, mitra, periode) ===== */
        .tile { display: block; background: #fff; border-radius: 14px; padding: 14px 16px;
                box-shadow: 0 1px 2px rgba(15, 27, 51, 0.05), 0 10px 26px -14px rgba(15, 27, 51, 0.16);
                border-left: 3px solid rgba(40, 68, 127, 0.25); transition: border-color .15s ease; }
        .tile:hover { border-left-color: #E85D2A; }

        /* ===== Batang tren order: tumbuh dari bawah sekali saat halaman dibuka ===== */
        @keyframes growBar { from { transform: scaleY(0); } to { transform: scaleY(1); } }
        .bar { transform-origin: bottom; animation: growBar .7s cubic-bezier(.22,.9,.32,1) both; }
        @media (prefers-reduced-motion: reduce) { .bar { animation: none; } }
    </style>
</head>
<body class="font-poppins text-navy-950 antialiased">

    @include('partials.home-sidebar')

    <div class="lg:pl-64">

        <div class="max-w-7xl mx-auto px-4 sm:px-5 md:px-8 pb-16">

            @php
                $hour = now()->hour;
                $sapaan = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

                $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

                // Ikon tahap alur kerja (isi <svg>, viewBox 24x24)
                $icons = [
                    'pemesanan'  => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17"/><path d="M8 3v4M16 3v4"/>',
                    'persiapan'  => '<path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5Z"/><path d="M4 8.5 12 13l8-4.5"/><path d="M12 13v7"/>',
                    'qc'         => '<path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
                    'packing'    => '<rect x="4" y="7" width="16" height="13" rx="1.5"/><path d="M8 7V5.5A2.5 2.5 0 0 1 10.5 3h3A2.5 2.5 0 0 1 16 5.5V7"/>',
                    'distribusi' => '<path d="M3 16V7a1 1 0 0 1 1-1h9v10"/><path d="M13 10h4l4 3.5V16h-2"/><circle cx="7.5" cy="17.5" r="1.7"/><circle cx="17" cy="17.5" r="1.7"/>',
                ];

                $maxStage = collect($pipeline)->max('total');
                $maxTren  = max(1, collect($tren)->max('value'));
                $totalTren = collect($tren)->sum('value');
            @endphp

            {{-- ============ HERO DASHBOARD ============ --}}
            <section class="mt-6 relative">
                <div class="relative overflow-hidden rounded-[22px] sm:rounded-[26px] bg-navy-900">
                    <div class="absolute inset-0 hero-grid opacity-[0.05]"></div>
                    <div class="hero-glow absolute -top-32 -right-20 w-80 h-80 rounded-full pointer-events-none"></div>

                    <div class="relative px-5 sm:px-8 md:px-10 pt-7 sm:pt-9 pb-7 sm:pb-9">

                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="anim-fade-up text-rust-500 text-xs sm:text-sm font-semibold" style="animation-delay:.05s">biMBA Logistik</p>
                                <h1 class="anim-fade-up mt-2 text-xl sm:text-2xl md:text-[30px] leading-tight font-bold text-white" style="animation-delay:.1s">
                                    {{ $sapaan }}, {{ Auth::user()->name ?? 'Admin' }}
                                </h1>
                                <p class="anim-fade-up mt-2 text-sm sm:text-[15px] max-w-md" style="animation-delay:.16s; color:#B9C4DC">
                                    Pantau alur kerja dari data masuk sampai barang terkirim, semua dalam satu halaman.
                                </p>
                            </div>

                            <div class="anim-fade-up text-right shrink-0" style="animation-delay:.1s">
                                <p id="hero-clock-date" class="text-xs sm:text-sm font-medium text-white/70"></p>
                                <p id="hero-clock-time" class="text-xl sm:text-2xl font-bold text-white mt-0.5 tabular-nums"></p>
                            </div>
                        </div>

                        {{-- Aksi cepat --}}
                        <div class="anim-fade-up mt-6 flex flex-wrap gap-3" style="animation-delay:.22s">
                            <a href="{{ route('order.index') }}"
                               class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                Lihat Order
                            </a>
                            <a href="{{ route('picking.index') }}"
                               class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/[0.16] border border-white/15 transition-colors text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                                Mulai Picking
                            </a>
                            <a href="{{ route('qc-outgoing.index') }}"
                               class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/[0.16] border border-white/15 transition-colors text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                                QC Outgoing
                            </a>
                        </div>

                        {{-- Ringkasan hari ini --}}
                        <div class="anim-fade-up mt-7 pt-6 border-t border-white/10 grid grid-cols-2 lg:grid-cols-4 gap-y-5" style="animation-delay:.3s">

                            <div class="flex items-center gap-3.5 pr-4 lg:pr-6">
                                <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['pemesanan'] !!}</svg>
                                </div>
                                <div>
                                    <p class="js-counter text-2xl sm:text-[26px] font-bold text-white leading-none" data-target="{{ $stats['order_hari_ini'] ?? 0 }}">0</p>
                                    <p class="text-xs sm:text-[13px] mt-1" style="color:#9FADC7">Order hari ini</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3.5 pl-4 lg:px-6 lg:border-l lg:border-white/10">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(232,93,42,0.18);">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="#F3946F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['distribusi'] !!}</svg>
                                </div>
                                <div>
                                    <p class="js-counter text-2xl sm:text-[26px] font-bold text-white leading-none" data-target="{{ $stats['siap_kirim'] ?? 0 }}">0</p>
                                    <p class="text-xs sm:text-[13px] mt-1" style="color:#9FADC7">Siap dikirim</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3.5 pr-4 lg:px-6 lg:border-l lg:border-white/10">
                                <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons['qc'] !!}</svg>
                                </div>
                                <div>
                                    <p class="js-counter text-2xl sm:text-[26px] font-bold text-white leading-none" data-target="{{ $stats['proses_qc'] ?? 0 }}">0</p>
                                    <p class="text-xs sm:text-[13px] mt-1" style="color:#9FADC7">Dalam QC</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3.5 pl-4 lg:pl-6 lg:border-l lg:border-white/10">
                                <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 3 19h18L12 4Z"/><path d="M12 10v4"/><path d="M12 16.8v.2"/></svg>
                                </div>
                                <div>
                                    <p class="js-counter text-2xl sm:text-[26px] font-bold text-white leading-none" data-target="{{ $stats['perlu_perhatian'] ?? 0 }}">0</p>
                                    <p class="text-xs sm:text-[13px] mt-1" style="color:#9FADC7">Perlu perhatian</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ ALUR KERJA ============ --}}
            <section class="reveal mt-10 sm:mt-11">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <h2 class="text-base sm:text-lg font-semibold text-navy-950">Alur kerja</h2>
                    <span class="text-xs text-navy-950/40">Pekerjaan yang menunggu di setiap tahap, dari order sampai distribusi</span>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 xl:gap-5">
                    @foreach ($pipeline as $st)
                        @php $hot = $maxStage > 0 && $st['total'] === $maxStage; @endphp
                        <a href="{{ route($st['route']) }}" class="stage-card {{ $hot ? 'is-hot' : '' }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $hot ? 'bg-rust-500/10' : 'bg-navy-700/[0.08]' }}">
                                        <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="{{ $hot ? '#D14E1F' : '#28447F' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$st['key']] !!}</svg>
                                    </div>
                                    <h3 class="font-semibold text-[15px] text-navy-950 truncate">{{ $st['title'] }}</h3>
                                </div>
                                <svg class="stage-go w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
                            </div>

                            <p class="js-counter mt-4 text-3xl font-bold leading-none tabular-nums {{ $hot ? 'text-rust-600' : 'text-navy-950' }}" data-target="{{ $st['total'] }}">0</p>
                            <p class="mt-1.5 text-xs text-navy-950/50">
                                {{ $st['caption'] }}
                                @if ($hot)
                                    <span class="ml-1 inline-block rounded-full bg-rust-500/10 text-rust-600 text-[10px] font-semibold px-2 py-0.5">paling menumpuk</span>
                                @endif
                            </p>

                            <div class="mt-4 pt-3 border-t border-navy-950/[0.06] grid grid-cols-3 gap-2">
                                @foreach ($st['parts'] as $label => $jumlah)
                                    <div>
                                        <p class="text-sm font-semibold tabular-nums text-navy-950">{{ number_format($jumlah, 0, ',', '.') }}</p>
                                        <p class="text-[11px] text-navy-950/45">{{ $label }}</p>
                                    </div>
                                @endforeach
                            </div>

                            @if (!empty($st['notes']))
                                <div class="mt-3 space-y-1">
                                    @foreach ($st['notes'] as $label => $jumlah)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-navy-950/50">{{ $label }}</span>
                                            <span class="font-semibold tabular-nums text-navy-950/80">{{ number_format($jumlah, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- ============ TREN ORDER + PERLU PERHATIAN ============ --}}
            <section class="reveal mt-8 sm:mt-9 grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Tren order 7 hari --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-card p-5 sm:p-6">
                    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                        <h2 class="text-base sm:text-lg font-semibold text-navy-950">Order masuk 7 hari terakhir</h2>
                        <span class="text-xs text-navy-950/45">{{ number_format($totalTren, 0, ',', '.') }} order dari semua sumber</span>
                    </div>

                    <div class="mt-5 flex items-end gap-2 sm:gap-4">
                        @foreach ($tren as $t)
                            @php $tinggi = $t['value'] > 0 ? max(6, round($t['value'] / $maxTren * 100)) : 3; @endphp
                            <div class="flex-1 min-w-0 flex flex-col items-center gap-2">
                                <span class="text-xs font-semibold tabular-nums {{ $t['today'] ? 'text-rust-600' : 'text-navy-950/70' }}">{{ $t['value'] }}</span>
                                <div class="w-full h-36 flex items-end">
                                    <div class="bar w-full rounded-t-lg"
                                         style="height: {{ $tinggi }}%; background: {{ $t['today'] ? '#E85D2A' : ($t['value'] > 0 ? '#28447F' : 'rgba(15,27,51,0.10)') }}; animation-delay: {{ $loop->index * 0.05 }}s;"
                                         title="{{ $t['tgl'] }}: {{ $t['value'] }} order"></div>
                                </div>
                                <div class="text-center leading-tight">
                                    <p class="text-[11px] font-medium {{ $t['today'] ? 'text-rust-600' : 'text-navy-950/70' }}">{{ $t['nama'] }}</p>
                                    <p class="text-[10px] text-navy-950/40">{{ $t['tgl'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-navy-950/[0.06]">
                        <p class="text-xs text-navy-950/45 mb-2">Hari ini menurut sumber</p>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            @foreach ($sumberOrder as $s)
                                <a href="{{ route($s['route']) }}" class="row-link flex items-center justify-between sm:block rounded-lg px-3 py-2 bg-navy-950/[0.025]">
                                    <p class="text-xs text-navy-950/55">{{ $s['label'] }}</p>
                                    <p class="text-base font-semibold tabular-nums text-navy-950">{{ number_format($s['value'], 0, ',', '.') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Perlu perhatian --}}
                <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-base sm:text-lg font-semibold text-navy-950">Perlu perhatian</h2>
                        @if ($totalPerhatian > 0)
                            <span class="text-xs font-semibold text-rust-600">{{ number_format($totalPerhatian, 0, ',', '.') }} item</span>
                        @endif
                    </div>

                    @if (count($perhatian) === 0)
                        <div class="mt-5 rounded-xl bg-navy-950/[0.025] px-4 py-8 text-center">
                            <p class="text-sm font-medium text-navy-950/70">Semua aman</p>
                            <p class="mt-1 text-xs text-navy-950/45">Tidak ada QC bermasalah, pengiriman tertahan, atau persetujuan yang menunggu.</p>
                        </div>
                    @else
                        <div class="mt-3 -mx-2 divide-y divide-navy-950/[0.06] max-h-[26rem] overflow-y-auto">
                            @foreach ($perhatian as $p)
                                <a href="{{ route($p['route']) }}" class="row-link flex items-center gap-3 px-2 py-3 rounded-lg">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $p['tone'] === 'rust' ? 'bg-rust-500' : 'bg-navy-700/50' }}"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-navy-950 truncate">{{ $p['label'] }}</span>
                                        <span class="block text-xs text-navy-950/45 truncate">{{ $p['hint'] }}</span>
                                    </span>
                                    <span class="text-sm font-semibold tabular-nums {{ $p['tone'] === 'rust' ? 'text-rust-600' : 'text-navy-950' }}">{{ number_format($p['value'], 0, ',', '.') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- ============ GUDANG & STOK ============ --}}
            <section class="reveal mt-10 sm:mt-11">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <h2 class="text-base sm:text-lg font-semibold text-navy-950">Gudang dan stok</h2>
                    <span class="text-xs text-navy-950/40">Produk, suplier, pembelian, dan penerimaan barang</span>
                </div>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    @foreach ($gudang as $g)
                        <a href="{{ route($g['route']) }}" class="tile">
                            <p class="js-counter text-xl font-bold tabular-nums text-navy-950 leading-none" data-target="{{ $g['value'] }}">0</p>
                            <p class="mt-2 text-xs text-navy-950/55 leading-tight">{{ $g['label'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- ============ KEUANGAN + DATABASE MITRA ============ --}}
            <section class="reveal mt-10 sm:mt-11 grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- Pengeluaran bulan ini --}}
                <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-base sm:text-lg font-semibold text-navy-950">Pengeluaran bulan ini</h2>
                        <a href="{{ route('pengeluaran.index') }}" class="text-xs font-medium text-navy-700 hover:text-rust-600 transition-colors">Lihat rincian</a>
                    </div>

                    <p class="mt-4 text-2xl font-bold tabular-nums text-navy-950">{{ $rp($keuangan['total']) }}</p>
                    <p class="mt-1 text-xs text-navy-950/45">{{ now()->locale('id')->isoFormat('MMMM YYYY') }}, gabungan biMBA Shop, Kasdana, dan Manual</p>

                    <div class="mt-4 flex h-2.5 w-full overflow-hidden rounded-full bg-navy-950/[0.08]">
                        @if ($keuangan['total'] > 0)
                            @foreach ($keuangan['items'] as $k)
                                @if ($k['value'] > 0)
                                    <div style="width: {{ $k['value'] / $keuangan['total'] * 100 }}%; background: {{ $k['color'] }};" title="{{ $k['label'] }}"></div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-4 divide-y divide-navy-950/[0.06]">
                        @foreach ($keuangan['items'] as $k)
                            <a href="{{ route($k['route']) }}" class="row-link flex items-center gap-3 py-2.5 rounded-lg">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $k['color'] }};"></span>
                                <span class="flex-1 text-sm text-navy-950/75">{{ $k['label'] }}</span>
                                <span class="text-sm font-semibold tabular-nums text-navy-950">{{ $rp($k['value']) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Database & mitra + periode --}}
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                            <h2 class="text-base sm:text-lg font-semibold text-navy-950">Database dan mitra</h2>
                            <span class="text-xs text-navy-950/40">Unit, stokis, dan pengguna</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">
                            @foreach ($mitra as $m)
                                <a href="{{ route($m['route']) }}" class="tile">
                                    <p class="js-counter text-xl font-bold tabular-nums text-navy-950 leading-none" data-target="{{ $m['value'] }}">0</p>
                                    <p class="mt-2 text-xs text-navy-950/55 leading-tight">{{ $m['label'] }}</p>
                                    @if (!empty($m['sub']))
                                        <p class="mt-0.5 text-[11px] text-navy-950/40 leading-tight">{{ $m['sub'] }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                            <h2 class="text-base sm:text-lg font-semibold text-navy-950">Periode pesanan</h2>
                            <span class="text-xs text-navy-950/40">Majalah, DLC, dan Pasif</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">
                            @foreach ($periode as $p)
                                <a href="{{ route($p['route']) }}" class="tile">
                                    <p class="js-counter text-xl font-bold tabular-nums text-navy-950 leading-none" data-target="{{ $p['value'] }}">0</p>
                                    <p class="mt-2 text-xs text-navy-950/55 leading-tight">{{ $p['label'] }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        // Jam live
        (function () {
            var dateEl = document.getElementById('hero-clock-date');
            var timeEl = document.getElementById('hero-clock-time');
            if (!dateEl || !timeEl) return;

            function render() {
                var now = new Date();
                dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
            render();
            setInterval(render, 30000);
        })();

        // Counter animasi (angka diformat ribuan gaya Indonesia)
        document.querySelectorAll('.js-counter').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-target'), 10) || 0;
            var duration = 900;
            var startTime = null;
            var fmt = function (n) { return n.toLocaleString('id-ID'); };

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = fmt(Math.floor(eased * target));
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = fmt(target);
            }
            requestAnimationFrame(step);
        });

        // Reveal saat scroll
        var revealTargets = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            revealTargets.forEach(function (el) { observer.observe(el); });
        } else {
            revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
        }
    </script>
</body>
</html>