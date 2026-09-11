<style>
.nav-dropdown-menu {
    display: none;
}
.nav-dropdown-menu.open {
    display: block;
}

/* Submenu ke samping */
.nav-submenu {
    display: none;
    position: absolute;
    left: 100%;
    top: 0;
    margin-left: 2px;
    min-width: 240px;
}
.nav-submenu.open {
    display: block;
}

/* Accordion (expand ke bawah) */
.nav-accordion-content {
    display: none;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}
.nav-accordion-content.open {
    display: block;
}

.nav-has-children {
    position: relative;
}
.nav-has-children > button {
    width: 100%;
    text-align: left;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Panah accordion */
.accordion-arrow {
    transition: transform 0.2s ease;
}
.accordion-arrow.rotate {
    transform: rotate(90deg);
}
</style>

<nav class="bg-white border-b border-gray-200 py-3 px-6 flex items-center justify-between shadow-sm">

    {{-- Logo --}}
    <div class="flex items-center shrink-0">
        <img src="/public/assets/img/logotulisan.png"
             alt="biMBA-AIUEO"
             class="h-9 w-auto object-contain">
    </div>

    {{-- Menu Utama --}}
    <div class="flex items-center gap-5 text-sm font-medium flex-wrap justify-center">

        <a href="#"
           class="{{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Dashboard
        </a>

        <a href="{{ route('home') }}"
           class="{{ request()->routeIs('home') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Home
        </a>

        <a href="{{ route('database-user.index') }}"
           class="{{ request()->routeIs('database-user.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Database User
        </a>

        {{-- ==================== ORDER ==================== --}}
        <div class="relative" id="orderDropdown">
            <button type="button" id="orderBtn"
                    class="{{ request()->routeIs([
                        'order.*',
                        'import.bimbashop', 'import.bimbashop.*',
                        'import.casdana', 'import.casdana.*',
                        'order-manual.*',
                        'order-manual-modul.*',
                        'order-manual-sertifikat.*',
                        'ops2.*',
                        'import.dlc.*',
                        'import.pasif.*',
                        'import.manual',
                        'pesanan-majalah.*',
                        'pesanan-majalah-kotamadya.*',
                        'pesanan-majalah-puw1.*',
                        'import.report-angka-cetak',
                    ]) ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap inline-flex items-center gap-1">
                Order
                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Level 1 --}}
            <div id="orderMenu"
                 class="nav-dropdown-menu absolute left-0 top-full mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50">

                {{-- ===== BS ===== --}}
                <div class="nav-has-children" id="bsItem">
                    <button type="button"
                            class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                        <span>BS</span>
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div class="nav-submenu bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50" id="bsSubmenu">
                        <a href="{{ route('import.bimbashop') }}"
                           class="block px-4 py-2.5 text-sm {{ request()->routeIs('import.bimbashop', 'import.bimbashop.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                            Bimba Shop
                        </a>
                        <a href="{{ route('import.casdana') }}"
                           class="block px-4 py-2.5 text-sm {{ request()->routeIs('import.casdana', 'import.casdana.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                            Casdana
                        </a>

                        {{-- Rekap --}}
                        <div class="nav-has-children" id="rekapItem">
                            <button type="button"
                                    class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                <span>Rekap</span>
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div class="nav-submenu bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50" id="rekapSubmenu" style="min-width: 280px;">
                                <a href="{{ route('order.unit-aktif') }}"
                                   class="block px-4 py-2.5 text-sm {{ request()->routeIs('order.unit-aktif') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                                    Data Order Unit Stokis Aktif
                                </a>

                                {{-- Data Order Unit Stokis Pasif --}}
                                <div class="nav-has-children" id="unitPasifItem">
                                    <button type="button"
                                            class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                        <span>Data Order Unit Stokis Pasif</span>
                                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div class="nav-submenu bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50" id="unitPasifSubmenu" style="min-width: 220px;">

                                        {{-- Jakarta Aktif (ACCORDION) --}}
                                        <div id="jakartaAktifItem">
                                            <button type="button" id="jakartaAktifBtn"
                                                    class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                                <span>Jakarta Aktif</span>
                                                <svg class="w-3.5 h-3.5 opacity-60 accordion-arrow" id="jakartaAktifArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>

                                            <div class="nav-accordion-content" id="jakartaAktifContent">
                                                <a href="{{ route('order.jakarta-aktif.realisasi') }}"
                                                   class="block px-6 py-2.5 text-sm {{ request()->routeIs('order.jakarta-aktif.realisasi') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                                    📋 Realisasi
                                                </a>
                                                <a href="{{ route('order.jakarta-aktif') }}"
                                                   class="block px-6 py-2.5 text-sm {{ request()->routeIs('order.jakarta-aktif') && !request()->routeIs('order.jakarta-aktif.realisasi') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                                    📊 Rekap Aktual
                                                </a>
                                            </div>
                                        </div>

                                        <a href="{{ route('order.jakarta-pasif') }}"
                                           class="block px-4 py-2.5 text-sm {{ request()->routeIs('order.jakarta-pasif') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                                            Jakarta Pasif
                                        </a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Logistik</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Semarang</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Surabaya</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Inventaris</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">InterVio (DLC)</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">English biMBA Talk (EBT)</a>
                                        <a href="#" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">Soccer School (biMBA SS)</a>
                                    </div>
                                </div>

                                <a href="#"
                                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                                    Data Order Unit Distribution Point (Dropshipper)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Manual ===== --}}
                <div class="nav-has-children" id="manualItem">
                    <button type="button"
                            class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                        <span>Manual</span>
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div class="nav-submenu bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50" id="manualSubmenu">

                        <div class="nav-has-children" id="majalahItem">
                            <button type="button"
                                    class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                <span>Majalah</span>
                                <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div class="nav-submenu bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50" id="majalahSubmenu" style="min-width: 260px;">

                                {{-- OPS2 (ACCORDION) --}}
                                <div id="ops2Item">
                                    <button type="button" id="ops2Btn"
                                            class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                        <span>Unit Operasional 2 (OPS2)</span>
                                        <svg class="w-3.5 h-3.5 opacity-60 accordion-arrow" id="ops2Arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div class="nav-accordion-content" id="ops2Content">
                                        <a href="{{ route('pesanan-majalah.index') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('pesanan-majalah.*') && !request()->routeIs('pesanan-majalah-kotamadya.*') && !request()->routeIs('pesanan-majalah-puw1.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            🏬 KORWIL
                                        </a>
                                        <a href="{{ route('pesanan-majalah-kotamadya.index') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('pesanan-majalah-kotamadya.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            🏬 PINWIL
                                        </a>
                                        <a href="{{ route('pesanan-majalah-puw1.index') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('pesanan-majalah-puw1.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            🏬 JABODETABEK (PUW1)
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('import.dlc.index') }}"
                                   class="block px-4 py-2.5 text-sm {{ request()->routeIs('import.dlc.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                                    DLC
                                </a>

                                {{-- Unit Pasif (ACCORDION) --}}
                                <div id="unitPasifManualItem">
                                    <button type="button" id="unitPasifManualBtn"
                                            class="w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center justify-between">
                                        <span>Unit Pasif</span>
                                        <svg class="w-3.5 h-3.5 opacity-60 accordion-arrow" id="unitPasifManualArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div class="nav-accordion-content" id="unitPasifManualContent">
                                        <a href="{{ route('import.pasif.list') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.pasif.list') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            📘 Unit Pasif
                                        </a>
                                        <a href="{{ route('import.pasif.spare') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.pasif.spare') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            📦 Spare Pasif 3%
                                        </a>
                                        <a href="{{ route('import.pasif.bacaan') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.pasif.bacaan') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            📖 Bacaan Unit
                                        </a>
                                        <a href="{{ route('import.pasif.rekap') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.pasif.rekap') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            📊 Import
                                        </a>
                                        <a href="{{ route('import.pasif.manual.index') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.pasif.manual.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            ✍️ Create Manual
                                        </a>
                                        <a href="{{ route('import.report-angka-cetak') }}"
                                           class="block px-6 py-2.5 text-sm {{ request()->routeIs('import.report-angka-cetak') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600' }} hover:bg-blue-50 hover:text-blue-700">
                                            📈 Report Angka Cetak
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('import.manual') }}"
                                   class="block px-4 py-2.5 text-sm {{ request()->routeIs('import.manual') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                                    Manual Pemesanan
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('order-manual-modul.index') }}"
                           class="block px-4 py-2.5 text-sm {{ request()->routeIs('order-manual-modul.*') || request()->is('order-manual-modul*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                            Modul
                        </a>

                        <a href="{{ route('order-manual-sertifikat.index') }}"
                           class="block px-4 py-2.5 text-sm {{ request()->routeIs('order-manual-sertifikat.*') || request()->is('order-manual-sertifikat*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                            Sertifikat
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <a href="{{ route('picking.index') }}"
           class="{{ request()->routeIs('picking.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Picking
        </a>

        <a href="{{ route('qc-outgoing.index') }}"
           class="{{ request()->routeIs('qc-outgoing.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            QC Outgoing
        </a>

        <a href="{{ route('packing.index') }}"
           class="{{ request()->routeIs('packing.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Packing
        </a>

        <a href="{{ route('distribution-order.index') }}"
           class="{{ request()->routeIs('distribution-order.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Distribution
        </a>
    </div>

    {{-- User + Logout --}}
    <div class="flex items-center gap-3 shrink-0">
        <span class="text-sm text-gray-700 font-medium whitespace-nowrap hidden lg:inline">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </span>
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl text-sm font-medium text-white transition-all whitespace-nowrap">
            LOGOUT
        </a>
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
(function () {
    const orderBtn         = document.getElementById('orderBtn');
    const orderMenu        = document.getElementById('orderMenu');
    const bsItem           = document.getElementById('bsItem');
    const bsSubmenu        = document.getElementById('bsSubmenu');
    const rekapItem        = document.getElementById('rekapItem');
    const rekapSubmenu     = document.getElementById('rekapSubmenu');
    const unitPasifItem    = document.getElementById('unitPasifItem');
    const unitPasifSubmenu = document.getElementById('unitPasifSubmenu');
    const manualItem       = document.getElementById('manualItem');
    const manualSubmenu    = document.getElementById('manualSubmenu');
    const majalahItem      = document.getElementById('majalahItem');
    const majalahSubmenu   = document.getElementById('majalahSubmenu');

    // Accordions
    const jakartaAktifBtn     = document.getElementById('jakartaAktifBtn');
    const jakartaAktifContent = document.getElementById('jakartaAktifContent');
    const jakartaAktifArrow   = document.getElementById('jakartaAktifArrow');

    const ops2Btn     = document.getElementById('ops2Btn');
    const ops2Content = document.getElementById('ops2Content');
    const ops2Arrow   = document.getElementById('ops2Arrow');

    const unitPasifManualBtn     = document.getElementById('unitPasifManualBtn');
    const unitPasifManualContent = document.getElementById('unitPasifManualContent');
    const unitPasifManualArrow   = document.getElementById('unitPasifManualArrow');

    if (!orderBtn || !orderMenu) return;

    // Klik Order
    orderBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = orderMenu.classList.contains('open');
        closeAll();
        if (!isOpen) orderMenu.classList.add('open');
    });

    // Hover side menus
    bsItem.addEventListener('mouseenter', function () {
        closeSideSubmenus();
        bsSubmenu.classList.add('open');
    });

    rekapItem.addEventListener('mouseenter', function () {
        rekapSubmenu.classList.add('open');
        unitPasifSubmenu.classList.remove('open');
    });

    unitPasifItem.addEventListener('mouseenter', function () {
        unitPasifSubmenu.classList.add('open');
    });

    manualItem.addEventListener('mouseenter', function () {
        closeSideSubmenus();
        manualSubmenu.classList.add('open');
    });

    majalahItem.addEventListener('mouseenter', function () {
        majalahSubmenu.classList.add('open');
    });

    // Helper accordion
    function toggleAccordion(content, arrow) {
        const isOpen = content.classList.contains('open');
        if (isOpen) {
            content.classList.remove('open');
            arrow.classList.remove('rotate');
        } else {
            content.classList.add('open');
            arrow.classList.add('rotate');
        }
    }

    // Accordion Jakarta Aktif
    if (jakartaAktifBtn) {
        jakartaAktifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleAccordion(jakartaAktifContent, jakartaAktifArrow);
        });
    }

    // Accordion OPS2
    if (ops2Btn) {
        ops2Btn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleAccordion(ops2Content, ops2Arrow);
        });
    }

    // Accordion Unit Pasif (Manual)
    if (unitPasifManualBtn) {
        unitPasifManualBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleAccordion(unitPasifManualContent, unitPasifManualArrow);
        });
    }

    // Klik di dalam menu jangan tutup
    orderMenu.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // Klik di luar → tutup semua
    document.addEventListener('click', function () {
        closeAll();
    });

    function closeSideSubmenus() {
        bsSubmenu.classList.remove('open');
        rekapSubmenu.classList.remove('open');
        unitPasifSubmenu.classList.remove('open');
        manualSubmenu.classList.remove('open');
        majalahSubmenu.classList.remove('open');
    }

    function closeAll() {
        orderMenu.classList.remove('open');
        closeSideSubmenus();

        // Tutup semua accordion
        [jakartaAktifContent, ops2Content, unitPasifManualContent].forEach(el => {
            if (el) el.classList.remove('open');
        });
        [jakartaAktifArrow, ops2Arrow, unitPasifManualArrow].forEach(el => {
            if (el) el.classList.remove('rotate');
        });
    }
})();
</script>