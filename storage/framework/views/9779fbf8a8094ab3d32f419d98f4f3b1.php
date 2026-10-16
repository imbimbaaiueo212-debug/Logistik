<?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl mb-4"><?php echo session('success'); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4"><?php echo e(session('error')); ?></div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\bimba-logistik\resources\views/partials/flash.blade.php ENDPATH**/ ?>