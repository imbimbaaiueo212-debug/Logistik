

<?php $__env->startSection('title', 'Quotation'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Quotation</h2>

        <a href="<?php echo e(route('quotation.create')); ?>"
           class="bg-blue-500 text-white px-4 py-2 rounded">
            + Buat Quotation
        </a>
    </div>

    <table class="w-full border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">No</th>
                <th class="p-2">Supplier</th>
                <th class="p-2">Tanggal</th>
                <th class="p-2">Items</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Total</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t text-center">
                <td>#<?php echo e($q->id); ?></td>
                <td>
                    <?php echo e($q->suppliers->pluck('supplier.name')->join(', ')); ?>

                </td>
                <td><?php echo e(\Carbon\Carbon::parse($q->date)->format('d-m-Y')); ?></td>
                <td>
                    <?php echo e($q->items->pluck('product.name')->filter()->join(', ')); ?>

                </td>
                <td><?php echo e($q->items->sum('qty')); ?></td>
                <td>Rp <?php echo e(number_format($q->total, 0, ',', '.')); ?></td>
                <td>
                    <span class="px-2 py-1 rounded text-white
                        <?php echo e($q->status == 'draft' ? 'bg-gray-500' : ''); ?>

                        <?php echo e($q->status == 'sent' ? 'bg-blue-500' : ''); ?>

                        <?php echo e($q->status == 'approved' ? 'bg-green-500' : ''); ?>

                         <?php echo e($q->status == 'created PO' ? 'bg-red-500' : ''); ?>">
                        <?php echo e($q->status); ?>

                    </span>
                </td>
                <td class="flex gap-2 justify-center flex-wrap">

    
    <a href="<?php echo e(route('quotation.show', $q->id)); ?>"
       class="bg-gray-500 text-white px-2 py-1 rounded text-sm">
        Detail
    </a>

    
    <a href="<?php echo e(route('quotation.edit', $q->id)); ?>"
       class="bg-blue-500 text-white px-2 py-1 rounded text-sm">
        Edit
    </a>

    
    <?php if($q->status == 'draft'): ?>
        <form method="POST" action="<?php echo e(route('quotation.send', $q->id)); ?>">
            <?php echo csrf_field(); ?>
            <button class="bg-green-500 text-white px-2 py-1 rounded text-sm">
                Kirim
            </button>
        </form>
    <?php endif; ?>

    
    <?php if($q->status == 'sent'): ?>
        <form method="POST" action="<?php echo e(route('quotation.approve', $q->id)); ?>">
            <?php echo csrf_field(); ?>
            <button class="bg-emerald-600 text-white px-2 py-1 rounded text-sm">
                Approve
            </button>
        </form>

        <form method="POST" action="<?php echo e(route('quotation.reject', $q->id)); ?>">
            <?php echo csrf_field(); ?>
            <button class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                Reject
            </button>
        </form>
    <?php endif; ?>

    
    <?php if($q->status == 'approved'): ?>
        <form method="POST"
              action="<?php echo e(route('quotation.convert.po', [$q->id, $q->suppliers->first()->supplier_id])); ?>">
            <?php echo csrf_field(); ?>
            <button class="bg-purple-600 text-white px-2 py-1 rounded text-sm">
                Convert PO
            </button>
        </form>
    <?php endif; ?>

</td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="text-center p-4">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Pengadaan\Resources/views/quotation/index.blade.php ENDPATH**/ ?>