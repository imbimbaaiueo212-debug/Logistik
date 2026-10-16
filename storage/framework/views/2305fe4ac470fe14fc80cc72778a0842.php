

<?php $__env->startSection('title', 'Data Supplier'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white rounded-2xl shadow p-6">

    <?php if(session('success')): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Data Supplier</h3>
        <a href="<?php echo e(route('suppliers.create')); ?>" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah Supplier
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center">Nama</th>
                    <th calss="p-3 text-center">SKU</th>
                    <th class="p-3 text-center">Email</th>
                    <th class="p-3 text-center">Phone</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t">
                    <td class="p-3 text-center"><?php echo e($s->nama_supplier); ?></td>
                    <td class="p-3 text-center"><?php echo e($s->kode_supplier); ?></td>
                    <td class="p-3 text-center"><?php echo e($s->email); ?></td>
                    <td class="p-3 text-center"><?php echo e($s->phone); ?></td>
                    <td class="p-3 text-center flex justify-center gap-2">

                        <a href="<?php echo e(route('suppliers.edit', $s->id)); ?>" 
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-white">
                            Edit
                        </a>

                        <form action="<?php echo e(route('suppliers.destroy', $s->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button onclick="return confirm('Yakin hapus?')" 
                                    class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-white">
                                Hapus
                            </button>
                        </form>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        Belum ada data supplier
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Master\Resources/views/suppliers/index.blade.php ENDPATH**/ ?>