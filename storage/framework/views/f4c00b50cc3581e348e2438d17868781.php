<?php $__env->startSection('title', 'Edit Stokis Pasif'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Edit Stokis Pasif</h1>
        <p class="mt-1 text-sm text-navy-950/55"><?php echo e($stokis->no_cab); ?></p>
    </div>

    <?php echo $__env->make('master::stokis_pasif._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Master\Resources/views/stokis_pasif/edit.blade.php ENDPATH**/ ?>