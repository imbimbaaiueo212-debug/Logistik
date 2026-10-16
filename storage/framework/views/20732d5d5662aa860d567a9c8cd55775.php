

<?php $__env->startSection('title', 'Gudang'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Data Gudang</h2>

        <a href="<?php echo e(route('warehouses.create')); ?>" 
           class="bg-green-500 text-white px-4 py-2 rounded">
            + Tambah Gudang
        </a>
    </div>

    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">ID</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Kode</th>
                <th class="p-2">Lokasi</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t">
                <td class="p-2 text-center">#<?php echo e($w->id); ?></td>
                <td class="p-2"><?php echo e($w->name); ?></td>
                <td class="p-2 text-center"><?php echo e($w->code); ?></td>
                <td class="p-2"><?php echo e($w->location); ?></td>
                <td class="p-2 text-center">
                    <div class="flex justify-center gap-2">

                        <a href="<?php echo e(route('warehouses.edit', $w->id)); ?>"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form action="<?php echo e(route('warehouses.destroy', $w->id)); ?>" method="POST"
                              onsubmit="return confirm('Yakin hapus?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>

                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                Hapus
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center p-4 text-gray-500">
                    Belum ada data gudang
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Master\Resources/views/warehouse/index.blade.php ENDPATH**/ ?>