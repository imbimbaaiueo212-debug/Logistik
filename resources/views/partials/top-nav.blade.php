<style>
.nav-dropdown-menu {
    display: none;
}
.nav-dropdown-menu.open {
    display: block;
}
</style>

<nav class="bg-white border-b border-gray-200 py-3 px-6 flex items-center justify-between shadow-sm">

    <div class="flex items-center shrink-0">
        <img src="/public/assets/img/logotulisan.png"
             alt="biMBA-AIUEO"
             class="h-9 w-auto object-contain">
    </div>

    <div class="flex items-center gap-5 text-sm font-medium flex-wrap justify-center">

        <a href="{{ route('dashboard') }}"
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

        <a href="{{ route('import.index') }}"
           class="{{ request()->routeIs(['import.index', 'import.bimbashop', 'import.bimbashop.*', 'import.casdana', 'import.casdana.*'])
                ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Data Import
        </a>

        {{-- Order Manual: klik untuk buka, klik luar untuk tutup --}}
        <div class="relative" id="orderManualDropdown">
            <button type="button" id="orderManualBtn"
                    class="{{ request()->routeIs([
                        'order-manual.*',
                        'order-manual-modul.*',
                        'order-manual-sertifikat.*',
                        'import.manual', 'import.manual.*',
                        'import.manual-printed', 'import.manual-printed.*', 'import.manual-print-*',
                        'import.sync-pesanan-majalah',
                        'import.dlc.*', 'import.pasif.*',
                        'ops2.index', 'ops2.*',
                        'pesanan-majalah.*', 'pesanan-majalah-kotamadya.*', 'pesanan-majalah-puw1.*',
                    ]) ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap inline-flex items-center gap-1">
                Order Manual
                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="orderManualMenu"
                 class="nav-dropdown-menu absolute left-0 top-full mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50">
                <a href="{{ route('order-manual.index') }}"
                    class="block px-4 py-2.5 text-sm {{ request()->routeIs('order-manual.*') && !request()->routeIs('order-manual-modul.*') && !request()->routeIs('order-manual-sertifikat.*') ? 'text-blue-600 font-semibold bg-blue-50' : 'text-gray-700' }} hover:bg-blue-50 hover:text-blue-700">
                    Majalah
                </a>
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

        <a href="{{ route('order.index') }}"
           class="{{ request()->routeIs('order.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 whitespace-nowrap">
            Order
        </a>

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
    const btn = document.getElementById('orderManualBtn');
    const menu = document.getElementById('orderManualMenu');
    const wrap = document.getElementById('orderManualDropdown');

    if (!btn || !menu || !wrap) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('open');
    });

    // Klik di dalam menu: jangan langsung tutup (biar bisa pilih link)
    menu.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // Klik sembarang di luar → tutup
    document.addEventListener('click', function () {
        menu.classList.remove('open');
    });
})();
</script>