

<?php $__env->startSection('title', 'Supplier Product'); ?>

<?php $__env->startSection('content'); ?>

<div class="bg-white rounded-2xl shadow p-6">

    <?php if(session('success')): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="flex justify-between mb-6">
        <h3 class="text-xl font-semibold">Supplier - Product Relation</h3>
    </div>

    <!-- Form Tambah Relasi -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-8">
        <h4 class="font-medium mb-4">Tambah Produk ke Supplier</h4>
        
        <form action="<?php echo e(route('supplier-product.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                            required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>">
                                <?php echo e($supplier->kode_supplier); ?> - <?php echo e($supplier->nama_supplier); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                    <select name="product_id" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                            required>
                        <option value="">-- Pilih Produk --</option>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($product->id); ?>">
                                <?php echo e($product->name ?? $product->nama_produk ?? 'Produk #' . $product->id); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                    <input type="number" 
                           name="price" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500"
                           required>
                </div>

                <div class="md:col-span-1 flex items-end">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg w-full">
                        Tambah
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Daftar Relasi Supplier & Produk -->
    <h4 class="text-lg font-medium mb-4">Daftar Relasi Supplier dan Produk</h4>

    <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-8">
            <div class="bg-gray-100 px-5 py-3 rounded-t-xl font-medium">
                <?php echo e($supplier->kode_supplier); ?> — <?php echo e($supplier->nama_supplier); ?>

            </div>

            <?php if($supplier->products->isNotEmpty()): ?>
                <table class="w-full border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left border-b">No</th>
                            <th class="p-3 text-left border-b">Nama Produk</th>
                            <th class="p-3 text-right border-b">Harga</th>
                            <th class="p-3 text-center border-b w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $supplier->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3"><?php echo e($index + 1); ?></td>
                                <td class="p-3">
                                    <?php echo e($product->name ?? $product->nama_produk ?? 'Produk #' . $product->id); ?>

                                </td>
                                <td class="p-3 text-right font-medium">
                                    Rp <?php echo e(number_format($product->pivot->price ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="p-3 flex gap-2 justify-center">
                                    <!-- Edit Harga -->
                                    <a href="<?php echo e(route('supplier-product.edit', [$supplier->id, $product->id])); ?>" 
                                       class="bg-yellow-400 hover:bg-yellow-500 px-4 py-1 rounded text-white text-sm">
                                        Edit Harga
                                    </a>

                                    <!-- Hapus -->
                                    <form action="<?php echo e(route('supplier-product.destroy', [$supplier->id, $product->id])); ?>" 
                                          method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus produk ini dari supplier?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-600 px-4 py-1 rounded text-white text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="border border-dashed border-gray-300 rounded-b-xl p-8 text-center text-gray-500">
                    Belum ada produk yang ditambahkan untuk supplier ini.
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
            Belum ada data supplier.
        </div>
    <?php endif; ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Master\Resources/views/supplier_product/index.blade.php ENDPATH**/ ?>