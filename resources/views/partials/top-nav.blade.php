<style>
.group2:hover .group2-hover\:block {
    display: block;
}

.group3:hover .group3-hover\:block {
    display: block;
}
</style>

<nav class="bg-white border-b border-gray-200 py-4 px-6 flex items-center justify-between shadow-sm">

    <div class="flex items-center gap-8">

        <!-- Logo -->
        <div class="flex items-center gap-3">
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

        <!-- Menu -->
        <div class="flex items-center gap-8 text-sm font-medium">

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
                Dashboard
            </a>

            <!-- Home -->
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600 transition-colors">
                Home
            </a>


            <div class="relative group">

    <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600 transition-colors">
        Import biMBA Shop & Kasdana
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-4 h-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Import -->
    <div class="absolute hidden group-hover:block pt-2 z-50">

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-72">

            <div class="border-t my-2"></div>

            <a href="{{ route('import.bimbashop') }}"
               class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors">
                🏪 Import biMBA Shop
            </a>

            <a href="{{ route('import.casdana') }}"
               class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600 transition-colors">
                💰 Import Kasdana
            </a>

        </div>

    </div>

</div>

            <!-- ================================================= -->
            <!-- ORDER -->
            <!-- ================================================= -->
            <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600 transition-colors">
                    Order
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Order -->
                <div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-80">

                        <!-- Stokis Aktif -->
                        <div class="relative group2">

                            <a href="{{ route('order.unit-aktif') }}"
                               class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">

                                <span>🎯 Data Order Unit Stokis Aktif</span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>

                            </a>

                            <!-- Level 2 -->
                            <div class="absolute hidden group2-hover:block left-full top-0 ml-2 z-50">

                                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-72">

                                    <!-- Jakarta -->
                                    <div class="relative group3">

                                        <a href="#"
                                           class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">

                                            <span>📍 Jakarta Aktif</span>
                                        </a>

                                        <a href="#"
                                           class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">

                                            <span>📍 Jakarta Aktif</span>
                                        </a>


                                    </div>


                                </div>

                            </div>

                        </div>

                        <!-- Stokis Pasif -->
                        <div class="relative group2">

                                        <a href="{{ route('order.unit-pasif') }}"
                                        class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">

                                            <span>🎯 Data Order Unit Stokis Pasif</span>
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-4 h-4"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 9l-7 7-7-7"/>
                                                        </svg>

                                        </a>

                                    <!-- SUBMENU PASIF -->
                                    <div class="absolute hidden group2-hover:block left-full top-0 ml-2 z-50">

                                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-72">

                                            <a href="{{ route('order.jakarta-aktif') }}"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Jakarta Aktif
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Jakarta Pasif
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Logistik
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Semarang
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Surabaya
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Inventaris
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 InterVio (DLC)
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 English biMBA Talk (EBT)
                                            </a>

                                            <a href="#"
                                            class="block px-5 py-3 hover:bg-gray-50 text-gray-700 hover:text-blue-600">
                                                📍 Soccer School (biMBA SS)
                                            </a>

                                        </div>

                                    </div>

                                </div>

                        <!-- Dropshipper -->
                        <a href="#"
                           class="block px-5 py-3 text-gray-400 cursor-not-allowed">
                            🎯 Data Order Unit Distribution Point
                        </a>

                    </div>

                </div>

            </div>

            <!-- ================================================= -->
            <!-- Picking -->
            <!-- ================================================= -->
            <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600">

                    Picking

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>

                    </svg>

                </button>

                <!--<div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-64">

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Data Unit
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Data Produk
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Data Stokis
                        </a>

                    </div>

                </div>-->

            </div>

            <!-- ================================================= -->
            <!-- QC -->
            <!-- ================================================= -->
            <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600">

                    QC Outgoing

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>

                    </svg>

                </button>

                <!--<div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-64">

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Order
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Unit
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Produk
                        </a>

                    </div>

                </div>-->

            </div>

               <!--================================================= -->
                <!-- Packing -->
                <!--================================================= -->
                <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600">

                    Packing

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>

                    </svg>

                </button>

                <!--<div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-64">

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Order
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Unit
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Produk
                        </a>

                    </div>

                </div>-->

            </div>

                <!--================================================= -->
                <!-- Distribution -->
                <!--================================================= -->
                <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600">

                    Distribution

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>

                    </svg>

                </button>

                <!--<div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-64">

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Order
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Unit
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Produk
                        </a>

                    </div>

                </div>-->

            </div>

                <!--================================================= -->
                <!-- Gudang Logistik -->
                <!--================================================= -->
                <div class="relative group">

                <button class="flex items-center gap-1 text-gray-700 hover:text-blue-600">

                    Gudang Logistik

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 9l-7 7-7-7"/>

                    </svg>

                </button>

                <!--<div class="absolute hidden group-hover:block pt-2 z-50">

                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 w-64">

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Order
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Unit
                        </a>

                        <a href="#"
                           class="block px-5 py-3 hover:bg-gray-50 hover:text-blue-600">
                            Rekap Produk
                        </a>

                    </div>

                </div>-->

            </div>

        </div>

    </div>

    <!-- Right Side -->
    <div class="flex items-center gap-4">

        <span class="text-sm text-gray-700 font-medium">
            Halo, {{ Auth::user()->name ?? 'Admin' }}
        </span>

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault();document.getElementById('logout-form').submit();"
           class="bg-red-600 hover:bg-red-700 px-5 py-2 rounded-xl text-sm font-medium text-white transition-all">
            LOGOUT
        </a>

    </div>

</nav>

<form id="logout-form"
      action="{{ route('logout') }}"
      method="POST"
      class="hidden">
    @csrf
</form>