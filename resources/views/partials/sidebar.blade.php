<style>
    /* Accordion generik untuk sidebar */
    .side-accordion-content {
        display: none;
        overflow: hidden;
    }
    .side-accordion-content.open {
        display: block;
    }
    .side-accordion-arrow {
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .side-accordion-arrow.rotate {
        transform: rotate(90deg);
    }
    .side-accordion-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
    }

    #sidebar {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    #sidebar::-webkit-scrollbar {
        width: 6px;
    }
    #sidebar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 9999px;
    }
</style>

<aside id="sidebar"
       class="fixed top-0 left-0 h-screen w-64 bg-white border-r border-gray-200 shadow-sm flex flex-col overflow-y-auto">

    {{-- Logo --}}
    <div class="flex items-center px-5 py-4 border-b border-gray-200 shrink-0">
        <img src="/public/assets/img/logotulisan.png"
             alt="biMBA-AIUEO"
             class="h-8 w-auto object-contain">
    </div>

    {{-- Menu --}}
    <nav class="flex-1 px-3 py-4 text-sm font-medium">

        <a href="#"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Dashboard
        </a>

        <a href="{{ route('home') }}"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('home') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Home
        </a>

        <a href="{{ route('database-user.index') }}"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('database-user.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Database User
        </a>

        {{-- ==================== ORDER ==================== --}}
        @php
            $orderActive = request()->routeIs([
                'order.*', 'import.bimbashop', 'import.bimbashop.*',
                'import.casdana', 'import.casdana.*', 'order-manual.*',
                'order-manual-modul.*', 'order-manual-sertifikat.*', 'ops2.*',
                'import.dlc.*', 'import.pasif.*', 'import.manual',
                'pesanan-majalah.*', 'pesanan-majalah-kotamadya.*',
                'pesanan-majalah-puw1.*', 'import.report-angka-cetak',
            ]);
        @endphp

        <div class="mt-1">
            <button type="button" data-toggle="#menu-order"
                    class="side-accordion-btn px-3 py-2.5 rounded-lg {{ $orderActive ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                <span>Order</span>
                <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow {{ $orderActive ? 'rotate' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div id="menu-order" class="side-accordion-content {{ $orderActive ? 'open' : '' }} pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">

                {{-- ===== BS ===== --}}
                <div>
                    <button type="button" data-toggle="#menu-bs"
                            class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                        <span>BS</span>
                        <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div id="menu-bs" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">
                        <a href="{{ route('import.bimbashop') }}"
                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.bimbashop', 'import.bimbashop.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                            Bimba Shop
                        </a>
                        <a href="{{ route('import.casdana') }}"
                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.casdana', 'import.casdana.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                            Casdana
                        </a>

                        {{-- Rekap --}}
                        <div>
                            <button type="button" data-toggle="#menu-rekap"
                                    class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                <span>Rekap</span>
                                <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div id="menu-rekap" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">
                                <a href="{{ route('order.unit-aktif') }}"
                                   class="block px-3 py-2 rounded-lg {{ request()->routeIs('order.unit-aktif') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                    Data Order Unit Stokis Aktif
                                </a>

                                {{-- Data Order Unit Stokis Pasif --}}
                                <div>
                                    <button type="button" data-toggle="#menu-unit-pasif"
                                            class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                        <span class="text-left">Data Order Unit Stokis Pasif</span>
                                        <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div id="menu-unit-pasif" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">

                                        {{-- Jakarta Aktif --}}
                                        <div>
                                            <button type="button" data-toggle="#menu-jakarta-aktif"
                                                    class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                                <span>Jakarta Aktif</span>
                                                <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>

                                            <div id="menu-jakarta-aktif" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">
                                                <a href="{{ route('order.jakarta-aktif.realisasi') }}"
                                                   class="block px-3 py-2 rounded-lg {{ request()->routeIs('order.jakarta-aktif.realisasi') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                                    📋 Realisasi
                                                </a>
                                                <a href="{{ route('order.jakarta-aktif') }}"
                                                   class="block px-3 py-2 rounded-lg {{ request()->routeIs('order.jakarta-aktif') && !request()->routeIs('order.jakarta-aktif.realisasi') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                                    📊 Rekap Aktual
                                                </a>
                                            </div>
                                        </div>

                                        <a href="{{ route('order.jakarta-pasif') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('order.jakarta-pasif') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                            Jakarta Pasif
                                        </a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">Logistik</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">Semarang</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">Surabaya</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">Inventaris</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">InterVio (DLC)</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">English biMBA Talk (EBT)</a>
                                        <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">Soccer School (biMBA SS)</a>
                                    </div>
                                </div>

                                <a href="#" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                    Data Order Unit Distribution Point (Dropshipper)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Manual ===== --}}
                <div>
                    <button type="button" data-toggle="#menu-manual"
                            class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                        <span>Manual</span>
                        <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div id="menu-manual" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">

                        <div>
                            <button type="button" data-toggle="#menu-majalah"
                                    class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                <span>Majalah</span>
                                <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div id="menu-majalah" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">

                                {{-- OPS2 --}}
                                <div>
                                    <button type="button" data-toggle="#menu-ops2"
                                            class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                        <span class="text-left">Unit Operasional 2 (OPS2)</span>
                                        <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div id="menu-ops2" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">
                                        <a href="{{ route('pesanan-majalah.index') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('pesanan-majalah.*') && !request()->routeIs('pesanan-majalah-kotamadya.*') && !request()->routeIs('pesanan-majalah-puw1.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            🏬 KORWIL
                                        </a>
                                        <a href="{{ route('pesanan-majalah-kotamadya.index') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('pesanan-majalah-kotamadya.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            🏬 PINWIL
                                        </a>
                                        <a href="{{ route('pesanan-majalah-puw1.index') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('pesanan-majalah-puw1.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            🏬 JABODETABEK (PUW1)
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('import.dlc.index') }}"
                                   class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.dlc.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                    DLC
                                </a>

                                {{-- Unit Pasif (Manual) --}}
                                <div>
                                    <button type="button" data-toggle="#menu-unit-pasif-manual"
                                            class="side-accordion-btn px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                        <span>Unit Pasif</span>
                                        <svg class="w-3.5 h-3.5 opacity-60 side-accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>

                                    <div id="menu-unit-pasif-manual" class="side-accordion-content pl-3 border-l border-gray-100 ml-4 mt-1 space-y-1">
                                        <a href="{{ route('import.pasif.list') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.pasif.list') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            📘 Unit Pasif
                                        </a>
                                        <a href="{{ route('import.pasif.spare') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.pasif.spare') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            📦 Spare Pasif 3%
                                        </a>
                                        <a href="{{ route('import.pasif.bacaan') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.pasif.bacaan') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            📖 Bacaan Unit
                                        </a>
                                        <a href="{{ route('import.pasif.rekap') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.pasif.rekap') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            📊 Import
                                        </a>
                                        <a href="{{ route('import.pasif.manual.index') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.pasif.manual.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            ✍️ Create Manual
                                        </a>
                                        <a href="{{ route('import.report-angka-cetak') }}"
                                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.report-angka-cetak') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }}">
                                            📈 Report Angka Cetak
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('import.manual') }}"
                                   class="block px-3 py-2 rounded-lg {{ request()->routeIs('import.manual') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                    Manual Pemesanan
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('order-manual-modul.index') }}"
                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('order-manual-modul.*') || request()->is('order-manual-modul*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                            Modul
                        </a>

                        <a href="{{ route('order-manual-sertifikat.index') }}"
                           class="block px-3 py-2 rounded-lg {{ request()->routeIs('order-manual-sertifikat.*') || request()->is('order-manual-sertifikat*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                            Sertifikat
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <a href="{{ route('picking.index') }}"
           class="flex items-center px-3 py-2.5 rounded-lg mt-1 {{ request()->routeIs('picking.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Picking
        </a>

        <a href="{{ route('qc-outgoing.index') }}"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('qc-outgoing.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            QC Outgoing
        </a>

        <a href="{{ route('packing.index') }}"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('packing.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Packing
        </a>

        <a href="{{ route('distribution-order.index') }}"
           class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('distribution-order.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
            Distribution
        </a>
    </nav>

    {{-- User + Logout --}}
    <div class="border-t border-gray-200 p-4 shrink-0">
        <p class="text-sm text-gray-700 font-medium mb-2 truncate">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </p>
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="block text-center bg-red-600 hover:bg-red-700 px-4 py-2 rounded-xl text-sm font-medium text-white transition-all">
            LOGOUT
        </a>
    </div>
</aside>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

{{--
    Beri jarak konten utama agar tidak tertutup sidebar, misal:
    <div class="ml-64"> ...konten halaman... </div>
--}}

<script>
(function () {
    document.querySelectorAll('[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.getAttribute('data-toggle'));
            var arrow  = btn.querySelector('.side-accordion-arrow');
            if (!target) return;

            target.classList.toggle('open');
            if (arrow) arrow.classList.toggle('rotate');
        });
    });
})();
</script>