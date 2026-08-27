<style>
.group2:hover .group2-hover\:block {
    display: block;
}

.group3:hover .group3-hover\:block {
    display: block;
}
</style>

<nav class="bg-white border-b border-gray-200 py-4 px-8 flex items-center justify-between shadow-sm">

    <!-- Left Side: Logo -->
    <div class="flex items-center">
    <img src="/template/assets/img/logotulisan.png" 
         alt="biMBA-AIUEO" 
         class="h-10 w-auto object-contain">
</div>

    <!-- Center: Menu -->
    <div class="flex items-center gap-8 text-sm font-medium">
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Dashboard
        </a>

        <a href="{{ route('home') }}"
           class="{{ request()->routeIs('home') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Home
        </a>

        <a href="{{ route('database-user.index') }}"
           class="{{ request()->routeIs('database-user.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Database User
        </a>

        {{-- Data Import: HANYA Bimba Shop + Cash Dana --}}
        {{-- Data Import: HANYA Bimba Shop + Cash Dana --}}
        <a href="{{ route('import.index') }}"
        class="{{ request()->routeIs([
                    'import.index',
                    'import.bimbashop',
                    'import.bimbashop.*',
                    'import.casdana',
                    'import.casdana.*',
                ])
                    ? 'text-blue-600 font-semibold'
                    : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Data Import
        </a>

        {{-- Order Manual Majalah: Manual + DLC + Pasif + OPS2 + Pesanan Majalah --}}
        <a href="{{ route('order-manual.index') }}"
        class="{{ request()->routeIs([
                    'order-manual.*',

                    // Manual Pemesanan
                    'import.manual',
                    'import.manual.*',
                    'import.manual-printed',
                    'import.manual-printed.*',
                    'import.manual-print-*',
                    'import.sync-pesanan-majalah',

                    // DLC + Pasif + Pasif Manual
                    'import.dlc.*',
                    'import.pasif.*',

                    // OPS2 + wilayah pesanan majalah
                    'ops2.index',
                    'ops2.*',
                    'pesanan-majalah.*',
                    'pesanan-majalah-kotamadya.*',
                    'pesanan-majalah-puw1.*',
                ])
                    ? 'text-blue-600 font-semibold'
                    : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Order Manual Majalah
        </a>

        <a href="{{ route('order.index') }}"
           class="{{ request()->routeIs('order.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Order
        </a>

        <a href="{{ route('picking.index') }}"
           class="{{ request()->routeIs('picking.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Picking
        </a>

        <a href="{{ route('qc-outgoing.index') }}"
           class="{{ request()->routeIs('qc-outgoing.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            QC Outgoing
        </a>

        <a href="{{ route('packing.index') }}"
           class="{{ request()->routeIs('packing.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Packing
        </a>

        <a href="{{ route('distribution-order.index') }}"
           class="{{ request()->routeIs('distribution-order.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Distribution
        </a>
    </div>

    <!-- Right Side: User Info + Logout -->
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-700 font-medium">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </span>

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault();document.getElementById('logout-form').submit();"
           class="bg-red-600 hover:bg-red-700 px-5 py-2.5 rounded-xl text-sm font-medium text-white transition-all">
            LOGOUT
        </a>
    </div>

</nav>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>