

<?php $__env->startSection('title', 'Distribution Order - Jakarta Aktif'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    th, td {
        padding: 12px 8px;
        font-size: 0.875rem;
    }
    .status-badge {
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-screen-2xl mx-auto px-6 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <i class="bi bi-truck text-blue-600"></i>
            Distribution Order Jakarta Aktif
        </h1>
        <a href="<?php echo e(route('distribution-order.create')); ?>"
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold flex items-center gap-2 transition">
            <i class="bi bi-plus-circle"></i> Tambah Order Baru
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status_pengiriman" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="belum_pickup" <?php echo e(request('status_pengiriman') == 'belum_pickup' ? 'selected' : ''); ?>>Belum Pickup</option>
                    <option value="pickup" <?php echo e(request('status_pengiriman') == 'pickup' ? 'selected' : ''); ?>>Pickup</option>
                    <option value="transit" <?php echo e(request('status_pengiriman') == 'transit' ? 'selected' : ''); ?>>Transit</option>
                    <option value="hold" <?php echo e(request('status_pengiriman') == 'hold' ? 'selected' : ''); ?>>Hold</option>
                    <option value="retur" <?php echo e(request('status_pengiriman') == 'retur' ? 'selected' : ''); ?>>Retur</option>
                    <option value="delivered" <?php echo e(request('status_pengiriman') == 'delivered' ? 'selected' : ''); ?>>Delivered</option>
                    <option value="missing" <?php echo e(request('status_pengiriman') == 'missing' ? 'selected' : ''); ?>>Missing</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kirim</label>
                <select name="jenis_pengiriman" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="diambil_sendiri" <?php echo e(request('jenis_pengiriman') == 'diambil_sendiri' ? 'selected' : ''); ?>>Diambil Sendiri</option>
                    <option value="ekspedisi" <?php echo e(request('jenis_pengiriman') == 'ekspedisi' ? 'selected' : ''); ?>>Ekspedisi</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ekspedisi</label>
                <select name="ekspedisi" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = $ekspedisiList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($eks); ?>" <?php echo e(request('ekspedisi') == $eks ? 'selected' : ''); ?>><?php echo e($eks); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                <select name="service" class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <?php $__currentLoopData = $serviceList ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($svc); ?>" <?php echo e(request('service') == $svc ? 'selected' : ''); ?>><?php echo e($svc); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-3 text-sm"
                       placeholder="No PL atau Nama Barang"
                       value="<?php echo e(request('search')); ?>">
            </div>
            <div class="md:col-span-12 flex justify-end gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-2xl transition">
                    Filter
                </button>
                <a href="<?php echo e(route('distribution-order.jakarta-aktif')); ?>"
                   class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-2xl text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3">No</th>
                    <th class="text-left px-4 py-3">No PL</th>
                    <th class="text-left px-4 py-3">Tgl PL</th>
                    <th class="text-left px-4 py-3">Unit</th>
                    <th class="text-left px-4 py-3">Barang</th>
                    <th class="text-left px-4 py-3">Jenis</th>
                    <th class="text-left px-4 py-3">Ekspedisi</th>
                    <th class="text-left px-4 py-3">Service</th>
                    <th class="text-left px-4 py-3">Berat</th>
                    <th class="text-left px-4 py-3">Berat Aktual</th>
                    <th class="text-left px-4 py-3">Koli</th>
                    <th class="text-left px-4 py-3">Tanggal Pickup</th>
                    <th class="text-left px-4 py-3">AWB</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Tgl Diterima</th>
                    <th class="text-left px-4 py-3">Penerima</th>
                    <th class="text-left px-4 py-3">Keterangan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $distributionOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $rowFormId = 'row-form-' . $item->id;
        // Baris dianggap final/terkunci kalau statusnya sudah Delivered
        $isLocked = ($item->status_pengiriman === 'delivered');
    ?>
    <tr class="transition duration-200 hover:bg-gray-50 <?php echo e($isLocked ? 'bg-gray-50' : ''); ?>">
        <td class="px-4 py-4 text-center font-semibold"><?php echo e($loop->iteration); ?></td>
        <td class="px-4 py-4 font-semibold"><?php echo e($item->no_pl); ?></td>
        <td class="px-4 py-4"><?php echo e($item->tgl_turun_pl?->format('d/m/Y') ?? '-'); ?></td>
        <td class="px-4 py-4"><?php echo e($item->nama_unit ?? '-'); ?></td>
        <td class="px-4 py-4"><?php echo e(Str::limit($item->nama_barang ?? '', 50)); ?></td>
        <td class="px-4 py-4">
            <?php $status = optional($item->jakartaAktif)->status_kirim; ?>
            <?php if($status == 'Diambil'): ?>
                <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">Diambil</span>
            <?php elseif($status == 'Dikirim'): ?>
                <span class="inline-block bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-medium">Dikirim</span>
            <?php else: ?>
                <span class="text-gray-400">-</span>
            <?php endif; ?>
        </td>
        <td class="px-4 py-4">
            <?php echo e($item->ekspedisi ?? '-'); ?>

        </td>
        <td class="px-4 py-4">
            <?php echo e($item->service ?? '-'); ?>

        </td>

        
        <td class="px-4 py-4 text-center">
            <?php echo e($item->packing && $item->packing->berat !== null
                ? rtrim(rtrim($item->packing->berat, '0'), '.')
                : '-'); ?> g
        </td>
        <td class="px-4 py-4 text-center">
            <?php echo e($item->packing && $item->packing->berat_aktual !== null
                ? rtrim(rtrim($item->packing->berat_aktual, '0'), '.')
                : '-'); ?> Kg
        </td>
        
        <td class="px-3 py-4 text-center font-medium whitespace-nowrap">
            <span class="inline-block min-w-[60px]"><?php echo e($item->packing->koli ?? '-'); ?></span>
        </td>

        <td class="px-3 py-4">
            <input type="date" name="tgl_pickup" form="<?php echo e($rowFormId); ?>"
                value="<?php echo e($item->tgl_pickup ? \Carbon\Carbon::parse($item->tgl_pickup)->format('Y-m-d') : ''); ?>"
                class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
        </td>

        <td class="px-3 py-4">
            <input type="text" name="awb" form="<?php echo e($rowFormId); ?>" value="<?php echo e($item->awb); ?>"
                placeholder="Masukkan No. Resi"
                class="w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
        </td>
        <td class="px-3 py-4">
            <select name="status_pengiriman" form="<?php echo e($rowFormId); ?>"
                class="w-44 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
                <option value="belum_pickup" <?php echo e($item->status_pengiriman == 'belum_pickup' ? 'selected' : ''); ?>>Belum Pickup</option>
                <option value="pickup" <?php echo e($item->status_pengiriman == 'pickup' ? 'selected' : ''); ?>>Pickup</option>
                <option value="transit" <?php echo e($item->status_pengiriman == 'transit' ? 'selected' : ''); ?>>Transit</option>
                <option value="hold" <?php echo e($item->status_pengiriman == 'hold' ? 'selected' : ''); ?>>Hold</option>
                <option value="retur" <?php echo e($item->status_pengiriman == 'retur' ? 'selected' : ''); ?>>Retur</option>
                <option value="delivered" <?php echo e($item->status_pengiriman == 'delivered' ? 'selected' : ''); ?>>Delivered</option>
            </select>
        </td>
        <td class="px-3 py-4">
            <input type="date" name="tgl_diterima" form="<?php echo e($rowFormId); ?>"
                value="<?php echo e($item->tgl_diterima ? \Carbon\Carbon::parse($item->tgl_diterima)->format('Y-m-d') : ''); ?>"
                class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
        </td>
        <td class="px-3 py-4">
            <input type="text" name="penerima" form="<?php echo e($rowFormId); ?>" value="<?php echo e($item->penerima ?? ''); ?>"
                placeholder="Masukkan nama penerima..."
                class="w-64 border rounded-lg px-3 py-2 text-sm"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
        </td>
        <td class="px-3 py-4">
            <input type="text" name="keterangan" form="<?php echo e($rowFormId); ?>" value="<?php echo e($item->keterangan ?? ''); ?>"
                placeholder="Tambahkan catatan..."
                class="w-64 border rounded-lg px-3 py-2 text-sm"
                <?php echo e($isLocked ? 'disabled' : ''); ?>>
        </td>
        <td class="px-4 py-4 text-center">
            <div class="flex justify-center gap-3">
                <?php if($isLocked): ?>
                    <button type="button" class="text-gray-400 cursor-not-allowed" title="Data sudah dikunci (Delivered)">
                        <i class="bi bi-lock-fill text-xl"></i>
                    </button>
                <?php else: ?>
                    <button type="submit" form="<?php echo e($rowFormId); ?>"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Simpan
                    </button>
                <?php endif; ?>
                <a href="<?php echo e(route('distribution-order.show', $item)); ?>" class="text-blue-600 hover:text-blue-700">
                    <i class="bi bi-eye text-xl"></i>
                </a>
                <a href="<?php echo e(route('distribution-order.edit', $item)); ?>" class="text-amber-600 hover:text-amber-700">
                    <i class="bi bi-pencil text-xl"></i>
                </a>
            </div>
        </td>
    </tr>
    
    <?php if (! ($isLocked)): ?>
    <tr class="hidden">
        <td colspan="18" class="p-0">
            <form id="<?php echo e($rowFormId); ?>" method="POST"
                  action="<?php echo e(route('distribution-order.update', $item->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
            </form>
        </td>
    </tr>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr>
        <td colspan="18" class="text-center py-12 text-gray-400">
            Belum ada data Distribution Order Jakarta Aktif.
        </td>
    </tr>
<?php endif; ?>
            </tbody>
        </table>

        <?php if($distributionOrders instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
            <div class="px-6 py-4 border-t flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Menampilkan <?php echo e($distributionOrders->firstItem()); ?> - <?php echo e($distributionOrders->lastItem()); ?>

                    dari <?php echo e($distributionOrders->total()); ?> data
                </div>
                <div><?php echo e($distributionOrders->links()); ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Distribusi\Resources/views/distribution-order/jakarta-aktif.blade.php ENDPATH**/ ?>