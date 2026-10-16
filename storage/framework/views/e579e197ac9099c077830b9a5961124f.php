

<?php $__env->startSection('title', 'Stok Opname'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">

    
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Stock Opname</h1>

        <a href="<?php echo e(route('stock-opname.create')); ?>"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            + Buat Opname
        </a>
    </div>

    
    <form method="GET" class="flex gap-3">
        <input type="text" name="search"
            placeholder="Cari kode..."
            value="<?php echo e(request('search')); ?>"
            class="border px-3 py-2 rounded w-64">

        <select name="status" class="border px-3 py-2 rounded">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
        </select>

        <button class="bg-gray-800 text-white px-4 py-2 rounded">
            Filter
        </button>
    </form>

    
    <div class="grid gap-4">

        <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $so): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('stock-opname.show', $so->id)); ?>"
            class="block bg-white p-4 rounded-xl shadow hover:shadow-md transition">

            <div class="flex justify-between items-center">

                
                <div>
                    <h2 class="font-semibold text-lg"><?php echo e($so->code); ?></h2>
                    <p class="text-sm text-gray-500">
                        Gudang: <?php echo e($so->warehouse->name); ?>

                    </p>
                </div>

                
                <div class="text-right">

                    
                    <?php if($so->status == 'draft'): ?>
                        <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-xs">
                            Draft
                        </span>
                    <?php elseif($so->status == 'submitted'): ?>
                        <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded text-xs">
                            Submitted
                        </span>
                    <?php elseif($so->status == 'approved'): ?>
                        <span class="bg-green-200 text-green-800 px-3 py-1 rounded text-xs">
                            Approved
                        </span>
                    <?php endif; ?>

                    <p class="text-xs text-gray-400 mt-1">
                        <?php echo e($so->created_at->format('d M Y H:i')); ?>

                    </p>
                </div>

            </div>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center text-gray-500">
            Tidak ada data
        </div>
        <?php endif; ?>

    </div>

    
    <div>
        <?php echo e($data->links()); ?>

    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Stok\Resources/views/stock_opname/index.blade.php ENDPATH**/ ?>