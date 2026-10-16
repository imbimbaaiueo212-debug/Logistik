<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - <?php echo e($no_pl ?? $item->no_pl ?? '-'); ?></title>
    <style>
        @page {
            size: A5 portrait;
            margin: 3mm 4mm 5mm 4mm;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10.8px;
            color: #333;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 135mm;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .header {
            text-align: left;
            border-bottom: 1px solid #000000;
            margin-bottom: 8px;
            padding-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        th, td {
            border: 1px solid #333;
            padding: 3.5px 3px;
            font-size: 13px;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: center;
        }

        .center { text-align: center; }
        .left { text-align: left; }

        .info-table td {
            border: none;
            padding: 3px 2px;
            vertical-align: top;
        }
    </style>
</head>
<body>

<div class="container">
    <div style="padding: 1mm 2mm 10mm 2mm;">

        <!-- HEADER -->
        <div class="header" style="margin-bottom: 8px;">
            <?php
                $kategoriJudul = '';

                if ($data->isNotEmpty()) {
                    $firstRow = $data->first();

                    $skuRaw = strtoupper(trim($firstRow->item_sku ?? $firstRow->sku ?? ''));
                    $sku = preg_replace('/[^A-Z0-9]/', '', $skuRaw);

                    $nama = strtolower(trim(
                        $firstRow->item_name
                        ?? $firstRow->nama_produk
                        ?? $firstRow->nama_barang
                        ?? $kategori_order
                        ?? ''
                    ));

                    if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STA';
                    } elseif (str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STPB';
                    } elseif (str_contains($nama, 'modul') || str_contains(strtolower($kategori_order ?? ''), 'modul')) {
                        $kategoriJudul = 'MODUL';
                    } elseif (str_contains($nama, 'majalah') || str_contains(strtolower($kategori_order ?? ''), 'majalah')) {
                        $kategoriJudul = 'MAJALAH SAHABAT biMBA';
                    } elseif (
                        str_contains($nama, 'sertifikat')
                        || str_contains($nama, 'surat tanda')
                        || str_contains(strtolower($kategori_order ?? ''), 'sertifikat')
                    ) {
                        $kategoriJudul = 'SERTIFIKAT';
                    } elseif (str_contains($nama, 'seragam')) {
                        $kategoriJudul = 'SERAGAM';
                    }
                }

                if (empty($kategoriJudul) && !empty($kategori_order)) {
                    $kategoriJudul = strtoupper($kategori_order);
                }
            ?>

            <span class="header-title" style="font-size: 20px; line-height: 1;">
                PICKING LIST
                <?php if($kategoriJudul): ?>
                    <span style="font-size: 20px; color: #000000;">| <?php echo e($kategoriJudul); ?></span>
                <?php endif; ?>
            </span><br>
            <span style="font-size: 18px; font-weight: 600; margin-top: -2px; display: inline-block;">
                <?php echo e(str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? $picking->vendor ?? 'Jakarta Pasif')); ?>

            </span>
        </div>

        <!-- INFO -->
        <table class="info-table" style="margin-bottom: 2px;">
            <tr>
                <td style="width:57%;">
                    <strong><?php echo e($no_pl ?? $item->no_pl ?? '-'); ?></strong><br>
                    PayDate: <strong>
                        <?php echo e(($item->tgl_bayar ?? null)
                            ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i')
                            : '-'); ?>

                    </strong><br>
                    Estimasi: <strong>
                        <?php echo e(($item->tgl_estimasi ?? null)
                            ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                            : '-'); ?>

                    </strong><br>
                    <strong><?php echo e($item->nama_unit ?? $picking->nama_unit ?? '-'); ?></strong><br>
                    <strong>
                        <?php echo e($jakarta_pasif->kirim
                            ?? $picking->kirim
                            ?? $picking->alamat_kirim
                            ?? '-'); ?>

                    </strong><br>
                    <strong>
                        <?php echo e($item->pengiriman ?? $picking->ekspedisi ?? '-'); ?>

                        |
                        <?php echo e($item->service_pengiriman ?? $picking->service_pengiriman ?? '-'); ?>

                    </strong>
                    <?php echo e($data->count()); ?> | <?php echo e($data->sum('item_qty') ?: $data->sum('qty') ?: $data->count()); ?>

                </td>

                <td style="width:20%; text-align:center; border:1px solid #333;">
                    <strong style="font-size:13.5px;">PARAF</strong>
                    <br><br><br>
                </td>
            </tr>
        </table>

        <!-- TABEL PRODUK -->
        <table>
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th>NAMA PRODUK</th>
                    <th width="17%">SKU</th>
                    <th width="7%">QTY</th>
                    <th width="8%">CEK</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="center"><?php echo e($index + 1); ?></td>
                        <td class="left">
                            <?php
                                $namaProduk = $row->item_name ?? $row->nama_produk ?? $row->nama_barang ?? '-';
                                $isMajalah = str_contains(strtolower($namaProduk), 'majalah');

                                if ($isMajalah) {
                                    $namaProduk = preg_replace('/\s*\([^)]*\)/', '', $namaProduk);
                                    $namaProduk = trim($namaProduk);
                                }
                            ?>
                            <?php echo e($namaProduk); ?>

                        </td>
                        <td class="center">
                            <?php
                                $sku = trim($row->item_sku ?? $row->sku ?? '');
                                $cleanSku = str_ireplace(['JKTP', 'JKT', '-'], '', $sku);
                                $cleanSku = trim($cleanSku);
                            ?>
                            <span class="font-medium"><?php echo e($cleanSku ?: '-'); ?></span>
                        </td>
                        <td class="center"><?php echo e($row->item_qty ?? $row->qty ?? 1); ?></td>
                        <td class="center"></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="center" style="padding:10px;color:#999;">
                            Tidak ada item
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>


<script type="text/php">
if (isset($pdf)) {
    $noOrder = "<?php echo e($no_pl ?? $item->no_pl ?? '-'); ?>";
    $printed = "<?php echo e(now()->format('d/m/Y H:i')); ?>";

    $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($noOrder, $printed) {
        $font = $fontMetrics->getFont("DejaVu Sans", "normal");

        $canvas->text(
            20,
            575,
            "No. Order: {$noOrder} | Dicetak: {$printed} | Jakarta Pasif",
            $font,
            8
        );

        $canvas->text(
            385,
            575,
            "{$pageNumber} / {$pageCount}",
            $font,
            8
        );
    });
}
</script>

</body>
</html><?php /**PATH C:\xampp\htdocs\bimba-logistik\app\Modules\Order\Resources/views/order/picking-list-pdf-pasif.blade.php ENDPATH**/ ?>