

<?php $__env->startSection('title', 'Distribusi'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Distribusi</h2>
        <a href="<?php echo e(route('distribution.create')); ?>"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            + Buat Distribusi
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Gudang</th>
                <th class="p-2">Tujuan</th>
                <th class="p-2">Tanggal</th>
                <th class="p-2">Item</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $distributions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t text-center">
                <td><?php echo e($d->warehouse->name ?? '-'); ?></td>
                <td><?php echo e($d->destination); ?></td>
                <td><?php echo e($d->date); ?></td>
                <td><?php echo e($d->items->pluck('product.name')->filter()->join(', ')); ?></td>
                <td><?php echo e($d->items->sum('qty')); ?></td>
                <td>
                    <span class="px-2 py-1 rounded text-white
                        <?php echo e($d->status == 'pending' ? 'bg-yellow-500' : ''); ?>

                        <?php echo e($d->status == 'approved' ? 'bg-green-500' : ''); ?>

                        <?php echo e($d->status == 'rejected' ? 'bg-red-500' : ''); ?>">
                        <?php echo e($d->status); ?>

                    </span>
                </td>
                <td class="flex gap-2 justify-center">

                    <a href="<?php echo e(route('distribution.edit', $d->id)); ?>"
                       class="bg-blue-500 text-white px-2 py-1 rounded text-sm">
                        Edit
                    </a>

                    <?php if($d->status == 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('distribution.approve', $d->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="bg-green-500 text-white px-2 py-1 rounded text-sm">
                            Approve
                        </button>
                    </form>

                    <form method="POST" action="<?php echo e(route('distribution.reject', $d->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                            Reject
                        </button>
                    </form>
                    <?php endif; ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center p-4">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Distribusi\Resources/views/distribution/index.blade.php ENDPATH**/ ?>