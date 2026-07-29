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
        <h1 class="text-3xl font-bold tracking-[-0.04em] leading-none"
            style="font-family: 'Fredoka', sans-serif;">
            <span style="color:#000e8e;">b</span>
            <span style="color:#000e8e;">i</span>
            <span style="color:#f44040;">M</span>
            <span style="color:#000e8e;">B</span>
            <span style="color:#000e8e;">A</span>
            <span style="color:#1e40af;">-AIUEO</span>
        </h1>
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

        <a href="{{ route('import.index') }}"
           class="{{ request()->routeIs('import.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Data Import
        </a>
        
        <a href="{{ route('order-manual.index') }}"
            class="{{ request()->routeIs('order-manual.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
            Order Manual
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