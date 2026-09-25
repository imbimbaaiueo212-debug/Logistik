<style>
    /* ===== Sidebar Home: accordion + tema navy/rust ===== */
    #home-sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.18) transparent;
    }
    #home-sidebar::-webkit-scrollbar { width: 6px; }
    #home-sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(255,255,255,0.18);
        border-radius: 9999px;
    }

    .hs-group-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.35);
        padding: 0 12px;
        margin-top: 18px;
        margin-bottom: 6px;
    }

    .hs-link, .hs-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        padding: 9px 12px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 500;
        color: rgba(255,255,255,0.72);
        transition: background-color .18s ease, color .18s ease, box-shadow .25s ease, transform .15s ease;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
        position: relative;
    }
    .hs-link:hover, .hs-toggle:hover {
        background-color: rgba(255,255,255,0.06);
        color: #fff;
    }
    .hs-link:active, .hs-toggle:active {
        transform: scale(0.98);
    }
    .hs-link.active, .hs-toggle.has-active {
        background-color: rgba(232,93,42,0.16);
        color: #fff;
    }
    .hs-link.active { box-shadow: inset 3px 0 0 #E85D2A; }

    .hs-chevron { width: 14px; height: 14px; opacity: .55; flex-shrink: 0; transition: transform .25s cubic-bezier(.4,0,.2,1); }
    .hs-toggle.open .hs-chevron { transform: rotate(90deg); }

    /* ===== Efek "menyala" saat baru diklik (independen per item) ===== */
    @keyframes hsGlowPulse {
        0%   { box-shadow: 0 0 0 0 rgba(232,93,42,0.55), inset 3px 0 0 #E85D2A; }
        60%  { box-shadow: 0 0 0 8px rgba(232,93,42,0), inset 3px 0 0 #E85D2A; }
        100% { box-shadow: 0 0 0 0 rgba(232,93,42,0), inset 3px 0 0 #E85D2A; }
    }
    .hs-glow {
        animation: hsGlowPulse .55s ease-out;
    }

    /* ===== Submenu: animasi slide smooth pakai CSS Grid (tanpa JS hitung tinggi) ===== */
    .hs-sub {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows .3s cubic-bezier(.4,0,.2,1), opacity .25s ease, margin .3s ease;
        opacity: 0;
        margin: 0 0 0 30px;
    }
    .hs-sub.open {
        grid-template-rows: 1fr;
        opacity: 1;
        margin: 2px 0 4px 30px;
    }
    .hs-sub > .hs-sub-inner {
        overflow: hidden;
        padding-left: 10px;
        border-left: 1px solid rgba(255,255,255,0.12);
        min-height: 0;
    }

    .hs-sub-link {
        display: block;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12.5px;
        color: rgba(255,255,255,0.6);
        transition: background-color .15s ease, color .15s ease, transform .15s ease;
    }
    .hs-sub-link:hover { background-color: rgba(255,255,255,0.06); color: #fff; transform: translateX(2px); }
    .hs-sub-link:active { transform: translateX(2px) scale(0.98); }
    .hs-sub-link.active {
        color: #fff;
        font-weight: 600;
        background-color: rgba(232,93,42,0.16);
        box-shadow: inset 2px 0 0 #E85D2A;
    }

    /* Dropdown bertingkat (submenu di dalam submenu) */
    .hs-sub-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12.5px;
        color: rgba(255,255,255,0.6);
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        transition: background-color .18s ease, color .18s ease, box-shadow .25s ease, transform .15s ease;
    }
    .hs-sub-toggle:hover { background-color: rgba(255,255,255,0.06); color: #fff; }
    .hs-sub-toggle:active { transform: scale(0.98); }
    .hs-sub-toggle.has-active { color: #fff; font-weight: 600; }
    .hs-sub-toggle .hs-chevron { width: 12px; height: 12px; }
    .hs-sub-toggle.open .hs-chevron { transform: rotate(90deg); }

    .hs-sub .hs-sub { margin: 2px 0 4px 8px; }
    .hs-sub .hs-sub.open { margin: 2px 0 4px 8px; }
    .hs-sub .hs-sub > .hs-sub-inner { padding-left: 8px; }
    .hs-sub .hs-sub .hs-sub > .hs-sub-inner { padding-left: 6px; }

    .hs-sub-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.32);
        padding: 8px 10px 2px;
    }
    .hs-soon {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 10px; font-size: 12.5px; color: rgba(255,255,255,0.32); cursor: default;
    }
    .hs-badge-soon {
        font-size: 9px; font-weight: 600; padding: 1px 6px; border-radius: 999px;
        background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.4);
    }

    /* Overlay + toggle mobile */
    #home-sidebar-overlay {
        display: none;
        position: fixed; inset: 0; background: rgba(15,27,51,0.5);
        z-index: 39;
        opacity: 0;
        transition: opacity .2s ease;
    }
    #home-sidebar-overlay.open { display: block; opacity: 1; }

    #home-sidebar-toggle {
        position: fixed; top: 22px; left: 18px; z-index: 45;
        width: 40px; height: 40px; border-radius: 12px;
        background: #162749; color: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px -8px rgba(15,27,51,0.4);
        transition: transform .15s ease;
    }
    #home-sidebar-toggle:active { transform: scale(0.92); }
    @media (min-width: 1024px) { #home-sidebar-toggle { display: none; } }
</style>

{{-- Tombol buka sidebar (mobile only) --}}
<button type="button" id="home-sidebar-toggle" aria-label="Buka menu">
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
</button>

{{-- Overlay (mobile only) --}}
<div id="home-sidebar-overlay"></div>

<aside id="home-sidebar"
       class="fixed top-0 left-0 h-screen w-64 z-40 flex flex-col overflow-y-auto
              -translate-x-full lg:translate-x-0 transition-transform duration-200"
       style="background:#162749;">

    {{-- Logo --}}
    <div class="flex items-center px-5 py-4 border-b shrink-0" style="border-color: rgba(255,255,255,0.08);">
        <img src="/assets/img/logotulisan.png" alt="biMBA-AIUEO" class="h-8 w-auto object-contain">
    </div>

    @php
        $__u = Auth::user();
    @endphp

    <nav class="flex-1 px-3 py-4">

        {{-- Home --}}
        <a href="{{ route('home') }}" class="hs-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9h12v-9"/></svg>
                Home
            </span>
        </a>

        {{-- Dashboard (Master Data / Gudang) — khusus admin & gudang --}}
        @if($__u->role === 'admin' || $__u->role === 'gudang')
        <a href="{{ route('dashboard') }}" class="hs-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Dashboard
            </span>
        </a>
        @endif

        {{-- ================= DATABASE ================= --}}
        @if($__u->hasModule('user') || $__u->hasModule('produk') || $__u->hasModule('suplier') || $__u->hasModule('stokis'))
        <p class="hs-group-label">DATABASE MASTER</p>
        @endif

        @if($__u->hasModule('user'))
        {{-- User --}}
        @php
            $hsUserActive = request()->routeIs(['user.export','unit-kemitraan.*','unit-kemitraan-user.*']);
        @endphp
        <button type="button" data-hs-toggle="#hs-user" class="hs-toggle {{ $hsUserActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c0-3 2.4-5 5.5-5s5.5 2 5.5 5"/></svg>
                User
            </span>
            <svg class="hs-chevron {{ $hsUserActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-user" class="hs-sub {{ $hsUserActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('user.export') }}" class="hs-sub-link {{ request()->routeIs('user.export') ? 'active' : '' }}">biMBA Shop</a>
                <a href="{{ route('unit-kemitraan.index') }}" class="hs-sub-link {{ request()->routeIs('unit-kemitraan.*') ? 'active' : '' }}">Unit Kemitraan</a>
                <a href="{{ route('unit-kemitraan-user.index') }}" class="hs-sub-link {{ request()->routeIs('unit-kemitraan-user.*') ? 'active' : '' }}">Unit + User Matching</a>
            </div>
        </div>
        @endif

        @if($__u->hasModule('produk'))
        {{-- Produk --}}
        <a href="{{ route('products.index') }}" class="hs-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="7" width="16" height="13" rx="1.5"/><path d="M8 7V5.5A2.5 2.5 0 0 1 10.5 3h3A2.5 2.5 0 0 1 16 5.5V7"/></svg>
                Produk
            </span>
        </a>
        @endif

        @if($__u->hasModule('suplier'))
        {{-- Suplier --}}
        <a href="{{ route('supplier-product.index') }}" class="hs-link {{ request()->routeIs('supplier-product.*') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 8.5 12 4l8.5 4.5-8.5 4.5-8.5-4.5Z"/><path d="M3.5 8.5v7L12 20l8.5-4.5v-7"/></svg>
                Suplier
            </span>
        </a>
        @endif

        @if($__u->hasModule('stokis'))
        {{-- Stokis --}}
        @php $hsStokisActive = request()->routeIs('stokis.*', 'stokis-pasif.*'); @endphp
        <button type="button" data-hs-toggle="#hs-stokis" class="hs-toggle {{ $hsStokisActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5 12 4l8 6.5"/><path d="M5.5 9.5V20h13V9.5"/></svg>
                Stokis
            </span>
            <svg class="hs-chevron {{ $hsStokisActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-stokis" class="hs-sub {{ $hsStokisActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('stokis.index') }}" class="hs-sub-link {{ request()->routeIs('stokis.*') ? 'active' : '' }}">Aktif</a>
                <a href="{{ route('stokis-pasif.index') }}" class="hs-sub-link {{ request()->routeIs('stokis-pasif.*') ? 'active' : '' }}">Pasif</a>
            </div>
        </div>
        @endif

        {{-- ================= APLIKASI PENJUALAN ================= --}}
        @if($__u->hasModule('pemesanan') || $__u->hasModule('penjualan') || $__u->hasModule('persiapan') || $__u->hasModule('qc') || $__u->hasModule('distribusi'))
        <p class="hs-group-label">ADM OPS LOGISTIK</p>
        @endif

        @if($__u->hasModule('pemesanan'))
        {{-- Pemesanan --}}
        @php
            // [label, nama route, pola route untuk state aktif]
            $hsPasifMenu = [
                ['Daftar Pasif',       'import.pasif.list',         ['import.pasif.list*']],
                ['Spare Pasif 3%',     'import.pasif.spare',        ['import.pasif.spare*']],
                ['Bacaan Unit',        'import.pasif.bacaan',       ['import.pasif.bacaan*']],
                ['Import',             'import.pasif.rekap',        ['import.pasif.rekap*']],
                ['Create Manual',      'import.pasif.manual.index', ['import.pasif.manual*']],
                ['Report Angka Cetak', 'import.report-angka-cetak', ['import.report-angka-cetak*']],
            ];
            $hsPasifActive = request()->routeIs(['import.pasif.*', 'import.report-angka-cetak*']);

            $hsOps2Active = request()->routeIs([
                'ops2.*','pesanan-majalah.*','pesanan-majalah-kotamadya.*','pesanan-majalah-puw1.*',
            ]);
            $hsMajalahActive = $hsOps2Active || $hsPasifActive || request()->routeIs([
                'order-manual.*','import.dlc.*',
            ]);

            $hsPemesananActive = $hsMajalahActive || request()->routeIs([
                'order.index','import.bimbashop','import.bimbashop.*',
                'order-manual-modul.manual.create','order-manual-modul.manual.create.*',
                'order-manual-sertifikat.manual.create','order-manual-sertifikat.manual.create.*',
            ]);
        @endphp
        <button type="button" data-hs-toggle="#hs-pemesanan" class="hs-toggle {{ $hsPemesananActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17"/><path d="M8 3v4M16 3v4"/></svg>
                Pemesanan
            </span>
            <svg class="hs-chevron {{ $hsPemesananActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-pemesanan" class="hs-sub {{ $hsPemesananActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <p class="hs-sub-label">Entry biMBA Shop</p>
                <a href="{{ route('import.bimbashop') }}" class="hs-sub-link {{ request()->routeIs('import.bimbashop','import.bimbashop.*') ? 'active' : '' }}">Import biMBA Shop</a>

                <p class="hs-sub-label">Entry Manual</p>

                {{-- Majalah (dropdown) --}}
                <button type="button" data-hs-toggle="#hs-majalah" class="hs-sub-toggle {{ $hsMajalahActive ? 'open has-active' : '' }}">
                    <span>Majalah</span>
                    <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div id="hs-majalah" class="hs-sub {{ $hsMajalahActive ? 'open' : '' }}">
                    <div class="hs-sub-inner">

                        {{-- Unit OPS2 (dropdown) --}}
                        <button type="button" data-hs-toggle="#hs-ops2" class="hs-sub-toggle {{ $hsOps2Active ? 'open has-active' : '' }}">
                            <span>Unit OPS2</span>
                            <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <div id="hs-ops2" class="hs-sub {{ $hsOps2Active ? 'open' : '' }}">
                            <div class="hs-sub-inner">
                                <a href="{{ route('pesanan-majalah.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah.*') ? 'active' : '' }}">KORWIL</a>
                                <a href="{{ route('pesanan-majalah-kotamadya.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah-kotamadya.*') ? 'active' : '' }}">PINWIL</a>
                                <a href="{{ route('pesanan-majalah-puw1.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah-puw1.*') ? 'active' : '' }}">JABODETABEK (PUW1)</a>
                            </div>
                        </div>

                        <a href="{{ route('import.dlc.index') }}" class="hs-sub-link {{ request()->routeIs('import.dlc.*') ? 'active' : '' }}">DLC</a>
                        {{-- Unit Pasif (dropdown) --}}
                        <button type="button" data-hs-toggle="#hs-pasif" class="hs-sub-toggle {{ $hsPasifActive ? 'open has-active' : '' }}">
                            <span>Unit Pasif</span>
                            <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <div id="hs-pasif" class="hs-sub {{ $hsPasifActive ? 'open' : '' }}">
                            <div class="hs-sub-inner">
                                @foreach($hsPasifMenu as [$label, $rute, $pola])
                                    <a href="{{ route($rute) }}" class="hs-sub-link {{ request()->routeIs($pola) ? 'active' : '' }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <a href="{{ route('order-manual-modul.manual.create') }}" class="hs-sub-link {{ request()->routeIs('order-manual-modul.manual.create','order-manual-modul.manual.create.*') ? 'active' : '' }}">Modul</a>
                <a href="{{ route('order-manual-sertifikat.manual.create') }}" class="hs-sub-link {{ request()->routeIs('order-manual-sertifikat.manual.create','order-manual-sertifikat.manual.create.*') ? 'active' : '' }}">Sertifikat</a>
            </div>
        </div>
        @endif

        @if($__u->hasModule('penjualan'))
        {{-- Penjualan --}}
        @php
            
            $hsPengeluaranActive = request()->routeIs([
                'pengeluaran.*',
                'order.jakarta-aktif','order.jakarta-aktif.*',
                'order.jakarta-pasif','order.jakarta-pasif.*',
                'import.manual','import.manual.*',
                'order-manual-modul.manual','order-manual-modul.manual.*',
                'order-manual-sertifikat.manual','order-manual-sertifikat.manual.*',
            ]) && ! request()->routeIs([
                'order-manual-modul.manual.create','order-manual-modul.manual.create.*',
                'order-manual-sertifikat.manual.create','order-manual-sertifikat.manual.create.*',
            ]);
            $hsPenjualanActive = request()->routeIs(['import.casdana', 'import.casdana.*']) || $hsPengeluaranActive;
        @endphp
        <button type="button" data-hs-toggle="#hs-penjualan" class="hs-toggle {{ $hsPenjualanActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><circle cx="12" cy="12" r="2.4"/></svg>
                Penjualan
            </span>
            <svg class="hs-chevron {{ $hsPenjualanActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-penjualan" class="hs-sub {{ $hsPenjualanActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('import.casdana') }}" class="hs-sub-link {{ request()->routeIs('import.casdana','import.casdana.*') ? 'active' : '' }}">Kasdana</a>

                {{-- Pengeluaran = rekap RA (Rekap Aktual): Aktif, Pasif, Manual, Modul, Sertifikat --}}
                <button type="button" data-hs-toggle="#hs-pengeluaran" class="hs-sub-toggle {{ $hsPengeluaranActive ? 'open has-active' : '' }}">
                    <span>Pengeluaran</span>
                    <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div id="hs-pengeluaran" class="hs-sub {{ $hsPengeluaranActive ? 'open' : '' }}">
                    <div class="hs-sub-inner">
                        <a href="{{ route('order.jakarta-aktif') }}#ra-aktif"
                           class="hs-sub-link {{ request()->routeIs('order.jakarta-aktif','order.jakarta-aktif.*') ? 'active' : '' }}">Aktif</a>
                        <a href="{{ route('order.jakarta-pasif') }}#ra-pasif"
                           class="hs-sub-link {{ request()->routeIs('order.jakarta-pasif','order.jakarta-pasif.*') ? 'active' : '' }}">Pasif</a>
                        <a href="{{ route('import.manual') }}#rekap-manual"
                           class="hs-sub-link {{ request()->routeIs('import.manual','import.manual.*') ? 'active' : '' }}">Manual</a>
                        <a href="{{ route('order-manual-modul.manual') }}#ra-modul"
                           class="hs-sub-link {{ request()->routeIs('order-manual-modul.manual') || (request()->routeIs('order-manual-modul.manual.*') && !request()->routeIs('order-manual-modul.manual.create','order-manual-modul.manual.create.*')) ? 'active' : '' }}">Modul</a>
                        <a href="{{ route('order-manual-sertifikat.manual') }}#ra-sertifikat"
                           class="hs-sub-link {{ request()->routeIs('order-manual-sertifikat.manual') || (request()->routeIs('order-manual-sertifikat.manual.*') && !request()->routeIs('order-manual-sertifikat.manual.create','order-manual-sertifikat.manual.create.*')) ? 'active' : '' }}">Sertifikat</a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($__u->hasModule('persiapan'))
            {{-- Persiapan --}}
            @php $hsPersiapanActive = request()->routeIs('picking.*'); @endphp
            <button type="button" data-hs-toggle="#hs-persiapan" class="hs-toggle {{ $hsPersiapanActive ? 'open has-active' : '' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5Z"/><path d="M4 8.5 12 13l8-4.5"/></svg>
                    Persiapan
                </span>
                <svg class="hs-chevron {{ $hsPersiapanActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div id="hs-persiapan" class="hs-sub {{ $hsPersiapanActive ? 'open' : '' }}">
                <div class="hs-sub-inner">
                    <a href="{{ route('picking.jakarta.aktif') }}" class="hs-sub-link {{ request()->routeIs('picking.jakarta.aktif') ? 'active' : '' }}">Jakarta Aktif</a>
                    <a href="{{ route('picking.jakarta.pasif') }}" class="hs-sub-link {{ request()->routeIs('picking.jakarta.pasif') ? 'active' : '' }}">Jakarta Pasif</a>
                    <div class="hs-soon">InterVio (DLC) <span class="hs-badge-soon">segera</span></div>
                    <div class="hs-soon">English biMBA Talk <span class="hs-badge-soon">segera</span></div>
                    <a href="{{ route('picking.order-manual') }}" class="hs-sub-link {{ request()->routeIs('picking.order-manual') ? 'active' : '' }}">Order Manual</a>
                </div>
            </div>
        @endif

        @if($__u->hasModule('qc'))
        {{-- QC --}}
        @php $hsQcActive = request()->routeIs('qc-outgoing.*'); @endphp
        <button type="button" data-hs-toggle="#hs-qc" class="hs-toggle {{ $hsQcActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/></svg>
                QC
            </span>
            <svg class="hs-chevron {{ $hsQcActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-qc" class="hs-sub {{ $hsQcActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('qc-outgoing.jakarta-aktif') }}" class="hs-sub-link {{ request()->routeIs('qc-outgoing.jakarta-aktif') ? 'active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('qc-outgoing.jakarta-pasif') }}" class="hs-sub-link {{ request()->routeIs('qc-outgoing.jakarta-pasif') ? 'active' : '' }}">Jakarta Pasif</a>
                <div class="hs-soon">InterVio (DLC) <span class="hs-badge-soon">segera</span></div>
                <div class="hs-soon">English biMBA Talk <span class="hs-badge-soon">segera</span></div>
                <a href="{{ route('qc-outgoing.order-manual') }}" class="hs-sub-link {{ request()->routeIs('qc-outgoing.order-manual') ? 'active' : '' }}">Order Manual</a>
            </div>
        </div>
        @endif

        @if($__u->hasModule('distribusi'))
{{-- Distribusi --}}
@php
    $hsPackingActive = request()->routeIs('packing.*');
    $hsEkspedisiActive = request()->routeIs('distribution-order.*') && !request()->filled('ekspedisi');
    $hsServiceActive = request()->routeIs('distribution-order.*') && request()->filled('ekspedisi');
    $hsDistribusiActive = $hsPackingActive || $hsEkspedisiActive || $hsServiceActive;
@endphp
<button type="button" data-hs-toggle="#hs-distribusi" class="hs-toggle {{ $hsDistribusiActive ? 'open has-active' : '' }}">
    <span class="inline-flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16V7a1 1 0 0 1 1-1h9v10"/><path d="M13 10h4l4 3.5V16h-2"/><circle cx="7.5" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/></svg>
        Distribusi
    </span>
    <svg class="hs-chevron {{ $hsDistribusiActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
</button>
<div id="hs-distribusi" class="hs-sub {{ $hsDistribusiActive ? 'open' : '' }}">
    <div class="hs-sub-inner">

        {{-- Packing (dropdown) --}}
        <button type="button" data-hs-toggle="#hs-packing" class="hs-sub-toggle {{ $hsPackingActive ? 'open has-active' : '' }}">
            <span>Packing</span>
            <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-packing" class="hs-sub {{ $hsPackingActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('packing.jakarta.aktif') }}" class="hs-sub-link {{ request()->routeIs('packing.jakarta.aktif') ? 'active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('packing.jakarta-pasif') }}" class="hs-sub-link {{ request()->routeIs('packing.jakarta-pasif') ? 'active' : '' }}">Jakarta Pasif</a>
                <div class="hs-soon">InterVio (DLC) <span class="hs-badge-soon">segera</span></div>
                <div class="hs-soon">English biMBA Talk <span class="hs-badge-soon">segera</span></div>
                <a href="{{ route('packing.order-manual') }}" class="hs-sub-link {{ request()->routeIs('packing.order-manual') ? 'active' : '' }}">Order Manual</a>
            </div>
        </div>

        {{-- Ekspedisi (dropdown) --}}
        <button type="button" data-hs-toggle="#hs-ekspedisi" class="hs-sub-toggle {{ $hsEkspedisiActive ? 'open has-active' : '' }}">
            <span>Ekspedisi</span>
            <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-ekspedisi" class="hs-sub {{ $hsEkspedisiActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                <a href="{{ route('distribution-order.jakarta-aktif') }}" class="hs-sub-link {{ request()->routeIs('distribution-order.jakarta-aktif') && !request()->filled('ekspedisi') ? 'active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('distribution-order.jakarta-pasif') }}" class="hs-sub-link {{ request()->routeIs('distribution-order.jakarta-pasif') && !request()->filled('ekspedisi') ? 'active' : '' }}">Jakarta Pasif</a>
                <div class="hs-soon">InterVio (DLC) <span class="hs-badge-soon">segera</span></div>
                <div class="hs-soon">English biMBA Talk <span class="hs-badge-soon">segera</span></div>
                <a href="{{ route('distribution-order.manual') }}" class="hs-sub-link {{ request()->routeIs('distribution-order.manual') && !request()->filled('ekspedisi') ? 'active' : '' }}">Manual</a>
            </div>
        </div>

        {{-- Service (dropdown, otomatis dari nama-nama kurir yang ada di data: JNE, TIKI, Lion Parcel, dst) --}}
        @php $ekspedisiOptions = $ekspedisiOptions ?? collect(); @endphp
        @if($ekspedisiOptions->isNotEmpty())
        <button type="button" data-hs-toggle="#hs-service" class="hs-sub-toggle {{ $hsServiceActive ? 'open has-active' : '' }}">
            <span>Service</span>
            <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-service" class="hs-sub {{ $hsServiceActive ? 'open' : '' }}">
            <div class="hs-sub-inner">
                @foreach($ekspedisiOptions as $eks)
                    <a href="{{ route('distribution-order.jakarta-aktif', ['ekspedisi' => $eks]) }}"
                       class="hs-sub-link {{ request('ekspedisi') === $eks ? 'active' : '' }}">
                        {{ $eks }}
                    </a>
                @endforeach
            </div>
        </div>
        @else
        <div class="hs-soon">Service <span class="hs-badge-soon">belum ada data</span></div>
        @endif

    </div>
</div>
@endif

    </nav>

    {{-- User + Logout --}}
    <div class="border-t p-4 shrink-0" style="border-color: rgba(255,255,255,0.08);">
        <p class="text-sm text-white/80 font-medium mb-2 truncate">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="block w-full text-center px-4 py-2 rounded-xl text-sm font-medium text-white transition-all cursor-pointer hover:brightness-110 active:scale-95"
                    style="background:#E85D2A;">
                LOGOUT
            </button>
        </form>
    </div>
</aside>

<script>
(function () {
    var nav = document.querySelector('#home-sidebar nav');
    // Guard: cegah listener terpasang dobel kalau partial ini ter-include lebih dari sekali
    if (!nav || nav.dataset.hsBound === '1') return;
    nav.dataset.hsBound = '1';

    // ===== Accordion (event delegation, 1 listener untuk semua toggle) =====
    nav.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-hs-toggle]');
        if (!btn || !nav.contains(btn)) return;

        var target = document.querySelector(btn.getAttribute('data-hs-toggle'));
        if (!target) return;

        // Sumber kebenaran tunggal: state target, lalu tombol mengikuti
        var willOpen = !target.classList.contains('open');

        target.classList.toggle('open', willOpen);
        btn.classList.toggle('open', willOpen);

        // Efek "menyala" hanya saat dibuka
        if (willOpen) {
            btn.classList.remove('hs-glow');
            void btn.offsetWidth; // force reflow supaya animasi restart
            btn.classList.add('hs-glow');
            btn.addEventListener('animationend', function handler() {
                btn.classList.remove('hs-glow');
                btn.removeEventListener('animationend', handler);
            });
        }
    });

    // ===== Efek "menyala" untuk link biasa (hs-link & hs-sub-link) =====
    nav.addEventListener('click', function (e) {
        var link = e.target.closest('.hs-link, .hs-sub-link');
        if (!link || !nav.contains(link)) return;
        link.classList.remove('hs-glow');
        void link.offsetWidth;
        link.classList.add('hs-glow');
    });

    // ===== Mobile open/close =====
    var sidebar = document.getElementById('home-sidebar');
    var overlay = document.getElementById('home-sidebar-overlay');
    var toggleBtn = document.getElementById('home-sidebar-toggle');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('open');
    }
    function closeSidebar() {
        if (window.innerWidth < 1024) sidebar.classList.add('-translate-x-full');
        overlay.classList.remove('open');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
})();
</script>