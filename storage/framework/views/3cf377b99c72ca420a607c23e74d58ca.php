<?php
    // [nama field, label, tipe]. Tipe: text, email, date, textarea, select
    $fields = [
        ['no_cab', 'No Cab', 'text'],
        ['no_induk_mitra', 'No Induk Mitra', 'text'],
        ['nama_stokis_db_kemitraan', 'Nama Stokis Kemitraan', 'text'],
        ['nama_stokis_db_bimbashop', 'Nama Stokis biMBA Shop', 'text'],
        ['nama_mitra', 'Nama Mitra', 'text'],
        ['email', 'Email', 'email'],
        ['no_hp', 'No HP', 'text'],
        ['ops_stokist', 'Ops Stokist', 'text'],
        ['db_kemitraan_db_bimbashop', 'DB Kemitraan & Shop', 'text'],
        ['status', 'Status', 'select'],
        ['tanggal_pasif', 'Tanggal Pasif', 'date'],
        ['related_form_pembukaan_unit_aktif', 'Form Pembukaan Unit', 'textarea'],
        ['related_formulir_kerjasama_english', 'Kerjasama English', 'textarea'],
        ['related_unit_bimba_aiueo', 'Unit biMBA', 'textarea'],
        ['related_formulir_kerjasama_mk_mm', 'Kerjasama MK/MM', 'textarea'],
        ['related_pengajuan_perubahan', 'Pengajuan Perubahan', 'textarea'],
        ['item_sku', 'Item SKU', 'textarea'],
    ];

    $sudahAda = $stokis->exists;
    $inputCls = 'w-full bg-white border border-navy-950/10 rounded-xl px-4 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
?>

<form action="<?php echo e($sudahAda ? route('stokis-pasif.update', $stokis->id) : route('stokis-pasif.store')); ?>" method="POST"
      class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-7">
    <?php echo csrf_field(); ?>
    <?php if($sudahAda): ?>
        <?php echo method_field('PUT'); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$nama, $label, $tipe]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $val = old($nama, $stokis->{$nama} ?? null);
                if (is_array($val)) { $val = implode(', ', $val); }
                if ($tipe === 'date') {
                    $val = filled($val) ? \Illuminate\Support\Carbon::parse($val)->format('Y-m-d') : '';
                }
            ?>

            <div class="<?php echo e($tipe === 'textarea' ? 'md:col-span-2' : ''); ?>">
                <label for="<?php echo e($nama); ?>" class="block text-sm text-navy-950/60 mb-1.5">
                    <?php echo e($label); ?><?php if($nama === 'no_cab'): ?> <span class="text-rust-600">*</span><?php endif; ?>
                </label>

                <?php if($tipe === 'textarea'): ?>
                    <textarea id="<?php echo e($nama); ?>" name="<?php echo e($nama); ?>" rows="2" class="<?php echo e($inputCls); ?>"><?php echo e($val); ?></textarea>

                <?php elseif($tipe === 'select' && $nama === 'status'): ?>
                    <select id="<?php echo e($nama); ?>" name="<?php echo e($nama); ?>" class="<?php echo e($inputCls); ?>">
                        <option value="aktif" <?php echo e($val === 'aktif' ? 'selected' : ''); ?>>Aktif</option>
                        <option value="pasif" <?php echo e(($val === 'pasif' || blank($val)) ? 'selected' : ''); ?>>Pasif</option>
                    </select>

                <?php else: ?>
                    <input id="<?php echo e($nama); ?>" type="<?php echo e($tipe); ?>" name="<?php echo e($nama); ?>" value="<?php echo e($val); ?>"
                           <?php if($nama === 'no_cab'): ?> required <?php endif; ?>
                           class="<?php echo e($inputCls); ?>">
                <?php endif; ?>

                <?php $__errorArgs = [$nama];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-6 flex items-center gap-2">
        <button type="submit"
                class="bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
            <?php echo e($sudahAda ? 'Simpan perubahan' : 'Simpan'); ?>

        </button>
        <a href="<?php echo e(route('stokis-pasif.index')); ?>"
           class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">
            Batal
        </a>
    </div>
</form><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Master\Resources/views/stokis_pasif/_form.blade.php ENDPATH**/ ?>