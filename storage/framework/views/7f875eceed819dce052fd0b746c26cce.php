<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-3xl shadow p-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Selamat Datang di Bimba Logistik</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 border border-blue-100 p-8 rounded-2xl">
                <p class="text-blue-600 text-sm font-medium">Total Gudang</p>
                <p class="text-5xl font-bold text-blue-700 mt-4">2</p>
                <p class="text-gray-500 mt-1">Logistik & Sawo Joglo</p>
            </div>

            <div class="bg-emerald-50 border border-emerald-100 p-8 rounded-2xl">
                <p class="text-emerald-600 text-sm font-medium">Unit BIMBA</p>
                <p class="text-5xl font-bold text-emerald-700 mt-4">1</p>
                <p class="text-gray-500 mt-1">Peminta Barang</p>
            </div>

            <div class="bg-amber-50 border border-amber-100 p-8 rounded-2xl">
                <p class="text-amber-600 text-sm font-medium">Stok Aktif</p>
                <p class="text-5xl font-bold text-amber-700 mt-4">0</p>
                <p class="text-gray-500 mt-1">Barang siap distribusi</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\resources\views/dashboard.blade.php ENDPATH**/ ?>