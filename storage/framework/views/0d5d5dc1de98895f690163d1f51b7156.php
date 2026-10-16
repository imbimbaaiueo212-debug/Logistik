

<?php $__env->startSection('title', 'Picking List - Jakarta Pasif - biMBA Logistik'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    table { border-collapse: collapse; }
    th, td { padding: 12px 8px; font-size: 0.85rem; }
    th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
    tr:hover { background-color: #f8fafc; }

    .processed-row {
        opacity: 0.65;
        background-color: #f1f5f9 !important;
        color: #64748b;
    }

    .pagination { display: flex; gap: 6px; }
    .pagination li a, .pagination li span {
        padding: 8px 14px;
        border-radius: 9999px;
        font-size: 0.9rem;
    }
    .pagination .active span {
        background-color: #4f46e5;
        color: white;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="p-8">

    <!-- Header + Filter Kategori -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Picking List - Jakarta Pasif</h1>
            <p class="text-gray-600">Daftar Picking yang sudah dibuat dari order Jakarta Pasif</p>
        </div>
        <div class="flex items-center gap-2 bg-white rounded-3xl p-1 shadow border">
            <a href="<?php echo e(route('picking.index')); ?>"
               class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">
                Menu
            </a>
            <a href="<?php echo e(route('picking.jakarta.pasif')); ?>"
               class="px-6 py-3 rounded-3xl font-medium transition-all <?php echo e(!request('kategori') ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100'); ?>">
                Semua
            </a>
            <a href="<?php echo e(route('picking.jakarta.pasif')); ?>?kategori=Modul"
               class="px-6 py-3 rounded-3xl font-medium transition-all <?php echo e(request('kategori') === 'Modul' ? 'bg-green-600 text-white shadow-sm' : 'hover:bg-gray-100'); ?>">
                🟢 Modul
            </a>
            <a href="<?php echo e(route('picking.jakarta.pasif')); ?>?kategori=Majalah"
               class="px-6 py-3 rounded-3xl font-medium transition-all <?php echo e(request('kategori') === 'Majalah' ? 'bg-blue-600 text-white shadow-sm' : 'hover:bg-gray-100'); ?>">
                🔵 Majalah
            </a>
            <a href="<?php echo e(route('picking.jakarta.pasif')); ?>?kategori=Sertifikat"
               class="px-6 py-3 rounded-3xl font-medium transition-all <?php echo e(request('kategori') === 'Sertifikat' ? 'bg-red-600 text-white shadow-sm' : 'hover:bg-gray-100'); ?>">
                🔴 Sertifikat
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No PL / ID Pesan</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Cari No PL...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                <input type="text" name="nama_unit" value="<?php echo e(request('nama_unit')); ?>"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Unit...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                    🔍 Terapkan Filter
                </button>
                <a href="<?php echo e(route('picking.jakarta.pasif')); ?>" class="text-gray-500 hover:text-red-600 px-4 py-2.5">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm" id="picking-table">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="text-left px-4 py-3">No</th>
                    <th class="text-left px-4 py-3">ID Pesan</th>
                    <th class="text-left px-4 py-3">Nama Unit</th>
                    <th class="text-center px-4 py-3">Billing Last Name</th>
                    <th class="text-center px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Payment Date</th>
                    <th class="text-center px-4 py-3">Waktu Estimasi</th>
                    <th class="text-center px-4 py-3">Distribusi</th>
                    <th class="text-center px-4 py-3">Waktu Terima (RA, PL, dan PS)</th>
                    <th class="extra-col text-center px-4 py-3 hidden">Status Persiapan</th>
                    <th class="extra-col text-center px-4 py-3 hidden">Tanggal Picking</th>
                    <th class="extra-col text-center px-4 py-3 hidden">PIC</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $pickingItems = $item->items ?? $item->pickingItems ?? collect();
            $isDone = ($item->status_persiapan ?? '') === 'Sudah' && !empty($item->pic);
        ?>
        <tr class="hover:bg-gray-50 <?php echo e($isDone ? 'processed-row' : ''); ?>" data-id="<?php echo e($item->id); ?>">
            <td class="px-4 py-3 text-center"><?php echo e($data->firstItem() + $index); ?></td>
            <td class="px-4 py-3"><?php echo e($item->no_pl ?? '-'); ?></td>
            <td class="px-4 py-3"><?php echo e($item->nama_unit ?? '-'); ?></td>
            <td class="px-4 py-3 text-center">
                <?php echo e($item->billing_last_name ?? '-'); ?>

                <?php if($item->billing_company): ?>
                    <br><small class="text-gray-500"><?php echo e($item->billing_company); ?></small>
                <?php endif; ?>
            </td>
            <td class="text-center text-sm px-4 py-3">
                <?php
                    $kategoriProduk = $pickingItems
                        ->map(function ($pickingItem) {
                            $sku = $pickingItem->product?->sku ?? $pickingItem->item_sku;
                            $kategori = $pickingItem->product?->kategori;
                            $namaBarang = $pickingItem->item_name;
                            $label = $pickingItem->product?->label ?? '';
                            $kategoriLower = strtolower($kategori ?? '');
                            $namaBarangLower = strtolower($namaBarang ?? '');

                            if (str_contains($kategoriLower, 'sertifikat') || str_contains($namaBarangLower, 'sertifikat')) {
                                return $sku ? $sku . ' - ' . ($kategori ?: 'Sertifikat') : ($kategori ?: 'Sertifikat');
                            }
                            if (str_contains($kategoriLower, 'majalah') || str_contains($namaBarangLower, 'majalah')) {
                                return $label ? $label . ' - ' . ($kategori ?: 'Majalah') : ($kategori ?: 'Majalah');
                            }
                            return $kategori ?? $namaBarang ?? '-';
                        })
                        ->filter()
                        ->unique()
                        ->implode(' | ');
                ?>
                <?php echo e($kategoriProduk ?: ($item->kategori_order ?? $item->pesanan ?? '-')); ?>

            </td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if($item->payment_date): ?>
                    <?php echo e(\Carbon\Carbon::parse($item->payment_date)->format('d/m/Y')); ?>

                <?php else: ?>
                    <span class="text-gray-400">-</span>
                <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <?php if($item->waktu_estimasi_persiapan): ?>
                    <?php echo e(\Carbon\Carbon::parse($item->waktu_estimasi_persiapan)->format('d/m/Y')); ?>

                <?php else: ?>
                    <span class="text-gray-400">-</span>
                <?php endif; ?>
            </td>
            <td class="text-center px-4 py-3">
                <?php echo e($item->ekspedisi ?? '-'); ?>

                <?php echo e($item->service_pengiriman ? '- ' . $item->service_pengiriman : ''); ?>

            </td>

            <td class="waktu-terima px-4 py-3 text-center whitespace-nowrap">
                <?php if($item->tgl_terima ?? null): ?>
                    <span class="text-emerald-600 font-medium"><?php echo e(\Carbon\Carbon::parse($item->tgl_terima)->format('d/m/Y')); ?></span>
                <?php else: ?>
                    <span class="text-gray-400">-</span>
                <?php endif; ?>
            </td>

            
            <td class="extra-col px-4 py-3 text-center <?php echo e(($item->tgl_terima ?? null) ? '' : 'hidden'); ?>">
                <select class="status-select border rounded-lg px-2 py-1 text-sm
                    <?php if($isDone): ?> bg-green-100 text-green-700
                    <?php else: ?> bg-red-100 text-red-700
                    <?php endif; ?>"
                    data-id="<?php echo e($item->id); ?>"
                    <?php echo e($isDone ? 'disabled' : ''); ?>>
                    <option value="Belum" <?php echo e(($item->status_persiapan ?? 'Belum') == 'Belum' ? 'selected' : ''); ?>>Belum</option>
                    <option value="Sudah" <?php echo e(($item->status_persiapan ?? '') == 'Sudah' ? 'selected' : ''); ?>>Sudah</option>
                </select>
            </td>

            
            <td class="extra-col tanggal-picking px-4 py-3 text-center whitespace-nowrap <?php echo e(($item->tgl_terima ?? null) ? '' : 'hidden'); ?>">
                <?php if($item->tgl_picking): ?>
                    <?php echo e(\Carbon\Carbon::parse($item->tgl_picking)->format('d/m/Y')); ?>

                <?php else: ?>
                    <span class="text-gray-400">-</span>
                <?php endif; ?>
            </td>

            
            <td class="extra-col px-4 py-3 text-center <?php echo e(($item->tgl_terima ?? null) ? '' : 'hidden'); ?>">
                <select class="pic-select border rounded-lg px-2 py-1 text-sm"
                        data-id="<?php echo e($item->id); ?>"
                        <?php echo e($isDone ? 'disabled' : ''); ?>>
                    <option value="">-- Pilih --</option>
                    <option value="Asep" <?php echo e(($item->pic ?? '') == 'Asep' ? 'selected' : ''); ?>>Asep</option>
                    <option value="Arif" <?php echo e(($item->pic ?? '') == 'Arif' ? 'selected' : ''); ?>>Arif</option>
                    <option value="Rama" <?php echo e(($item->pic ?? '') == 'Rama' ? 'selected' : ''); ?>>Rama</option>
                    <option value="Riky" <?php echo e(($item->pic ?? '') == 'Riky' ? 'selected' : ''); ?>>Riky</option>
                </select>
            </td>

            
            <td class="text-center px-4 py-3">
                <div class="flex items-center justify-center gap-4 text-sm">
                    <label class="flex items-center gap-1 <?php echo e($isDone ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'); ?>" title="Checklist">
                        <input type="checkbox"
                               class="w-4 h-4 accent-emerald-600 checklist-item"
                               data-id="<?php echo e($item->id); ?>"
                               <?php echo e(($item->tgl_terima ?? null) ? 'checked' : ''); ?>

                               <?php echo e($isDone ? 'disabled' : ''); ?>>
                        <span class="text-emerald-600 text-xs font-medium">Cek</span>
                    </label>
                </div>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="13" class="text-center py-16 text-gray-500">
                Belum ada Picking List Jakarta Pasif.
            </td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>

        <?php if($data instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
            <div class="px-6 py-4 bg-white border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <span class="font-medium"><?php echo e($data->firstItem()); ?></span>
                    sampai <span class="font-medium"><?php echo e($data->lastItem()); ?></span>
                    dari total <span class="font-medium"><?php echo e($data->total()); ?></span> data
                </div>
                <div><?php echo e($data->appends(request()->query())->links()); ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {

    function formatTanggalHariIni() {
        return new Date().toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    }

    // Fungsi untuk mengunci baris
    function lockRow(row) {
        row.classList.add('processed-row');

        const checkbox = row.querySelector('.checklist-item');
        if (checkbox) {
            checkbox.disabled = true;
            checkbox.closest('label')?.classList.add('cursor-not-allowed', 'opacity-60');
            checkbox.closest('label')?.classList.remove('cursor-pointer');
        }

        const statusSelect = row.querySelector('.status-select');
        if (statusSelect) {
            statusSelect.disabled = true;
            statusSelect.classList.remove('bg-red-100', 'text-red-700');
            statusSelect.classList.add('bg-green-100', 'text-green-700');
        }

        const picSelect = row.querySelector('.pic-select');
        if (picSelect) {
            picSelect.disabled = true;
        }
    }

    // Cek apakah sudah siap dikunci (Status Sudah + PIC terisi)
    function shouldLock(row) {
        const statusSelect = row.querySelector('.status-select');
        const picSelect = row.querySelector('.pic-select');
        return statusSelect?.value === 'Sudah' && picSelect?.value !== '';
    }

    // ==================== CHECKLIST ====================
    document.querySelectorAll('.checklist-item').forEach(chk => {
        if (chk.disabled) return;

        chk.addEventListener('change', function () {
            const id = this.dataset.id;
            const checked = this.checked;
            const row = this.closest('tr');

            row.querySelector('.waktu-terima').innerHTML = checked
                ? `<span class="text-emerald-600 font-medium">${formatTanggalHariIni()}</span>`
                : `<span class="text-gray-400">-</span>`;

            row.querySelectorAll('.extra-col').forEach(col => {
                col.classList.toggle('hidden', !checked);
            });

            const hasChecked = document.querySelectorAll('.checklist-item:checked').length > 0;
            document.querySelectorAll('th.extra-col').forEach(th => {
                th.classList.toggle('hidden', !hasChecked);
            });

            fetch("<?php echo e(route('picking.pasif.checklist')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ id: id, checked: checked })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update checklist: ' + (data.message || ''));
                    this.checked = !checked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
                this.checked = !checked;
            });
        });
    });

    // ==================== STATUS PERSIAPAN ====================
    document.querySelectorAll('.status-select').forEach(select => {
        if (select.disabled) return;

        select.addEventListener('change', function () {
            const id = this.dataset.id;
            const status = this.value;
            const row = this.closest('tr');

            this.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            if (status === 'Sudah') {
                this.classList.add('bg-green-100', 'text-green-700');
            } else {
                this.classList.add('bg-red-100', 'text-red-700');
            }

            fetch("<?php echo e(route('picking.pasif.status')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    id: id,
                    status_persiapan: status
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update status: ' + (data.message || ''));
                    return;
                }

                // Hanya kunci jika Status = Sudah DAN PIC sudah terisi
                if (shouldLock(row)) {
                    lockRow(row);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan');
            });
        });
    });

    // ==================== PIC ====================
    document.querySelectorAll('.pic-select').forEach(select => {
        if (select.disabled) return;

        select.addEventListener('change', function () {
            const id = this.dataset.id;
            const pic = this.value;
            const row = this.closest('tr');

            fetch("<?php echo e(route('picking.pasif.pic')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ id: id, pic: pic })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal update PIC');
                    return;
                }

                // Setelah PIC diisi, cek apakah Status sudah Sudah → kunci
                if (shouldLock(row)) {
                    lockRow(row);
                }
            })
            .catch(err => console.error(err));
        });
    });

    // ==================== SAAT PAGE LOAD ====================
    document.querySelectorAll('tr[data-id]').forEach(row => {
        if (shouldLock(row)) {
            lockRow(row);
            row.querySelectorAll('.extra-col').forEach(col => col.classList.remove('hidden'));
        }
    });

    // Tampilkan header extra-col jika ada yang sudah dicek
    const hasAnyChecked = document.querySelectorAll('.checklist-item:checked').length > 0;
    if (hasAnyChecked) {
        document.querySelectorAll('th.extra-col').forEach(th => th.classList.remove('hidden'));
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Picking\Resources/views/picking/jakarta/pasif.blade.php ENDPATH**/ ?>