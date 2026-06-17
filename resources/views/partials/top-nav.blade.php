<nav class="bg-white border-b border-gray-200 py-4 px-6 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-8">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold tracking-[-0.04em] leading-none" style="font-family: 'Fredoka', sans-serif;">
                <span style="color: #000e8e;">b</span>
                <span style="color: #000e8e;">i</span>
                <span style="color: #f44040;">M</span>
                <span style="color: #000e8e;">B</span>
                <span style="color: #000e8e;">A</span>
                <span style="color: #1e40af;">-AIUEO</span>
            </h1>
        </div>

        <!-- Main Navigation -->
        <div class="flex items-center gap-8 text-sm font-medium">
            
            <a href="{{ route('home') }}" 
               class="hover:text-blue-600 transition-colors {{ request()->routeIs('home') ? 'text-blue-600' : 'text-gray-700' }}">
                Home
            </a>

            <!-- ==================== DATA IMPORT ==================== -->
            <div class="relative group">
                <button class="flex items-center gap-1 hover:text-blue-600 transition-colors {{ request()->is('import/*') ? 'text-blue-600' : 'text-gray-700' }}">
                    Data Import
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <div class="absolute hidden group-hover:block pt-2 z-50">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-56">
                        <a href="{{ route('import.bimbashop') }}" 
                           class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors">
                            🏪 biMBA Shop
                        </a>
                        <a href="{{ route('import.casdana') }}" 
                           class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors">
                            💰 Kasdana
                        </a>
                    </div>
                </div>
            </div>

            <!-- ==================== ORDER MENU ==================== -->
            <div class="relative group">
                <button class="flex items-center gap-1 hover:text-blue-600 transition-colors {{ request()->is('order/*') ? 'text-blue-600' : 'text-gray-700' }}">
                    Order
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                
                <div class="absolute hidden group-hover:block pt-2 z-50">
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-72">

                        <div class="border-t my-2"></div>

                        <a href="{{ route('order.unit-aktif') }}" 
                           class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors pl-8">
                            🎯 Data Order Unit Stokis Aktif
                        </a>
                        
                        <a href="{{ route('order.unit-pasif') }}" 
                           class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors pl-8">
                            🎯 Data Order Unit Stokis Pasif
                        </a>
                        
                        <a href="#" 
                           class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors pl-8 opacity-75 cursor-not-allowed">
                            🎯 Data Order Unit Distribution Point (Dropshipper)
                        </a>

                        <div class="border-t my-2"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Right Side -->
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-700 font-medium">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </span>
        
        <a href="{{ route('logout') }}" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="bg-red-600 hover:bg-red-700 px-5 py-2 rounded-xl text-sm font-medium text-white transition-all">
            LOGOUT
        </a>
    </div>
</nav>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>