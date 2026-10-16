<?php $__env->startSection('title', 'Rekap Aktual - Logistik Aktif'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .ra-table { border-collapse: collapse; width: 100%; font-size: 14px; }
    .ra-table th, .ra-table td {
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        vertical-align: top;
        line-height: 1.35;
    }
    .ra-table thead th {
        background: #f1f5f9;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
    }
    .ra-table td.wrap { word-break: break-word; min-width: 200px; }
    .ra-table tbody tr:hover { background: #f8fafc; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-screen-2xl mx-auto px-6 py-6">

    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Rekap Aktual Logistik</h1>
            <p class="text-gray-600">Data realisasi Logistik Aktif yang sudah diproses</p>
        </div>
        <a href="<?php echo e(route('order.logistik-aktif')); ?>"
           class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-2xl font-semibold">
            Kembali
        </a>
    </div>

    
    <form method="GET" action="<?php echo e(route('order.logistik-printed')); ?>"
          class="bg-white rounded-2xl shadow p-4 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <input type="text" name="id_pesan" value="<?php echo e(request('id_pesan')); ?>" placeholder="ID Order"
               class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <input type="text" name="nama_unit" value="<?php echo e(request('nama_unit')); ?>" placeholder="Nama Unit"
               class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>"
               class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>"
               class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 py-2 text-sm font-medium">Filter</button>
            <a href="<?php echo e(route('order.logistik-printed')); ?>"
               class="flex-1 text-center border border-gray-300 hover:bg-gray-50 rounded-xl px-4 py-2 text-sm font-medium">Reset</a>
        </div>
    </form>

    
    <?php $__empty_1 = true; $__currentLoopData = $groupedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tanggal => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $first      = $rows->first();
            $collapseId = 'collapse_' . $loop->index;
            $allPrinted     = $rows->every(fn($item) => !is_null($item->printed_at));
            $allPickingDone = $rows->every(fn($item) => !is_null($item->picking_printed_at));
        ?>

        <div class="bg-white shadow-lg border-2 border-gray-800 mb-6 rounded-xl overflow-hidden"
             data-tanggal="<?php echo e($tanggal); ?>">

            <button type="button" onclick="toggleContent('<?php echo e($collapseId); ?>')"
                    class="w-full flex flex-wrap justify-between items-center gap-3 px-6 py-5 bg-gray-100 hover:bg-gray-200 transition text-left">

                <div class="flex items-center gap-3">
                    <svg id="icon-<?php echo e($collapseId); ?>" class="w-5 h-5 text-gray-600 transition-transform -rotate-90"
                         viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold <?php echo e($allPrinted ? 'text-green-600' : 'text-red-500'); ?>">
                        <?php echo e($allPrinted ? 'RA SUDAH DICETAK' : 'RA BELUM DICETAK'); ?>

                    </span>
                </div>

                <div class="flex-1 text-center">
                    <span class="font-bold text-lg">Rekap Aktual Detail - <?php echo e($first->nama_stokis ?? 'Stokis Logistik'); ?></span>
                    <span class="text-indigo-600 font-semibold ml-2"><?php echo e($first->rekap_number ?? '#0001'); ?></span>
                    <div class="text-sm text-gray-500 mt-1">
                        Total Order: <span class="font-bold text-blue-600"><?php echo e($rows->count()); ?></span>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-sm text-gray-500">Tanggal Order</div>
                    <div class="font-semibold text-gray-800"><?php echo e(\Carbon\Carbon::parse($tanggal)->format('d/m/Y')); ?></div>
                </div>
            </button>

            <div id="<?php echo e($collapseId); ?>" class="hidden">
                <div class="overflow-x-auto">
                    <table class="ra-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>ID ORDER</th>
                                <th>NAMA UNIT</th>
                                <th>BARANG</th>
                                <th>DISTRIBUSI</th>
                                <th>CATATAN</th>
                                <th>PICKING LIST</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $catatan = trim($item->ket ?? '');
                                    $catatan = preg_replace('/Di proses bulk pada .*?: /i', '', $catatan);
                                ?>
                                <tr data-id="<?php echo e($item->id); ?>">
                                    <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                    <td class="text-center font-medium whitespace-nowrap"><?php echo e($item->no_pl ?? '-'); ?></td>
                                    <td class="wrap"><?php echo e($item->nama_unit ?? '-'); ?></td>
                                    <td class="wrap text-sm"><?php echo e($item->nama_barang ?? '-'); ?></td>
                                    <td class="text-center">
                                        <div><?php echo e($item->pengiriman ?? '-'); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($item->service_pengiriman ?? '-'); ?></div>
                                    </td>
                                    <td class="wrap text-xs">
                                        <?php if($catatan !== ''): ?>
                                            <span class="inline-block bg-gray-100 px-3 py-1 rounded-md"><?php echo e(strtoupper($catatan)); ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-3">

                                            
                                            <button type="button"
                                                    onclick="bukaPicking(<?php echo e($item->id); ?>, '<?php echo e($item->no_pl); ?>')"
                                                    title="Cetak Picking List"
                                                    class="<?php echo e($item->picking_printed_at ? 'text-purple-600' : 'text-blue-600 hover:text-blue-700'); ?>">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <?php if($item->picking_printed_at): ?>
                                                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>
                                                    <?php else: ?>
                                                        <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>
                                                    <?php endif; ?>
                                                </svg>
                                            </button>

                                            
                                            <a href="<?php echo e(route('order.realisasi-logistik.tanda-terima', $item->id)); ?>"
                                               target="_blank" title="Cetak Tanda Terima Produk"
                                               class="text-emerald-600 hover:text-emerald-700">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>
                                                </svg>
                                            </a>

                                            
                                            <form action="<?php echo e(route('order.realisasi-logistik.delete', $item->id)); ?>" method="POST"
                                                  onsubmit="return confirm('Hapus <?php echo e($item->no_pl); ?> dari Rekap Aktual?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" title="Hapus" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 11v5M14 11v5"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 border-t p-4 flex flex-wrap gap-3 justify-end items-center">
                    <?php if($allPickingDone): ?>
                        <button type="button" onclick="cetakRa('<?php echo e($tanggal); ?>', 'pricing')"
                                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Cetak RA Pricing</button>
                        <button type="button" onclick="cetakRa('<?php echo e($tanggal); ?>', 'pemesanan')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">RA PICKING</button>
                        <button type="button" onclick="cetakRa('<?php echo e($tanggal); ?>', 'qc')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">RA QC</button>
                        <button type="button" onclick="cetakRa('<?php echo e($tanggal); ?>', 'packing')"
                                class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">RA PACKING</button>
                        <button type="button" onclick="cetakRa('<?php echo e($tanggal); ?>', 'ekspedisi')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium">RA EKSPEDISI</button>
                    <?php else: ?>
                        <span class="text-sm text-gray-500">Tombol RA muncul setelah semua Picking List di tanggal ini dicetak.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-500">
            Belum ada data Rekap Aktual Logistik. Proses order dari halaman Logistik Aktif terlebih dahulu.
        </div>
    <?php endif; ?>
</div>


<div id="pickingModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-xl font-semibold mb-2">Print Picking List</h3>
        <p class="text-gray-600 mb-6" id="modalMessage"></p>
        <div class="flex gap-3">
            <button type="button" onclick="tutupPicking()" class="flex-1 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 font-medium">Batal</button>
            <button type="button" onclick="cetakPicking()" class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">Cetak Sekarang</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const RA_KEY = 'raLogistikOpen';

    function setOpen(id, open) {
        const content = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (!content) return;
        content.classList.toggle('hidden', !open);
        if (icon) icon.classList.toggle('-rotate-90', !open);
    }

    function toggleContent(id) {
        const isHidden = document.getElementById(id).classList.contains('hidden');
        setOpen(id, isHidden);
        try {
            isHidden ? localStorage.setItem(RA_KEY, id) : localStorage.removeItem(RA_KEY);
        } catch (e) {}
    }

    const RA_URL = {
        pricing:   `<?php echo e(route('order.realisasi-logistik.print-pdf')); ?>`,
        pemesanan: `<?php echo e(route('order.realisasi-logistik.print-pemesanan')); ?>`,
        qc:        `<?php echo e(route('order.realisasi-logistik.print-qc')); ?>`,
        packing:   `<?php echo e(route('order.realisasi-logistik.print-packing')); ?>`,
        ekspedisi: `<?php echo e(route('order.realisasi-logistik.print-ekspedisi')); ?>`,
    };

    function cetakRa(tanggal, jenis) {
        const box = document.querySelector(`[data-tanggal="${tanggal}"]`);
        if (!box) return;

        const ids = Array.from(box.querySelectorAll('tr[data-id]'))
            .map(r => r.dataset.id)
            .join(',');

        const extra = jenis === 'pricing' ? '&mark_printed=true' : '';
        window.open(`${RA_URL[jenis]}?ids=${ids}${extra}`, '_blank');
        setTimeout(() => location.reload(), 1200);
    }

    let pickingId = null;

    function bukaPicking(id, noPl) {
        pickingId = id;
        document.getElementById('modalMessage').innerHTML = `Cetak Picking List untuk No. PL <strong>${noPl}</strong>?`;
        document.getElementById('pickingModal').classList.remove('hidden');
    }

    function tutupPicking() {
        document.getElementById('pickingModal').classList.add('hidden');
    }

    function cetakPicking() {
        if (!pickingId) return;
        window.open(`<?php echo e(url('order/realisasi-logistik/picking-list')); ?>/${pickingId}`, '_blank');
        tutupPicking();
        setTimeout(() => location.reload(), 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        try {
            const opened = localStorage.getItem(RA_KEY);
            if (opened) setOpen(opened, true);
        } catch (e) {}
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Order\Resources/views/order/logistik-printed.blade.php ENDPATH**/ ?>