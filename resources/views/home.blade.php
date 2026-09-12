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
                        },
                        rust: {
                            500: '#E85D2A',
                            600: '#D14E1F',
                        },
                        canvas: '#EEF1F6',
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(15, 27, 51, 0.06), 0 8px 24px -12px rgba(15, 27, 51, 0.12)',
                        cardHover: '0 4px 10px rgba(15, 27, 51, 0.08), 0 16px 32px -12px rgba(15, 27, 51, 0.18)',
                    },
                }
            }
        }
    </script>
    <style>
        body { background: #EEF1F6; }

        /* ===== Rute putus-putus yang berjalan (sudah ada sebelumnya) ===== */
        .route-dash {
            stroke-dasharray: 6 7;
            animation: dash 18s linear infinite;
        }
        @keyframes dash { to { stroke-dashoffset: -260; } }

        /* ===== Titik oranye di ujung rute: berdenyut pelan ===== */
        .route-dot {
            animation: pulseDot 2.2s ease-in-out infinite;
            transform-origin: center;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(1.6); opacity: 0.55; }
        }

        /* ===== Truk melayang pelan (naik-turun halus) ===== */
        .truck-float {
            animation: floatTruck 3.4s ease-in-out infinite;
        }
        @keyframes floatTruck {
            0%, 100% { transform: translateY(0px); }
            50%      { transform: translateY(-6px); }
        }

        /* ===== Entrance: fade + slide naik ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-fade-up {
            opacity: 0;
            animation: fadeInUp 0.7s cubic-bezier(.22,.9,.32,1) forwards;
        }

        /* ===== Scroll-reveal untuk tiap section di bawah hero ===== */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ===== Stagger kartu menu: masing-masing anak ditunda dikit ===== */
        .stagger-grid.is-visible > * {
            opacity: 0;
            animation: fadeInUp 0.55s cubic-bezier(.22,.9,.32,1) forwards;
        }
        .stagger-grid.is-visible > *:nth-child(1) { animation-delay: 0.02s; }
        .stagger-grid.is-visible > *:nth-child(2) { animation-delay: 0.10s; }
        .stagger-grid.is-visible > *:nth-child(3) { animation-delay: 0.18s; }
        .stagger-grid.is-visible > *:nth-child(4) { animation-delay: 0.26s; }
        .stagger-grid.is-visible > *:nth-child(5) { animation-delay: 0.34s; }
        .stagger-grid.is-visible > *:nth-child(6) { animation-delay: 0.42s; }

        @media (prefers-reduced-motion: reduce) {
            .route-dash, .route-dot, .truck-float, .anim-fade-up,
            .stagger-grid.is-visible > * { animation: none !important; }
            .reveal { opacity: 1; transform: none; transition: none; }
        }

        /* ===== Hover kartu menu (sudah ada, dipertegas transisinya) ===== */
        .menu-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .menu-card:hover { transform: translateY(-4px); }
        .menu-card:hover .menu-icon-wrap { transform: translateY(-2px) scale(1.06); }
        .menu-icon-wrap { transition: transform 0.25s ease; }
        .menu-arrow { transition: transform 0.25s ease, opacity 0.25s ease; }
        .menu-card:hover .menu-arrow { transform: translateX(3px); opacity: 1; }
    </style>
</head>
<body class="font-poppins text-navy-950 antialiased">

    @include('partials.top-nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-5 md:px-8 pb-16">

        {{-- ============ HERO ============ --}}
        <section class="mt-6 relative overflow-hidden rounded-[22px] sm:rounded-[28px] bg-navy-900">
            <svg class="absolute inset-0 w-full h-full opacity-[0.10]" viewBox="0 0 800 260" preserveAspectRatio="none" fill="none">
                <path class="route-dash" d="M-20 210 C 150 210, 190 90, 340 90 S 520 210, 700 100 S 780 40, 860 40" stroke="#ffffff" stroke-width="2"/>
            </svg>

            <div class="relative grid grid-cols-1 lg:grid-cols-[1.3fr_1fr] gap-6 lg:gap-8 items-center px-4 sm:px-6 md:px-10 py-6 sm:py-9 md:py-11">
                <div>
                    <p class="anim-fade-up text-rust-500 text-xs sm:text-sm font-semibold tracking-wide" style="animation-delay:.05s">biMBA Logistik</p>
                    <h1 class="anim-fade-up mt-2 text-xl sm:text-2xl md:text-[32px] leading-tight font-bold text-white" style="animation-delay:.15s">
                        Pusat kendali gudang, order, dan pengiriman
                    </h1>
                    <p class="anim-fade-up mt-3 text-navy-100/80 text-sm sm:text-[15px] max-w-md" style="animation-delay:.25s; color:#C9D3E6">
                        Pantau alur kerja dari data masuk sampai barang terkirim, semua dalam satu halaman.
                    </p>

                    <div class="anim-fade-up mt-6 sm:mt-7 grid grid-cols-3 gap-2 sm:gap-3 max-w-md" style="animation-delay:.35s">
                        <div class="bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-2.5 sm:px-4 py-2.5 sm:py-3 min-w-0">
                            <p class="js-counter text-white text-base sm:text-lg md:text-xl font-bold truncate"
                               data-target="{{ $stats['order_hari_ini'] ?? 0 }}">0</p>
                            <p class="text-[10px] sm:text-[11px] mt-0.5 leading-tight" style="color:#9FADC7">Order hari ini</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-2.5 sm:px-4 py-2.5 sm:py-3 min-w-0">
                            <p class="js-counter text-white text-base sm:text-lg md:text-xl font-bold truncate"
                               data-target="{{ $stats['siap_kirim'] ?? 0 }}">0</p>
                            <p class="text-[10px] sm:text-[11px] mt-0.5 leading-tight" style="color:#9FADC7">Siap dikirim</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl px-2.5 sm:px-4 py-2.5 sm:py-3 min-w-0">
                            <p class="js-counter text-white text-base sm:text-lg md:text-xl font-bold truncate"
                               data-target="{{ $stats['proses_qc'] ?? 0 }}">0</p>
                            <p class="text-[10px] sm:text-[11px] mt-0.5 leading-tight" style="color:#9FADC7">Dalam QC</p>
                        </div>
                    </div>
                </div>

                {{-- Ilustrasi gudang - truk sederhana bergaya line-art --}}
                <div class="hidden md:block anim-fade-up" style="animation-delay:.3s">
                    <svg viewBox="0 0 340 200" class="w-full h-auto max-w-[280px] lg:max-w-none mx-auto">
                        <rect x="18" y="70" width="120" height="80" rx="6" fill="#1D3361" stroke="#3A548A" stroke-width="1.5"/>
                        <rect x="30" y="84" width="24" height="24" rx="3" fill="#28447F"/>
                        <rect x="60" y="84" width="24" height="24" rx="3" fill="#28447F"/>
                        <rect x="90" y="84" width="24" height="24" rx="3" fill="#28447F"/>
                        <rect x="30" y="114" width="24" height="24" rx="3" fill="#28447F"/>
                        <rect x="60" y="114" width="24" height="24" rx="3" fill="#28447F"/>
                        <rect x="90" y="114" width="24" height="24" rx="3" fill="#E85D2A"/>
                        <path d="M18 70 L78 40 L138 70" fill="none" stroke="#3A548A" stroke-width="2" stroke-linejoin="round"/>

                        <g class="truck-float">
                            <rect x="150" y="126" width="86" height="34" rx="4" fill="#E8ECF3"/>
                            <rect x="150" y="104" width="40" height="26" rx="4" fill="#E8ECF3"/>
                            <circle cx="172" cy="164" r="10" fill="#0F1B33" stroke="#E8ECF3" stroke-width="3"/>
                            <circle cx="212" cy="164" r="10" fill="#0F1B33" stroke="#E8ECF3" stroke-width="3"/>
                            <rect x="156" y="110" width="20" height="14" rx="2" fill="#9FADC7"/>

                            <g stroke="#E85D2A" stroke-width="2" fill="none" stroke-linecap="round">
                                <path d="M246 150 h20 M246 158 h14"/>
                            </g>
                        </g>

                        <path d="M250 96 C 262 60, 300 46, 322 20" fill="none" stroke="#E85D2A" stroke-width="2" stroke-dasharray="5 6" class="route-dash"/>
                        <circle cx="322" cy="20" r="5" fill="#E85D2A" class="route-dot"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- ============ DATA & MASTER ============ --}}
        <section class="reveal mt-8 sm:mt-10">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <h2 class="text-base sm:text-lg font-semibold text-navy-950">Data & Master</h2>
                <span class="hidden sm:inline text-xs text-navy-950/40">Sumber data operasional</span>
            </div>
            <div class="stagger-grid mt-4 grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">

                <a href="{{ route('dashboard') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-900">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <ellipse cx="12" cy="5.5" rx="7.5" ry="2.5"/>
                            <path d="M4.5 5.5V18.5C4.5 19.88 7.86 21 12 21C16.14 21 19.5 19.88 19.5 18.5V5.5"/>
                            <path d="M4.5 12C4.5 13.38 7.86 14.5 12 14.5C16.14 14.5 19.5 13.38 19.5 12"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Database</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Ringkasan seluruh data</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('database-user.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-800">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="8" r="3.2"/>
                            <path d="M3.5 20c0-3.3 2.6-5.5 5.8-5.5S15 16.7 15 20"/>
                            <circle cx="17" cy="8.5" r="2.4"/>
                            <path d="M16.2 14.6c2.6.3 4.3 2.2 4.3 5.1"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Database User</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Kelola akun pengguna</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('import.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rust-500">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v11"/>
                            <path d="M7.5 10.5 12 15l4.5-4.5"/>
                            <path d="M4.5 17.5v2a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5v-2"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Data Import biMBA Shop</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Unggah data massal</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

            </div>
        </section>

        {{-- ============ ORDER MANUAL ============ --}}
        <section class="reveal mt-8 sm:mt-9">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <h2 class="text-base sm:text-lg font-semibold text-navy-950">Order Manual</h2>
                <span class="hidden sm:inline text-xs text-navy-950/40">Input order per kategori produk</span>
            </div>
            <div class="stagger-grid mt-4 grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">

                <a href="{{ route('order-manual.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-700">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 4h11l3 3v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/>
                            <path d="M16 4v3h3"/>
                            <path d="M8 12h8M8 15.5h8M8 8.5h4"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Majalah</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Order manual majalah</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('order-manual-modul.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-700">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 6.5c-1.8-1.2-4-1.7-6.5-1.5v13c2.5-.2 4.7.3 6.5 1.5 1.8-1.2 4-1.7 6.5-1.5V5c-2.5-.2-4.7.3-6.5 1.5Z"/>
                            <path d="M12 6.5V20"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Modul</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Order manual modul</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('order-manual-sertifikat.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-700">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="9" r="5.2"/>
                            <path d="m8.3 13.2-1.6 7 5.3-2.6 5.3 2.6-1.6-7"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Sertifikat</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Order manual sertifikat</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

            </div>
        </section>

        {{-- ============ ALUR GUDANG ============ --}}
        <section class="reveal mt-8 sm:mt-9">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <h2 class="text-base sm:text-lg font-semibold text-navy-950">Alur Gudang</h2>
                <span class="hidden sm:inline text-xs text-navy-950/40">Order → Picking → QC → Packing → Kirim</span>
            </div>
            <div class="stagger-grid mt-4 grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">

                <a href="{{ route('picking.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-800">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5Z"/>
                            <path d="M4 8.5 12 13l8-4.5"/>
                            <path d="M12 13v7"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Picking</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Ambil barang di rak</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('qc-outgoing.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-800">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/>
                            <path d="m9 12 2.2 2.2L15.5 10"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">QC Outgoing</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Cek kualitas keluar</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('packing.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-navy-800">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="8" width="16" height="12" rx="1.5"/>
                            <path d="M4 13h16"/>
                            <path d="M12 8v12"/>
                            <path d="M8 8V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V8"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Packing</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Kemas untuk kirim</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

                <a href="{{ route('distribution-order.index') }}" class="menu-card group bg-white rounded-2xl shadow-card hover:shadow-cardHover p-4 sm:p-5">
                    <div class="menu-icon-wrap inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rust-500">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 16V7a1 1 0 0 1 1-1h9v10"/>
                            <path d="M13 10h4l4 3.5V16h-2"/>
                            <circle cx="7.5" cy="17.5" r="1.7"/>
                            <circle cx="17" cy="17.5" r="1.7"/>
                            <path d="M9.2 17.5h6.1"/>
                        </svg>
                    </div>
                    <h3 class="mt-3.5 font-semibold text-[15px] text-navy-950">Distribution</h3>
                    <p class="mt-0.5 text-xs text-navy-950/45">Jadwal & rute kirim</p>
                    <span class="menu-arrow inline-block mt-2 text-rust-500 text-sm opacity-0">Buka →</span>
                </a>

            </div>
        </section>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        // ===== 1. Angka statistik hero: hitung naik dari 0 ke nilai asli =====
        document.querySelectorAll('.js-counter').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-target'), 10) || 0;
            var duration = 900; // ms
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                // easeOutCubic biar di akhir melambat halus
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target);
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target;
                }
            }
            requestAnimationFrame(step);
        });

        // ===== 2. Reveal section & stagger kartu saat discroll ke layar =====
        var revealTargets = document.querySelectorAll('.reveal, .stagger-grid');

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            revealTargets.forEach(function (el) { observer.observe(el); });
        } else {
            // Fallback browser lama: langsung tampilkan semua
            revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
        }
    </script>

</body>
</html>