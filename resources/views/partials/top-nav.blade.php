<style>
/* ===========================================================
   CoLabs-style Floating Pill Navbar
   =========================================================== */
.nav-dropdown-menu {
    display: block;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateY(-6px);
    transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
}
.nav-dropdown-menu.open {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .nav-dropdown-menu { transition: none; }
}

.nav-item-active {
    color: #D14E1F;
    font-weight: 600;
    background-color: #FBECE4;
}
.nav-link-active {
    color: #E85D2A;
    font-weight: 600;
}

/* ===== Chevron ===== */
.nav-chevron {
    transition: transform 0.22s ease;
}
.nav-btn-open .nav-chevron {
    transform: rotate(180deg);
}

/* Megamenu */
.nav-mega {
    width: 100%;
}
.nav-mega-inner {
    max-width: 1180px;
}
.mega-col-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #8892A8;
    margin-bottom: 10px;
}
.mega-sub-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #4B5670;
    margin-top: 12px;
    margin-bottom: 2px;
}
.mega-link {
    display: block;
    padding: 6px 8px;
    margin: 0 -8px;
    border-radius: 8px;
    font-size: 13.5px;
    color: #28304A;
    line-height: 1.5;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
}
.mega-link:hover {
    background-color: #FBECE4;
    color: #D14E1F;
    transform: translateX(2px);
}
.mega-link.indent {
    padding-left: 16px;
    font-size: 13px;
    color: #5C6884;
}
.mega-step {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 6px;
    background: #162749;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    margin-right: 8px;
    flex-shrink: 0;
}

/* Stagger animation */
@keyframes megaColIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.nav-dropdown-menu .nav-mega-inner > * {
    opacity: 0;
}
.nav-dropdown-menu.open .nav-mega-inner > * {
    animation: megaColIn 0.32s cubic-bezier(.22,.9,.32,1) forwards;
}
.nav-dropdown-menu.open .nav-mega-inner > *:nth-child(1) { animation-delay: 0.02s; }
.nav-dropdown-menu.open .nav-mega-inner > *:nth-child(2) { animation-delay: 0.06s; }
.nav-dropdown-menu.open .nav-mega-inner > *:nth-child(3) { animation-delay: 0.10s; }
.nav-dropdown-menu.open .nav-mega-inner > *:nth-child(4) { animation-delay: 0.14s; }
.nav-dropdown-menu.open .nav-mega-inner > *:nth-child(5) { animation-delay: 0.18s; }

@media (prefers-reduced-motion: reduce) {
    .nav-dropdown-menu .nav-mega-inner > *,
    .nav-dropdown-menu.open .nav-mega-inner > * {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}

.nav-dropdown-menu:not(.nav-mega) a {
    opacity: 0;
}
.nav-dropdown-menu.open:not(.nav-mega) a {
    animation: megaColIn 0.22s ease forwards;
}
.nav-dropdown-menu.open:not(.nav-mega) a:nth-child(1) { animation-delay: 0.02s; }
.nav-dropdown-menu.open:not(.nav-mega) a:nth-child(2) { animation-delay: 0.06s; }
.nav-dropdown-menu.open:not(.nav-mega) a:nth-child(3) { animation-delay: 0.10s; }

/* Expand columns */
#unitPasifColumn,
#majalahColumn {
    display: block;
    overflow: hidden;
    max-height: 800px;
    opacity: 1;
    transition: max-height 0.32s ease, opacity 0.25s ease, margin 0.32s ease;
}
#unitPasifColumn.hidden,
#majalahColumn.hidden {
    max-height: 0;
    opacity: 0;
    margin: 0;
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    #unitPasifColumn, #majalahColumn { transition: none; }
}

/* ===== CoLabs style ===== */
.nav-wrapper {
    position: relative;
    z-index: 50;
    padding: 16px 24px 0;
}

.nav-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-radius: 9999px;
    padding: 10px 10px 10px 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);
    max-width: 1200px;
    margin: 0 auto;
}

.nav-logo {
    flex-shrink: 0;
    padding-right: 8px;
}

.nav-links-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #F5F0E8;
    border-radius: 9999px;
    padding: 6px 10px;
}

.nav-links-pill a,
.nav-links-pill button {
    font-size: 13.5px;
    font-weight: 500;
    color: #28304A;
    padding: 8px 14px;
    border-radius: 9999px;
    white-space: nowrap;
    transition: background-color 0.18s ease, color 0.18s ease;
    border: none;
    background: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.nav-links-pill a:hover,
.nav-links-pill button:hover {
    background: rgba(255,255,255,0.7);
    color: #E85D2A;
}

.nav-links-pill a.nav-link-active,
.nav-links-pill button.nav-link-active {
    background: #fff;
    color: #E85D2A;
    font-weight: 600;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-left: 12px;
    padding-right: 6px;
}

.btn-logout {
    background: #E85D2A;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 9999px;
    transition: background-color 0.2s ease, transform 0.12s ease;
    white-space: nowrap;
}
.btn-logout:hover {
    background: #D14E1F;
}
.btn-logout:active {
    transform: scale(0.96);
}

/* Entrance animation */
@keyframes navFadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.nav-enter {
    animation: navFadeIn 0.45s ease forwards;
}
@media (prefers-reduced-motion: reduce) {
    .nav-enter { animation: none; }
}
</style>

<div class="nav-wrapper nav-enter" style="font-family: 'Poppins', sans-serif;">
    <nav class="nav-pill">

        {{-- Logo --}}
        <div class="nav-logo">
            <img src="/public/assets/img/logotulisan.png"
                 alt="biMBA-AIUEO"
                 class="h-8 w-auto object-contain">
        </div>

        {{-- Menu Links (pill) --}}
        <div class="nav-links-pill">

            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'nav-link-active' : '' }}">
                Home
            </a>

            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                Database Master Gudang
            </a>

            {{-- Database User --}}
            <div class="relative" id="databaseDropdown">
                <button type="button" id="databaseBtn"
                        class="{{ request()->routeIs(['user.export','unit-kemitraan.*','unit-kemitraan-user.*','database-user.*']) ? 'nav-link-active' : '' }}">
                    Database User
                    <svg class="nav-chevron w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="databaseMenu"
                     class="nav-dropdown-menu absolute left-0 top-full mt-3 w-60 bg-white border border-[#E4E8F0] rounded-2xl shadow-xl py-2 z-50">
                    <a href="{{ route('user.export') }}"
                       class="block px-4 py-2.5 text-sm {{ request()->routeIs('user.export') ? 'nav-item-active' : 'text-[#28304A]' }} hover:bg-[#FBECE4] hover:text-[#D14E1F] rounded-lg mx-1">
                        User biMBA Shop
                    </a>
                    <a href="{{ route('unit-kemitraan.index') }}"
                       class="block px-4 py-2.5 text-sm {{ request()->routeIs('unit-kemitraan.*') ? 'nav-item-active' : 'text-[#28304A]' }} hover:bg-[#FBECE4] hover:text-[#D14E1F] rounded-lg mx-1">
                        Unit Kemitraan
                    </a>
                    <a href="{{ route('unit-kemitraan-user.index') }}"
                       class="block px-4 py-2.5 text-sm {{ request()->routeIs('unit-kemitraan-user.*') ? 'nav-item-active' : 'text-[#28304A]' }} hover:bg-[#FBECE4] hover:text-[#D14E1F] rounded-lg mx-1">
                        Unit + User Matching
                    </a>
                </div>
            </div>

            {{-- Order --}}
            <div class="relative" id="orderDropdown">
                <button type="button" id="orderBtn"
                        class="{{ request()->routeIs([
                            'order.*','import.bimbashop','import.bimbashop.*','import.casdana','import.casdana.*',
                            'order-manual.*','order-manual-modul.*','order-manual-sertifikat.*','ops2.*',
                            'import.dlc.*','import.pasif.*','import.manual','pesanan-majalah.*',
                            'pesanan-majalah-kotamadya.*','pesanan-majalah-puw1.*','import.report-angka-cetak',
                        ]) ? 'nav-link-active' : '' }}">
                    Order
                    <svg class="nav-chevron w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- Proses --}}
            <div class="relative" id="prosesDropdown">
                <button type="button" id="prosesBtn"
                        class="{{ request()->routeIs(['picking.*','qc-outgoing.*','packing.*','distribution-order.*']) ? 'nav-link-active' : '' }}">
                    Proses
                    <svg class="nav-chevron w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

        </div>

        {{-- User + Logout --}}
        <div class="nav-right">
            <span class="text-sm text-[#28304A] font-medium whitespace-nowrap hidden lg:inline">
                Halo, {{ Auth::user()->name ?? 'Admin' }}
            </span>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="btn-logout">
                LOGOUT
            </a>
        </div>

    </nav>

    {{-- ===== ORDER MEGAMENU (full width di bawah pill) ===== --}}
    <div id="orderMenu"
         class="nav-mega nav-dropdown-menu absolute left-0 right-0 top-full mt-2 mx-6 bg-white border border-[#E4E8F0] rounded-2xl shadow-xl z-50">
        <div class="nav-mega-inner mx-auto px-8 py-7 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 max-h-[75vh] overflow-y-auto">

            {{-- Kolom 1: BS --}}
            <div>
                <p class="mega-col-label">BS</p>
                <a href="{{ route('import.bimbashop') }}" class="mega-link {{ request()->routeIs('import.bimbashop', 'import.bimbashop.*') ? 'nav-item-active' : '' }}">Bimba Shop</a>
                <a href="{{ route('import.casdana') }}" class="mega-link {{ request()->routeIs('import.casdana', 'import.casdana.*') ? 'nav-item-active' : '' }}">Casdana</a>

                <p class="mega-sub-label">Rekap</p>
                <a href="{{ route('order.unit-aktif') }}" class="mega-link indent {{ request()->routeIs('order.unit-aktif') ? 'nav-item-active' : '' }}">Data Order Unit Stokis Aktif</a>

                <button type="button" id="toggleUnitPasifBtn"
                        class="mega-link indent {{ request()->routeIs(['order.jakarta-aktif', 'order.jakarta-aktif.*', 'order.jakarta-pasif']) ? 'nav-item-active' : '' }}">
                    Data Order Unit Stokis Pasif >
                </button>

                <a href="#" class="mega-link indent">Data Order Unit Distribution Point (Dropshipper)</a>
            </div>

            {{-- Kolom 2: Unit Stokis Pasif --}}
            <div id="unitPasifColumn" class="hidden">
                <p class="mega-col-label">BS &middot; REKAP &middot; UNIT STOKIS PASIF</p>
                <p class="mega-sub-label" style="margin-top:0;">Jakarta Aktif</p>
                <a href="{{ route('order.jakarta-aktif.realisasi') }}" class="mega-link indent {{ request()->routeIs('order.jakarta-aktif.realisasi') ? 'nav-item-active' : '' }}">Realisasi</a>
                <a href="{{ route('order.jakarta-aktif') }}" class="mega-link indent {{ request()->routeIs('order.jakarta-aktif') && !request()->routeIs('order.jakarta-aktif.realisasi') ? 'nav-item-active' : '' }}">Rekap Aktual</a>

                <a href="{{ route('order.jakarta-pasif') }}" class="mega-link mt-2 {{ request()->routeIs('order.jakarta-pasif') ? 'nav-item-active' : '' }}">Jakarta Pasif</a>
                <a href="#" class="mega-link">Logistik</a>
                <a href="#" class="mega-link">Semarang</a>
                <a href="#" class="mega-link">Surabaya</a>
                <a href="#" class="mega-link">Inventaris</a>
                <a href="#" class="mega-link">InterVio (DLC)</a>
                <a href="#" class="mega-link">English biMBA Talk (EBT)</a>
                <a href="#" class="mega-link">Soccer School (biMBA SS)</a>
            </div>

            {{-- Kolom 3: MANUAL --}}
            <div>
                <p class="mega-col-label">MANUAL</p>
                <button type="button" id="toggleMajalahBtn"
                        class="mega-link {{ request()->routeIs(['pesanan-majalah.*','pesanan-majalah-kotamadya.*','pesanan-majalah-puw1.*','import.dlc.*','import.pasif.*','import.manual','import.report-angka-cetak']) ? 'nav-item-active' : '' }}">
                    Majalah >
                </button>
                <a href="{{ route('order-manual-modul.index') }}" class="mega-link {{ request()->routeIs('order-manual-modul.*') || request()->is('order-manual-modul*') ? 'nav-item-active' : '' }}">Modul</a>
                <a href="{{ route('order-manual-sertifikat.index') }}" class="mega-link {{ request()->routeIs('order-manual-sertifikat.*') || request()->is('order-manual-sertifikat*') ? 'nav-item-active' : '' }}">Sertifikat</a>
            </div>

            {{-- Kolom 4: Majalah Detail --}}
            <div id="majalahColumn" class="hidden">
                <p class="mega-col-label">MANUAL &middot; MAJALAH</p>
                <p class="mega-sub-label" style="margin-top:0;">Unit Operasional 2 (OPS2)</p>
                <a href="{{ route('pesanan-majalah.index') }}" class="mega-link indent {{ request()->routeIs('pesanan-majalah.*') && !request()->routeIs('pesanan-majalah-kotamadya.*') && !request()->routeIs('pesanan-majalah-puw1.*') ? 'nav-item-active' : '' }}">KORWIL</a>
                <a href="{{ route('pesanan-majalah-kotamadya.index') }}" class="mega-link indent {{ request()->routeIs('pesanan-majalah-kotamadya.*') ? 'nav-item-active' : '' }}">PINWIL</a>
                <a href="{{ route('pesanan-majalah-puw1.index') }}" class="mega-link indent {{ request()->routeIs('pesanan-majalah-puw1.*') ? 'nav-item-active' : '' }}">JABODETABEK (PUW1)</a>
                <a href="{{ route('import.dlc.index') }}" class="mega-link mt-2 {{ request()->routeIs('import.dlc.*') ? 'nav-item-active' : '' }}">DLC</a>
                <p class="mega-sub-label">Unit Pasif</p>
                <a href="{{ route('import.pasif.list') }}" class="mega-link indent {{ request()->routeIs('import.pasif.list') ? 'nav-item-active' : '' }}">Unit Pasif</a>
                <a href="{{ route('import.pasif.spare') }}" class="mega-link indent {{ request()->routeIs('import.pasif.spare') ? 'nav-item-active' : '' }}">Spare Pasif 3%</a>
                <a href="{{ route('import.pasif.bacaan') }}" class="mega-link indent {{ request()->routeIs('import.pasif.bacaan') ? 'nav-item-active' : '' }}">Bacaan Unit</a>
                <a href="{{ route('import.pasif.rekap') }}" class="mega-link indent {{ request()->routeIs('import.pasif.rekap') ? 'nav-item-active' : '' }}">Import</a>
                <a href="{{ route('import.pasif.manual.index') }}" class="mega-link indent {{ request()->routeIs('import.pasif.manual.*') ? 'nav-item-active' : '' }}">Create Manual</a>
                <a href="{{ route('import.report-angka-cetak') }}" class="mega-link indent {{ request()->routeIs('import.report-angka-cetak') ? 'nav-item-active' : '' }}">Report Angka Cetak</a>
                <a href="{{ route('import.manual') }}" class="mega-link mt-2 {{ request()->routeIs('import.manual') ? 'nav-item-active' : '' }}">Manual Pemesanan</a>
            </div>

            {{-- Kolom 5: Highlight --}}
            <div class="rounded-2xl p-5 flex flex-col justify-between" style="background: linear-gradient(150deg, #162749, #0F1B33);">
                <div>
                    <p class="text-white font-semibold text-[15px] leading-snug">Ringkasan seluruh order</p>
                    <p class="text-[12.5px] mt-1.5" style="color:#9FADC7">Lihat semua transaksi order dalam satu tampilan.</p>
                </div>
                <a href="{{ route('order.index') }}"
                   class="mt-4 inline-flex items-center justify-center bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-semibold rounded-lg px-3.5 py-2.5 transition-colors">
                    Buka Order
                </a>
            </div>
        </div>
    </div>

    {{-- ===== PROSES MEGAMENU ===== --}}
    <div id="prosesMenu"
         class="nav-mega nav-dropdown-menu absolute left-0 right-0 top-full mt-2 mx-6 bg-white border border-[#E4E8F0] rounded-2xl shadow-xl z-50">
        <div class="nav-mega-inner mx-auto px-8 py-7 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 max-h-[75vh] overflow-y-auto">

            <div>
                <p class="mega-col-label"><span class="mega-step">01</span>PICKING</p>
                <a href="{{ route('picking.jakarta.aktif') }}" class="mega-link {{ request()->routeIs('picking.jakarta.aktif') ? 'nav-item-active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('picking.jakarta.pasif') }}" class="mega-link {{ request()->routeIs('picking.jakarta.pasif') ? 'nav-item-active' : '' }}">Jakarta Pasif</a>
                <a href="#" class="mega-link">InterVio (DLC)</a>
                <a href="#" class="mega-link">English biMBA Talk</a>
                <a href="{{ route('picking.order-manual') }}" class="mega-link {{ request()->routeIs('picking.order-manual') ? 'nav-item-active' : '' }}">Order Manual</a>
            </div>

            <div>
                <p class="mega-col-label"><span class="mega-step">02</span>QC OUTGOING</p>
                <a href="{{ route('qc-outgoing.jakarta-aktif') }}" class="mega-link {{ request()->routeIs('qc-outgoing.jakarta-aktif') ? 'nav-item-active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('qc-outgoing.jakarta-pasif') }}" class="mega-link {{ request()->routeIs('qc-outgoing.jakarta-pasif') ? 'nav-item-active' : '' }}">Jakarta Pasif</a>
                <a href="#" class="mega-link">InterVio (DLC)</a>
                <a href="#" class="mega-link">English biMBA Talk</a>
                <a href="{{ route('qc-outgoing.order-manual') }}" class="mega-link {{ request()->routeIs('qc-outgoing.order-manual') ? 'nav-item-active' : '' }}">Order Manual</a>
            </div>

            <div>
                <p class="mega-col-label"><span class="mega-step">03</span>PACKING</p>
                <a href="{{ route('packing.jakarta.aktif') }}" class="mega-link {{ request()->routeIs('packing.jakarta.aktif') ? 'nav-item-active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('packing.jakarta-pasif') }}" class="mega-link {{ request()->routeIs('packing.jakarta-pasif') ? 'nav-item-active' : '' }}">Jakarta Pasif</a>
                <a href="#" class="mega-link">InterVio (DLC)</a>
                <a href="#" class="mega-link">English biMBA Talk</a>
                <a href="{{ route('packing.order-manual') }}" class="mega-link {{ request()->routeIs('packing.order-manual') ? 'nav-item-active' : '' }}">Order Manual</a>
            </div>

            <div>
                <p class="mega-col-label"><span class="mega-step">04</span>DISTRIBUTION</p>
                <a href="{{ route('distribution-order.jakarta-aktif') }}" class="mega-link {{ request()->routeIs('distribution-order.jakarta-aktif') ? 'nav-item-active' : '' }}">Jakarta Aktif</a>
                <a href="{{ route('distribution-order.jakarta-pasif') }}" class="mega-link {{ request()->routeIs('distribution-order.jakarta-pasif') ? 'nav-item-active' : '' }}">Jakarta Pasif</a>
                <a href="{{ route('distribution-order.intervio') }}" class="mega-link {{ request()->routeIs('distribution-order.intervio') ? 'nav-item-active' : '' }}">InterVio (DLC)</a>
                <a href="{{ route('distribution-order.ebt') }}" class="mega-link {{ request()->routeIs('distribution-order.ebt') ? 'nav-item-active' : '' }}">English biMBA Talk</a>
                <a href="{{ route('distribution-order.manual') }}" class="mega-link {{ request()->routeIs('distribution-order.manual') ? 'nav-item-active' : '' }}">Manual</a>
            </div>

            <div class="rounded-2xl p-5 flex flex-col justify-between" style="background: linear-gradient(150deg, #162749, #0F1B33);">
                <div>
                    <p class="text-white font-semibold text-[15px] leading-snug">Alur gudang lengkap</p>
                    <p class="text-[12.5px] mt-1.5" style="color:#9FADC7">Order &rarr; Picking &rarr; QC &rarr; Packing &rarr; Kirim.</p>
                </div>
                <a href="{{ route('dashboard') }}"
                   class="mt-4 inline-flex items-center justify-center bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-semibold rounded-lg px-3.5 py-2.5 transition-colors">
                    Buka Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
(function () {
    const databaseBtn  = document.getElementById('databaseBtn');
    const databaseMenu = document.getElementById('databaseMenu');
    const orderBtn     = document.getElementById('orderBtn');
    const orderMenu    = document.getElementById('orderMenu');
    const prosesBtn    = document.getElementById('prosesBtn');
    const prosesMenu   = document.getElementById('prosesMenu');

    const toggleUnitPasifBtn = document.getElementById('toggleUnitPasifBtn');
    const unitPasifColumn    = document.getElementById('unitPasifColumn');
    const toggleMajalahBtn   = document.getElementById('toggleMajalahBtn');
    const majalahColumn      = document.getElementById('majalahColumn');

    const pairs = [
        { btn: databaseBtn, menu: databaseMenu },
        { btn: orderBtn,    menu: orderMenu },
        { btn: prosesBtn,   menu: prosesMenu },
    ];

    function toggle(menu, btn) {
        const isOpen = menu.classList.contains('open');
        closeAll();
        if (!isOpen) {
            menu.classList.add('open');
            if (btn) btn.classList.add('nav-btn-open');
        }
    }

    pairs.forEach(function (pair) {
        if (!pair.btn || !pair.menu) return;
        pair.btn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggle(pair.menu, pair.btn);
        });
        pair.menu.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    if (toggleUnitPasifBtn && unitPasifColumn) {
        toggleUnitPasifBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            unitPasifColumn.classList.toggle('hidden');
        });
    }

    if (toggleMajalahBtn && majalahColumn) {
        toggleMajalahBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            majalahColumn.classList.toggle('hidden');
        });
    }

    document.addEventListener('click', function () { closeAll(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll();
    });

    function closeAll() {
        pairs.forEach(function (pair) {
            if (pair.menu) pair.menu.classList.remove('open');
            if (pair.btn) pair.btn.classList.remove('nav-btn-open');
        });
        if (unitPasifColumn) unitPasifColumn.classList.add('hidden');
        if (majalahColumn) majalahColumn.classList.add('hidden');
    }
})();
</script>