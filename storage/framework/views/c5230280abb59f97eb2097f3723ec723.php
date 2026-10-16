

<?php $__env->startSection('title', 'Stok Gudang'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Stok per Gudang</h2>
    </div>

    <!-- FILTER GUDANG -->
    <form method="GET" class="mb-4">
        <select name="warehouse_id" class="border p-2 rounded" onchange="this.form.submit()">
            <option value="">Semua Gudang</option>
            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($w->id); ?>" 
                    <?php echo e(request('warehouse_id') == $w->id ? 'selected' : ''); ?>>
                    <?php echo e($w->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>

    <!-- TABLE -->
    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Gudang</th>
                <th class="p-2">Produk</th>
                <th class="p-2">Kode</th>
                <th class="p-2">Jenis</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Label</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t text-center">
                <td class="p-2"><?php echo e($s->warehouse->name ?? '-'); ?></td>
                <td class="p-2"><?php echo e($s->product->name); ?></td>
                <td class="p-2"><?php echo e($s->product->sku ?? '-'); ?></td>
                <td class="p-2"><?php echo e($s->product->jenis ?? '-'); ?></td>
                <td class="p-2 font-bold"><?php echo e($s->qty); ?></td>
                <td class="p-2 text-blue-600 font-semibold">
                    <?php echo e($s->label); ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">
                    Belum ada data stok
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Stok\Resources/views/stock/index.blade.php ENDPATH**/ ?>