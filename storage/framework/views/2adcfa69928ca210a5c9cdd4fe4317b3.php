

<?php $__env->startSection('title', 'Stok Masuk'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Stok Masuk</h2>

        <a href="<?php echo e(route('gr.create')); ?>" 
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
            + Tambah Stok Masuk
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center">ID</th>
                    <th class="p-3 text-center">PO</th>
                    <th class="p-3 text-center">Gudang</th>
                    <th class="p-3 text-center">Tanggal</th>
                    <th class="p-3 text-center">Produk</th>
                    <th class="p-3 text-center">Total</th>
                    <th class="p-3 text-center">OK</th>
                    <th class="p-3 text-center">Reject</th>
                    <th class="p-3 text-center">QC</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-b">

                    <td class="p-3 text-center">#<?php echo e($r->id); ?></td>
                    <td class="p-3 text-center">PO #<?php echo e($r->purchase_order_id); ?></td>
                    <td class="p-3 text-center"><?php echo e($r->warehouse->name ?? '-'); ?></td>
                    <td class="p-3 text-center"><?php echo e($r->date->format('d-m-Y')); ?></td>

                    
                    <td class="p-3 text-sm">
                        <?php $__currentLoopData = $r->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>• <?php echo e($item->product->name); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>

                    
                    <td class="text-center">
                        <?php echo e($r->items->sum('qty_received')); ?>

                    </td>

                    
                    <td class="text-center text-green-600 font-bold">
                        <?php echo e($r->items->sum('qty_ok')); ?>

                    </td>

                    
                    <td class="text-center text-red-600 font-bold">
                        <?php echo e($r->items->sum('qty_reject')); ?>

                    </td>

                    
                    <td class="text-center">
                        <?php if($r->items->every(fn($i) => $i->isQcDone())): ?>
                            <span class="bg-green-200 text-green-800 px-2 py-1 rounded text-xs">
                                QC Done
                            </span>
                        <?php else: ?>
                            <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs">
                                Belum QC
                            </span>
                        <?php endif; ?>
                    </td>

                    
                    <td class="text-center space-x-1">

                        <a href="<?php echo e(route('gr.edit', $r->id)); ?>" 
                           class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                            Edit
                        </a>

                        
                        <?php if($r->items->contains(fn($i) => !$i->isQcDone())): ?>
                        <a href="<?php echo e(route('gr.qc.page', $r->id)); ?>"
                           class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                            QC
                        </a>
                        <?php endif; ?>

                        <form action="<?php echo e(route('gr.destroy', $r->id)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">
                                Hapus
                            </button>
                        </form>

                    </td>

                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Pengadaan\Resources/views/gr/index.blade.php ENDPATH**/ ?>