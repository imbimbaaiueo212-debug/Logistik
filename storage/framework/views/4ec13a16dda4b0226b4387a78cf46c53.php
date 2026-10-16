<?php $__env->startSection('title', 'Data Order biMBA Shop'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 4400px;
        font-size: 0.8125rem;
    }
    .data-table th,
    .data-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
        border-bottom: 1px solid rgba(15, 27, 51, 0.06);
    }
    .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #F4F6FA;
        color: rgba(15, 27, 51, 0.6);
        font-weight: 600;
        font-size: 0.75rem;
        border-bottom: 1px solid rgba(15, 27, 51, 0.1);
    }
    .data-table tbody tr:hover td { background-color: #F7F9FC; }
    .data-table .ctr { text-align: center; }
    .data-table .num { text-align: right; font-variant-numeric: tabular-nums; }

    /* Teks panjang dipotong "...", isi lengkap muncul saat kursor diarahkan (atribut title) */
    .cell-clip { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }

    /* Kolom pertama dan Aksi tetap terlihat saat tabel digeser ke samping */
    .data-table .sticky-l { position: sticky; left: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-l { z-index: 30; background: #F4F6FA; }
    .data-table .sticky-r { position: sticky; right: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-r { z-index: 30; background: #F4F6FA; }
    .data-table tbody tr:hover .sticky-l,
    .data-table tbody tr:hover .sticky-r { background-color: #F7F9FC; }

    /* Sembunyikan teks "Showing ..." bawaan pagination Laravel */
    .pager nav p { display: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    <?php
        // Definisi kolom tabel: [judul, nama field, tipe]
        // Tipe: text, clip, tgl, rp, gr, qty, status, nama (gabungan first_name + last_name)
        $kolom = [
            ['Order ID', 'order_id'],
            ['Waktu Import', 'created_at', 'tgl'],
            ['Order Date', 'order_date', 'tgl'],
            ['Item SKU', 'item_sku'],
            ['Item Name', 'item_name', 'clip'],
            ['Item Price', 'item_price', 'rp'],
            ['Qty', 'item_qty', 'qty'],
            ['Status', 'status', 'status'],
            ['Order Total', 'order_total', 'rp'],
            ['Ship Total', 'ship_total', 'rp'],
            ['Berat', 'order_weight', 'gr'],
            ['Discount', 'discount_total', 'rp'],
            ['Refunded', 'refunded_total', 'rp'],
            ['Payment Method', 'payment_method'],
            ['Partial Pay Wallet ID', 'partial_pay_wallet_id'],
            ['Billing Name', 'billing', 'nama'],
            ['Billing Company', 'billing_company', 'clip'],
            ['Billing Address 1', 'billing_address_1', 'clip'],
            ['Billing Address 2', 'billing_address_2', 'clip'],
            ['Billing City', 'billing_city'],
            ['Billing State', 'billing_state'],
            ['Billing Postcode', 'billing_postcode'],
            ['Billing Country', 'billing_country'],
            ['Billing Phone', 'billing_phone'],
            ['Billing Email', 'billing_email', 'clip'],
            ['Shipping Name', 'shipping', 'nama'],
            ['Shipping Company', 'shipping_company', 'clip'],
            ['Shipping Address 1', 'shipping_address_1', 'clip'],
            ['Shipping Address 2', 'shipping_address_2', 'clip'],
            ['Shipping City', 'shipping_city'],
            ['Shipping State', 'shipping_state'],
            ['Shipping Postcode', 'shipping_postcode'],
            ['Shipping Country', 'shipping_country'],
            ['Shipping Phone', 'shipping_phone'],
        ];

        // Perataan kolom per tipe
        $rataTipe = ['rp' => 'num', 'gr' => 'num', 'qty' => 'ctr'];

        // Filter teks: [name, label, placeholder]
        $filterTeks = [
            ['order_id', 'Order ID', 'Cari Order ID'],
            ['item_sku', 'Item SKU', 'SKU'],
            ['item_name', 'Item Name', 'Nama item'],
            ['billing_name', 'Billing Name', 'Nama billing'],
        ];

        $optPayment = ['bacs' => 'bacs', 'lunas_payment_gateway' => 'lunas_payment_gateway'];
        $optStatus  = ['completed' => 'Completed', 'processing' => 'Processing', 'on-hold' => 'On Hold', 'pending' => 'Pending'];
        $perPage    = (int) request('per_page', $bimbashopOrders->perPage());

        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-sm text-navy-950/60 mb-1.5';
    ?>

    
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Data Order (biMBA Shop)</h1>
            <p class="mt-1 text-sm text-navy-950/55">Import dan kelola data order dari biMBA Shop</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!--<a href="<?php echo e(route('import.index')); ?>"
               class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Daftar Import
            </a>-->
            <button type="button"
                    onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                Import Data Baru
            </button>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <div id="importForm" class="hidden mt-5 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <h2 class="text-base font-semibold text-navy-950">Upload file Excel / CSV</h2>

        <form action="<?php echo e(route('import.bimbashop.store')); ?>" method="POST" enctype="multipart/form-data" class="mt-4">
            <?php echo csrf_field(); ?>
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label for="import_file" class="<?php echo e($lbl); ?>">Pilih file</label>
                    <input id="import_file" type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-navy-950/60
                                  file:mr-4 file:py-2.5 file:px-5
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-navy-700/10 file:text-navy-700
                                  hover:file:bg-navy-700/20">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                        Upload dan import
                    </button>
                    <button type="button"
                            onclick="document.getElementById('importForm').classList.add('hidden')"
                            class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">
                        Batal
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 text-xs text-navy-950/45">Format yang didukung: .xlsx, .xls, .csv. Ukuran maksimal 10 MB.</p>
    </div>

    
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">

                <?php $__currentLoopData = $filterTeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$nama, $label, $ph]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <label for="f_<?php echo e($nama); ?>" class="<?php echo e($lbl); ?>"><?php echo e($label); ?></label>
                        <input id="f_<?php echo e($nama); ?>" type="text" name="<?php echo e($nama); ?>" value="<?php echo e(request($nama)); ?>"
                               placeholder="<?php echo e($ph); ?>" class="<?php echo e($inp); ?>">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div>
                    <label for="f_payment" class="<?php echo e($lbl); ?>">Payment Method</label>
                    <select id="f_payment" name="payment_method" class="<?php echo e($inp); ?>">
                        <option value="">Semua</option>
                        <?php $__currentLoopData = $optPayment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $teks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($nilai); ?>" <?php echo e(request('payment_method') == $nilai ? 'selected' : ''); ?>><?php echo e($teks); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label for="f_status" class="<?php echo e($lbl); ?>">Status</label>
                    <select id="f_status" name="status" class="<?php echo e($inp); ?>">
                        <option value="">Semua status</option>
                        <?php $__currentLoopData = $optStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $teks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($nilai); ?>" <?php echo e(request('status') == $nilai ? 'selected' : ''); ?>><?php echo e($teks); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label for="f_start" class="<?php echo e($lbl); ?>">Dari tanggal</label>
                    <input id="f_start" type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="<?php echo e($inp); ?>">
                </div>

                <div>
                    <label for="f_end" class="<?php echo e($lbl); ?>">Sampai tanggal</label>
                    <input id="f_end" type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="<?php echo e($inp); ?>">
                </div>

                <div>
                    <label for="f_per_page" class="<?php echo e($lbl); ?>">Tampilkan</label>
                    <select id="f_per_page" name="per_page" onchange="this.form.submit()" class="<?php echo e($inp); ?>">
                        <?php $__currentLoopData = [5, 10, 25, 50, 100, 200, 500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opsi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($opsi); ?>" <?php echo e($perPage === $opsi ? 'selected' : ''); ?>><?php echo e($opsi); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    Terapkan filter
                </button>
                <a href="<?php echo e(route('import.bimbashop')); ?>"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-3 py-2.5">
                    Reset
                </a>
            </div>
        </form>
    </div>

    
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php $__currentLoopData = $kolom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $kol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="<?php echo e($i === 0 ? 'sticky-l' : ''); ?> <?php echo e($rataTipe[$kol[2] ?? 'text'] ?? ''); ?>"><?php echo e($kol[0]); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="ctr sticky-r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bimbashopOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = $kolom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $kol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $tipe = $kol[2] ?? 'text';

                                    if ($tipe === 'nama') {
                                        $val = trim(($order->{$kol[1] . '_first_name'} ?? '') . ' ' . ($order->{$kol[1] . '_last_name'} ?? ''));
                                    } else {
                                        $val = $order->{$kol[1]};
                                    }

                                    if ($tipe === 'tgl' && filled($val)) {
                                        $val = \Illuminate\Support\Carbon::parse($val)->format('d/m/Y H:i');
                                    } elseif ($tipe === 'rp') {
                                        $val = 'Rp ' . number_format($val ?? 0, 0, ',', '.');
                                    } elseif ($tipe === 'gr') {
                                        $val = number_format($val ?? 0, 0, ',', '.') . ' gr';
                                    } elseif ($tipe === 'qty') {
                                        $val = $val ?? 0;
                                    } elseif ($tipe === 'status' && blank($val)) {
                                        $val = 'Pending';
                                    }

                                    $tampil = filled($val) ? $val : '-';
                                    $rata   = $rataTipe[$tipe] ?? '';
                                ?>

                                <td class="<?php echo e($i === 0 ? 'sticky-l font-medium' : ''); ?> <?php echo e($rata); ?> <?php echo e($tipe === 'clip' ? 'cell-clip' : ''); ?>"
                                    <?php if($tipe === 'clip'): ?> title="<?php echo e($tampil); ?>" <?php endif; ?>>
                                    <?php if($tipe === 'status'): ?>
                                        <?php
                                            $st    = strtolower($tampil);
                                            $badge = $st === 'completed' ? 'bg-emerald-50 text-emerald-700'
                                                   : ($st === 'processing' ? 'bg-navy-700/10 text-navy-700' : 'bg-amber-50 text-amber-700');
                                        ?>
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($badge); ?>"><?php echo e($tampil); ?></span>
                                    <?php else: ?>
                                        <?php echo e($tampil); ?>

                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <td class="ctr sticky-r">
                                <div class="inline-flex items-center gap-0.5">
                                    <a href="<?php echo e(route('import.bimbashop.edit', $order->id)); ?>" title="Edit" aria-label="Edit order"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                                    </a>
                                    <form action="<?php echo e(route('import.bimbashop.destroy', $order->id)); ?>" method="POST"
                                          onsubmit="return confirm('Yakin hapus data ini? Data yang dihapus tidak bisa dikembalikan.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" title="Hapus" aria-label="Hapus order"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(count($kolom) + 1); ?>" class="!text-center py-16 text-navy-950/50">
                                Tidak ada data yang sesuai filter.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="mt-5 flex flex-col lg:flex-row items-center justify-between gap-4">
        <p class="text-sm text-navy-950/60">
            Menampilkan
            <span class="font-semibold text-navy-950"><?php echo e(number_format($bimbashopOrders->firstItem() ?? 0, 0, ',', '.')); ?></span>
            sampai
            <span class="font-semibold text-navy-950"><?php echo e(number_format($bimbashopOrders->lastItem() ?? 0, 0, ',', '.')); ?></span>
            dari
            <span class="font-semibold text-navy-950"><?php echo e(number_format($bimbashopOrders->total(), 0, ',', '.')); ?></span>
            data
        </p>
        <div class="pager max-w-full overflow-x-auto">
            <?php echo e($bimbashopOrders->withQueryString()->onEachSide(1)->links('pagination::tailwind')); ?>

        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Import\Resources/views/import/bimbashop.blade.php ENDPATH**/ ?>