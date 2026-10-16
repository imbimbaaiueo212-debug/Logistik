

<?php $__env->startSection('title', 'Purchase Order'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <?php if(session('success')): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="flex justify-between mb-6">
        <h3 class="text-xl font-semibold">Purchase Order</h3>

        <a href="<?php echo e(route('po.create')); ?>" 
           class="bg-blue-500 text-white px-4 py-2 rounded-lg">
            + Buat PO
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Supplier</th>
                <th class="p-3">Tanggal</th>
                <th class="p-3">Items</th>
                <th class="p-3">Qty</th>
                <th class="p-3">Total</th>
                <th class="p-3">Status</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t text-center">
                <td class="p-3">#<?php echo e($po->id); ?></td>
                <td class="p-3"><?php echo e($po->supplier->name ?? '-'); ?></td>
                <td class="p-3">
                   <?php echo e($po->created_at?->format('d/m/Y')); ?>

                </td>
                <td class="p-3">
                    <?php echo e($po->items->pluck('product.name')->filter()->join(', ')); ?>

                </td>
                <td class="p-3">
                    <?php echo e($po->items->sum('qty')); ?>

                </td>
                <td class="p-3">Rp <?php echo e(number_format($po->total)); ?></td>
                <td>
                    <span class="
                    <?php if($po->status == 'Draft'): ?> bg-gray-400
                    <?php elseif($po->status == 'Partial'): ?> bg-yellow-500
                    <?php elseif($po->status == 'Completed'): ?> bg-green-500
                    <?php else: ?> bg-red-500
                    <?php endif; ?>
                    text-white px-3 py-1 rounded">
                    <?php echo e($po->status); ?>

                </span>
                </td>
                <td class="p-3">
                    <a href="<?php echo e(route('po.edit', $po)); ?>" 
                       class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </a>
                    <a href="<?php echo e(route('po.show', $po->id)); ?>" 
                        class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                        Detail
                    </a>
                    
                    <form action="<?php echo e(route('po.destroy', $po)); ?>" method="POST" 
                          class="inline" onsubmit="return confirm('Yakin hapus PO ini?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                                class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center p-4">
                    Belum ada data PO
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Pengadaan\Resources/views/po/index.blade.php ENDPATH**/ ?>