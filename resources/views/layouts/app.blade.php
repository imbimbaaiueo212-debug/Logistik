<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Bimba Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { background-color: #f8fafc; }
        .sidebar { background: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%); }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div class="w-64 sidebar text-white flex flex-col">
        
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold tracking-tight">BIMBA LOGISTIK</h1>
            <p class="text-blue-200 text-sm mt-1">Multi Warehouse System</p>
        </div>

        <div class="flex-1 px-3 py-6 overflow-y-auto">
            <nav class="space-y-1">

                <!-- Dashboard -->
                <a href="{{ url('/') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Supplier -->

                <a href="{{ route('suppliers.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition {{ request()->routeIs('suppliers.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-truck w-5"></i>
                    <span>Supplier</span>
                </a>


                <!-- Produk Supplier-->
                 <a href="{{ route('supplier-product.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition">
                    <i class="fas fa-box w-5"></i>
                    <span>Supplier Product</span>
                </a>

                <!-- Purchase Order -->
                <a href="{{ route('po.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition {{ request()->routeIs('po.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-file-invoice w-5"></i>
                    <span>Purchase Order</span>
                </a>

                <a href="{{ route('gr.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition {{ request()->routeIs('gr.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-box-open w-5"></i>
                    <span>Stok Masuk</span>
                </a>

                <a href="{{ route('warehouses.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition {{ request()->routeIs('po.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-file-invoice w-5"></i>
                    <span>Gudang</span>
                </a>

                <a href="{{ route('stock.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition {{ request()->routeIs('stock.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-boxes w-5"></i>
                    <span>Stok Gudang</span>
                </a>

                <a href="{{ route('transfer.index') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition">
                    <i class="fas fa-exchange-alt w-5"></i>
                    <span>Transfer Gudang</span>
                </a>

                <!-- Product -->
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-blue-700 transition">
                    <i class="fas fa-box w-5"></i>
                    <span>Master Produk</span>
                </a>


            </nav>
        </div>

        <!-- User -->
        <div class="p-4 border-t border-blue-700">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-medium text-sm">
                        {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                    </p>
                    <p class="text-blue-200 text-xs">Administrator</p>
                </div>
            </div>

            @auth
            <form action="{{ route('logout') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 py-3 rounded-2xl text-sm font-medium transition">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
            @endauth
        </div>
    </div>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Bar -->
        <header class="bg-white border-b px-8 py-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-800">
                @yield('title', 'Dashboard')
            </h2>
            <div class="text-sm text-gray-500">
                {{ now()->format('d F Y') }}
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-auto p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>