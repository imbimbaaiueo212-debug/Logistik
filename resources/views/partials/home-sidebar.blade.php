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
        transition: background-color .15s ease, color .15s ease;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
    }
    .hs-link:hover, .hs-toggle:hover {
        background-color: rgba(255,255,255,0.06);
        color: #fff;
    }
    .hs-link.active, .hs-toggle.has-active {
        background-color: rgba(232,93,42,0.16);
        color: #fff;
    }
    .hs-link.active { box-shadow: inset 3px 0 0 #E85D2A; }

    .hs-chevron { width: 14px; height: 14px; opacity: .55; flex-shrink: 0; transition: transform .18s ease; }
    .hs-toggle.open .hs-chevron { transform: rotate(90deg); }

    .hs-sub {
        display: none;
        margin: 2px 0 4px 30px;
        padding-left: 10px;
        border-left: 1px solid rgba(255,255,255,0.12);
    }
    .hs-sub.open { display: block; }

    .hs-sub-link {
        display: block;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12.5px;
        color: rgba(255,255,255,0.6);
        transition: background-color .15s ease, color .15s ease;
    }
    .hs-sub-link:hover { background-color: rgba(255,255,255,0.06); color: #fff; }
    .hs-sub-link.active { color: #fff; font-weight: 600; background-color: rgba(232,93,42,0.16); }

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
        transition: background-color .15s ease, color .15s ease;
    }
    .hs-sub-toggle:hover { background-color: rgba(255,255,255,0.06); color: #fff; }
    .hs-sub-toggle.has-active { color: #fff; font-weight: 600; }
    .hs-sub-toggle .hs-chevron { width: 12px; height: 12px; }
    .hs-sub-toggle.open .hs-chevron { transform: rotate(90deg); }
    .hs-sub .hs-sub { margin: 2px 0 4px 8px; padding-left: 8px; }
    .hs-sub .hs-sub .hs-sub { margin-left: 4px; padding-left: 6px; }

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
    }
    #home-sidebar-overlay.open { display: block; }

    #home-sidebar-toggle {
        position: fixed; top: 22px; left: 18px; z-index: 45;
        width: 40px; height: 40px; border-radius: 12px;
        background: #162749; color: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px -8px rgba(15,27,51,0.4);
    }
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
            <a href="{{ route('user.export') }}" class="hs-sub-link {{ request()->routeIs('user.export') ? 'active' : '' }}">biMBA Shop</a>
            <a href="{{ route('unit-kemitraan.index') }}" class="hs-sub-link {{ request()->routeIs('unit-kemitraan.*') ? 'active' : '' }}">Unit Kemitraan</a>
            <a href="{{ route('unit-kemitraan-user.index') }}" class="hs-sub-link {{ request()->routeIs('unit-kemitraan-user.*') ? 'active' : '' }}">Unit + User Matching</a>
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
            <a href="{{ route('stokis.index') }}" class="hs-sub-link {{ request()->routeIs('stokis.*') ? 'active' : '' }}">Aktif</a>
            <a href="{{ route('stokis-pasif.index') }}" class="hs-sub-link {{ request()->routeIs('stokis-pasif.*') ? 'active' : '' }}">Pasif</a>
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
                'order-manual.*','import.dlc.*','import.manual','import.manual.*',
            ]);
            $hsPemesananActive = $hsMajalahActive || request()->routeIs([
                'order.*','import.bimbashop','import.bimbashop.*',
                'order-manual-modul.*','order-manual-sertifikat.*',
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
            <p class="hs-sub-label">Entry biMBA Shop</p>
            <a href="{{ route('order.index') }}" class="hs-sub-link {{ request()->routeIs('order.index') ? 'active' : '' }}">Ringkasan Order</a>
            <a href="{{ route('import.bimbashop') }}" class="hs-sub-link {{ request()->routeIs('import.bimbashop','import.bimbashop.*') ? 'active' : '' }}">Import biMBA Shop</a>

            <p class="hs-sub-label">Entry Manual</p>

            {{-- Majalah (dropdown) --}}
            <button type="button" data-hs-toggle="#hs-majalah" class="hs-sub-toggle {{ $hsMajalahActive ? 'open has-active' : '' }}">
                <span>Majalah</span>
                <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <div id="hs-majalah" class="hs-sub {{ $hsMajalahActive ? 'open' : '' }}">

                {{-- Unit OPS2 (dropdown) --}}
                <button type="button" data-hs-toggle="#hs-ops2" class="hs-sub-toggle {{ $hsOps2Active ? 'open has-active' : '' }}">
                    <span>Unit OPS2</span>
                    <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div id="hs-ops2" class="hs-sub {{ $hsOps2Active ? 'open' : '' }}">
                    <a href="{{ route('pesanan-majalah.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah.*') ? 'active' : '' }}">KORWIL</a>
                    <a href="{{ route('pesanan-majalah-kotamadya.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah-kotamadya.*') ? 'active' : '' }}">PINWIL</a>
                    <a href="{{ route('pesanan-majalah-puw1.index') }}" class="hs-sub-link {{ request()->routeIs('pesanan-majalah-puw1.*') ? 'active' : '' }}">JABODETABEK (PUW1)</a>
                </div>

                <a href="{{ route('import.dlc.index') }}" class="hs-sub-link {{ request()->routeIs('import.dlc.*') ? 'active' : '' }}">DLC</a>
                {{-- Unit Pasif (dropdown) --}}
                <button type="button" data-hs-toggle="#hs-pasif" class="hs-sub-toggle {{ $hsPasifActive ? 'open has-active' : '' }}">
                    <span>Unit Pasif</span>
                    <svg class="hs-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <div id="hs-pasif" class="hs-sub {{ $hsPasifActive ? 'open' : '' }}">
                    @foreach($hsPasifMenu as [$label, $rute, $pola])
                        <a href="{{ route($rute) }}" class="hs-sub-link {{ request()->routeIs($pola) ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <a href="{{ route('import.manual') }}" class="hs-sub-link {{ request()->routeIs('import.manual','import.manual.*') ? 'active' : '' }}">Data Realisasi</a>
            </div>

            <a href="{{ route('order-manual-modul.manual') }}" class="hs-sub-link {{ request()->routeIs('order-manual-modul.*') ? 'active' : '' }}">Modul</a>
            <a href="{{ route('order-manual-sertifikat.manual') }}" class="hs-sub-link {{ request()->routeIs('order-manual-sertifikat.*') ? 'active' : '' }}">Sertifikat</a>
        </div>
        @endif

        @if($__u->hasModule('penjualan'))
        {{-- Penjualan --}}
        @php $hsPenjualanActive = request()->routeIs(['import.casdana','import.casdana.*','pengeluaran.*']); @endphp
        <button type="button" data-hs-toggle="#hs-penjualan" class="hs-toggle {{ $hsPenjualanActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5.5" width="17" height="13" rx="2"/><circle cx="12" cy="12" r="2.4"/></svg>
                Penjualan
            </span>
            <svg class="hs-chevron {{ $hsPenjualanActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-penjualan" class="hs-sub {{ $hsPenjualanActive ? 'open' : '' }}">
            <a href="{{ route('import.casdana') }}" class="hs-sub-link {{ request()->routeIs('import.casdana','import.casdana.*') ? 'active' : '' }}">Kasdana</a>
            <a href="{{ route('pengeluaran.index') }}" class="hs-sub-link {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">Pengeluaran</a>
        </div>
        @endif

        @if($__u->hasModule('persiapan'))
        {{-- Persiapan --}}
        <a href="{{ route('picking.index') }}" class="hs-link {{ request()->routeIs('picking.*') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5Z"/><path d="M4 8.5 12 13l8-4.5"/></svg>
                Persiapan
            </span>
        </a>
        @endif

        @if($__u->hasModule('qc'))
        {{-- QC --}}
        <a href="{{ route('qc-outgoing.index') }}" class="hs-link {{ request()->routeIs('qc-outgoing.*') ? 'active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/></svg>
                QC
            </span>
        </a>
        @endif

        @if($__u->hasModule('distribusi'))
        {{-- Distribusi --}}
        @php $hsDistribusiActive = request()->routeIs('packing.*'); @endphp
        <button type="button" data-hs-toggle="#hs-distribusi" class="hs-toggle {{ $hsDistribusiActive ? 'open has-active' : '' }}">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16V7a1 1 0 0 1 1-1h9v10"/><path d="M13 10h4l4 3.5V16h-2"/><circle cx="7.5" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/></svg>
                Distribusi
            </span>
            <svg class="hs-chevron {{ $hsDistribusiActive ? 'open' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div id="hs-distribusi" class="hs-sub {{ $hsDistribusiActive ? 'open' : '' }}">
            <a href="{{ route('packing.index') }}" class="hs-sub-link {{ request()->routeIs('packing.*') ? 'active' : '' }}">Packing</a>
            <p class="hs-sub-label">Ekspedisi</p>
            <div class="hs-soon">Diambil <span class="hs-badge-soon">segera</span></div>
            <div class="hs-soon">Driver <span class="hs-badge-soon">segera</span></div>
            <div class="hs-soon">JNE <span class="hs-badge-soon">segera</span></div>
            <div class="hs-soon">Tiki <span class="hs-badge-soon">segera</span></div>
            <div class="hs-soon">Dll <span class="hs-badge-soon">segera</span></div>
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
                    class="block w-full text-center px-4 py-2 rounded-xl text-sm font-medium text-white transition-colors cursor-pointer"
                    style="background:#E85D2A;"
                    onmouseover="this.style.background='#D14E1F'" onmouseout="this.style.background='#E85D2A'">
                LOGOUT
            </button>
        </form>
    </div>
</aside>

<script>
(function () {
    // Accordion
    document.querySelectorAll('[data-hs-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.getAttribute('data-hs-toggle'));
            if (!target) return;
            target.classList.toggle('open');
            btn.classList.toggle('open');
        });
    });

    // Mobile open/close
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