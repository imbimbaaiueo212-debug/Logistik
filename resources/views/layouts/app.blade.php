<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Bimba Logistik</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Alpine JS -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        body { background-color: #f8fafc; }
        .sidebar { background: linear-gradient(180deg, #1e40af 0%, #3b82f6 100%); }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            transition: 0.2s;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        .section {
            margin-top: 16px;
            padding: 0 16px;
        }

        .section p {
            font-size: 11px;
            color: #bfdbfe;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin-bottom: 8px;
        }

        .tooltip {
            position: absolute;
            left: 70px;
            background: black;
            color: white;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }
    </style>
</head>

<body x-data="sidebar()" x-init="init()" class="flex h-screen overflow-hidden">

<!-- SIDEBAR -->
<div :class="open ? 'w-64' : 'w-20'" class="sidebar text-white flex flex-col transition-all duration-300">

    <!-- LOGO -->
    <div class="p-6 border-b border-blue-700 flex items-center justify-between">
        <div x-show="open">
            <h1 class="text-xl font-bold">BIMBA</h1>
            <p class="text-blue-200 text-xs">Logistik</p>
        </div>

        <button @click="toggle()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- MENU -->
    <div class="flex-1 px-2 py-4 overflow-y-auto">

        <!-- DASHBOARD -->
        <a href="{{ url('/') }}" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-tachometer-alt"></i>
            <span x-show="open">Dashboard</span>
            <div x-show="!open && t" class="tooltip">Dashboard</div>
        </a>

        <!-- MASTER DATA -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Master Data</p>
        </div>

        <a href="{{ route('warehouses.index') }}" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-warehouse"></i>
            <span x-show="open">Gudang</span>
            <div x-show="!open && t" class="tooltip">Gudang</div>
        </a>

        <a href="{{ route('suppliers.index') }}" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-truck"></i>
            <span x-show="open">Supplier</span>
            <div x-show="!open && t" class="tooltip">Supplier</div>
        </a>

        <a href="{{ route('products.index') }}" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-box"></i>
            <span x-show="open">Produk</span>
            <div x-show="!open && t" class="tooltip">Produk</div>
        </a>

        <!-- TRANSAKSI -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Transaksi</p>
        </div>

        <a href="{{ route('po.index') }}" class="menu-item" x-data="{t:false}">
            <i class="fas fa-file-invoice"></i>
            <span x-show="open">Purchase Order</span>
        </a>

        <a href="{{ route('gr.index') }}" class="menu-item" x-data="{t:false}">
            <i class="fas fa-box-open"></i>
            <span x-show="open">Stok Masuk</span>
        </a>

        <a href="{{ route('transfer.index') }}" class="menu-item" x-data="{t:false}">
            <i class="fas fa-exchange-alt"></i>
            <span x-show="open">Transfer</span>
        </a>

        <!-- INVENTORY -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Inventory</p>
        </div>

        <a href="{{ route('stock.index') }}" class="menu-item">
            <i class="fas fa-boxes"></i>
            <span x-show="open">Stok</span>
        </a>

        <a href="{{ route('stock-card.index') }}" class="menu-item">
            <i class="fas fa-book"></i>
            <span x-show="open">Kartu Stok</span>
        </a>

        <a href="{{ route('stock-opname.index') }}" class="menu-item">
            <i class="fas fa-clipboard-check"></i>
            <span x-show="open">Stock Opname</span>
        </a>

        <a href="{{ route('reject.index') }}" class="menu-item">
    <i class="fas fa-exclamation-triangle"></i>
    <span x-show="open">Reject</span>
</a>

    </div>

    <!-- USER -->
    <div class="p-4 border-t border-blue-700">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-user"></i>
            </div>

            <div x-show="open">
                <p class="text-sm">{{ Auth::user()->name ?? 'Guest' }}</p>
                <p class="text-xs text-blue-200">Administrator</p>
            </div>
        </div>

        @auth
        <form action="{{ route('logout') }}" method="POST" class="mt-4">
            @csrf
            <button class="w-full bg-red-500 hover:bg-red-600 py-2 rounded-xl text-sm">
                Logout
            </button>
        </form>
        @endauth
    </div>

</div>

<!-- MAIN -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- HEADER -->
    <header class="bg-white border-b px-6 py-4 flex items-center">
        <button @click="toggle()" class="mr-4 text-gray-600">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <h2 class="text-2xl font-semibold">
            @yield('title', 'Dashboard')
        </h2>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 overflow-auto p-6">
        @yield('content')
    </main>

</div>

<!-- SCRIPT -->
<script>
function sidebar() {
    return {
        open: true,

        init() {
            const saved = localStorage.getItem('sidebar');
            this.open = saved !== null ? JSON.parse(saved) : true;
        },

        toggle() {
            this.open = !this.open;
            localStorage.setItem('sidebar', this.open);
        }
    }
}
</script>

</body>
</html>