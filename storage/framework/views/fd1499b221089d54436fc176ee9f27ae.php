

<?php $__env->startSection('title', 'Transfer Gudang'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

<div class="flex justify-between mb-4">
    <h2 class="text-xl font-bold">Transfer Gudang</h2>

    <a href="<?php echo e(route('transfer.create')); ?>" class="bg-blue-500 text-white px-4 py-2 rounded">
        + Transfer
    </a>
</div>

<table class="w-full border">
<thead>
<tr>
<th>ID</th>
<th>Dari</th>
<th>Ke</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>
<?php $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr class="border-t text-center">
<td>#<?php echo e($t->id); ?></td>
<td><?php echo e($t->fromWarehouse->name); ?></td>
<td><?php echo e($t->toWarehouse->name); ?></td>
<td><?php echo e($t->date); ?></td>

<td>
    <?php if($t->status == 'pending'): ?>
        <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded">
            Pending
        </span>
    <?php elseif($t->status == 'approved'): ?>
        <span class="bg-green-200 text-green-800 px-2 py-1 rounded">
            Approved
        </span>
    <?php else: ?>
        <span class="bg-red-200 text-red-800 px-2 py-1 rounded">
            Rejected
        </span>
    <?php endif; ?>
</td>

<td>
    <a href="<?php echo e(route('transfer.show', $t->id)); ?>" class="bg-blue-500 text-white px-2 py-1 rounded">Detail</a>

    <?php if($t->status == 'pending'): ?>
        <form action="<?php echo e(route('transfer.approve', $t->id)); ?>" method="POST" class="inline">
            <?php echo csrf_field(); ?>
            <button class="bg-green-600 text-white px-2 py-1 rounded">
                Approve
            </button>
        </form>
    <?php endif; ?>

    <?php if($t->status == 'pending'): ?>
    <a href="<?php echo e(route('transfer.edit', $t->id)); ?>" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
<?php endif; ?>

    <form action="<?php echo e(route('transfer.destroy', $t->id)); ?>" method="POST" class="inline">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
        <button onclick="return confirm('Hapus?')" class="bg-red-500 text-white px-2 py-1 rounded">
            Hapus
        </button>
    </form>
</td>

</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>

</table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Stok\Resources/views/transfer/index.blade.php ENDPATH**/ ?>