<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - Bimba Logistik</title>
    
    <link rel="icon" type="image/jpeg" href="<?php echo e(asset('public/assets/img/logologistik.jpg')); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('public/assets/img/logologistik.jpg')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('public/assets/img/logologistik.jpg')); ?>">

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

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
    <div class="flex items-center">
    <img src="/public/assets/img/logotulisan.png" 
         alt="biMBA-AIUEO" 
         class="h-10 w-auto object-contain">
</div>

    <button @click="toggle()">
        <i class="fas fa-bars"></i>
    </button>
</div>

    <!-- MENU -->
    <div class="flex-1 px-2 py-4 overflow-y-auto">

        <!-- DASHBOARD -->
        <a href="<?php echo e(url('/')); ?>" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-tachometer-alt"></i>
            <span x-show="open">Dashboard</span>
            <div x-show="!open && t" class="tooltip">Dashboard</div>
        </a>

        <!-- MASTER DATA -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Master Data</p>
        </div>

        <a href="<?php echo e(route('warehouses.index')); ?>" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-warehouse"></i>
            <span x-show="open">Gudang</span>
            <div x-show="!open && t" class="tooltip">Gudang</div>
        </a>

        <a href="<?php echo e(route('suppliers.index')); ?>" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-truck"></i>
            <span x-show="open">Supplier</span>
            <div x-show="!open && t" class="tooltip">Supplier</div>
        </a>

        <a href="<?php echo e(route('products.index')); ?>" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-box"></i>
            <span x-show="open">Master Produk</span>
            <div x-show="!open && t" class="tooltip">Produk</div>
        </a>
        

        <!-- TRANSAKSI -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Transaksi</p>
        </div>

        <a href="<?php echo e(route('supplier-product.index')); ?>" class="menu-item" x-data="{t:false}" @mouseenter="t=true" @mouseleave="t=false">
            <i class="fas fa-box"></i>
            <span x-show="open">Produk Supplier</span>
            <div x-show="!open && t" class="tooltip">Produk</div>
        </a>

        <a href="<?php echo e(route('quotation.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-file-alt"></i>
            <span x-show="open">Quotation</span>
        </a>

        <a href="<?php echo e(route('po.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-file-invoice"></i>
            <span x-show="open">Purchase Order</span>
        </a>

        <a href="<?php echo e(route('gr.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-box-open"></i>
            <span x-show="open">Stok Masuk</span>
        </a>

        <a href="<?php echo e(route('transfer.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-exchange-alt"></i>
            <span x-show="open">Transfer</span>
        </a>

        <a href="<?php echo e(route('distribution.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-shipping-fast"></i>
            <span x-show="open">Distribusi</span>
        </a>
        <a href="<?php echo e(route('return-distribution.index')); ?>" class="menu-item" x-data="{t:false}">
            <i class="fas fa-shipping-fast"></i>
            <span x-show="open">Distribusi Retur</span>
        </a>

        <!-- INVENTORY -->
        <div class="section">
            <div class="divider"></div>
            <p x-show="open">Inventory</p>
        </div>

        <a href="<?php echo e(route('stock.index')); ?>" class="menu-item">
            <i class="fas fa-boxes"></i>
            <span x-show="open">Stok</span>
        </a>

        <a href="<?php echo e(route('stock-card.index')); ?>" class="menu-item">
            <i class="fas fa-book"></i>
            <span x-show="open">Kartu Stok</span>
        </a>

        <a href="<?php echo e(route('stock-opname.index')); ?>" class="menu-item">
            <i class="fas fa-clipboard-check"></i>
            <span x-show="open">Stock Opname</span>
        </a>

        <a href="<?php echo e(route('reject.index')); ?>" class="menu-item">
    <i class="fas fa-exclamation-triangle"></i>
    <span x-show="open">Reject</span>
</a>

    </div>

    <!-- USER -->
    <div class="p-4 border-t border-blue-700">

    <!-- USER INFO -->
    <a href="<?php echo e(route('users.index')); ?>" 
   class="flex items-center gap-3 hover:bg-white/10 p-2 rounded-xl transition">

    <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
        <i class="fas fa-user"></i>
    </div>

    <div x-show="open">
        <p class="text-sm font-medium">
            <?php echo e(Auth::user()->name ?? 'Guest'); ?>

        </p>

        <p class="text-xs text-blue-200 capitalize">
            <?php echo e(Auth::user()->role ?? 'guest'); ?>

        </p>
    </div>

</a>

    <?php if(auth()->guard()->check()): ?>
    <div class="mt-4 space-y-2">

    <!-- TOMBOL BACK -->
    <a href="<?php echo e(route('home')); ?>"
       class="block w-full text-center bg-gray-500 hover:bg-gray-600 py-2 rounded-xl text-sm font-medium flex items-center justify-center gap-2">
        Home
    </a>

    <!-- RESET PASSWORD (SELF) -->
    <a href="<?php echo e(route('users.reset.form', Auth::id())); ?>"
       class="block w-full text-center bg-yellow-500 hover:bg-yellow-600 py-2 rounded-xl text-sm font-medium">
        Reset Password
    </a>

    <!-- LOGOUT -->
    <form action="<?php echo e(route('logout')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <button class="w-full bg-red-500 hover:bg-red-600 py-2 rounded-xl text-sm font-medium">
            Logout
        </button>
    </form>

</div>
    <?php endif; ?>

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
            <?php echo $__env->yieldContent('title', 'Dashboard'); ?>
        </h2>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 overflow-auto p-6">
        <?php echo $__env->yieldContent('content'); ?>
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
</html><?php /**PATH C:\xampp\htdocs\bimba-logistik\resources\views/layouts/app.blade.php ENDPATH**/ ?>