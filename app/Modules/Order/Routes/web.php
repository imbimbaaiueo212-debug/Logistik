<?php

// ====================== ORDER (Jakarta Aktif / Pasif, Realisasi, cetak) ======================
// Di-load oleh OrderServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Order\Http\Controllers\JakartaAktifController;
use App\Modules\Order\Http\Controllers\JakartaPasifController;
use App\Modules\Order\Http\Controllers\LogistikAktifController;
use App\Modules\Order\Http\Controllers\LogistikPasifController;
use App\Modules\Order\Http\Controllers\RealisasiLogistikPasifController;
use App\Modules\Order\Http\Controllers\OrderKategoriController;
use App\Modules\Order\Http\Controllers\OrderMenuController;
use App\Modules\Order\Http\Controllers\RealisasiAktifController;
use App\Modules\Order\Http\Controllers\RealisasiPasifController;
use App\Modules\Order\Http\Controllers\RealisasiLogistikAktifController;

// === ORDER ROUTES ===
Route::prefix('order')
    ->name('order.')
    ->middleware('auth')
    ->group(function () {

        // =========================
        // MENU
        // =========================
        Route::get('/', [OrderMenuController::class, 'index'])->name('index');
        Route::get('/unit-aktif', [OrderMenuController::class, 'unitAktif'])->name('unit-aktif');
        Route::get('/unit-pasif', [OrderMenuController::class, 'unitPasif'])->name('unit-pasif');

        // =========================
        // JAKARTA AKTIF
        // =========================
        Route::get('/jakarta-aktif', [JakartaAktifController::class, 'jakartaAktif'])->name('jakarta-aktif');
        Route::get('/modul', [OrderKategoriController::class, 'modul'])->name('modul');
        Route::get('/majalah', [OrderKategoriController::class, 'majalah'])->name('majalah');
        Route::get('/sertifikat', [OrderKategoriController::class, 'sertifikat'])->name('sertifikat');

        Route::post('/jakarta-aktif/import', [JakartaAktifController::class, 'importJakartaAktif'])->name('jakarta-aktif.import');
        Route::post('/jakarta-aktif/sync-jkt', [JakartaAktifController::class, 'syncJktFromBimbashop'])->name('jakarta-aktif.sync-jkt');
        Route::post('/jakarta-aktif/bulk-action', [JakartaAktifController::class, 'bulkActionJakartaAktif'])->name('jakarta-aktif.bulk-action');
        Route::get('/jakarta-aktif/{id}/edit', [JakartaAktifController::class, 'editJakartaAktif'])->name('jakarta-aktif.edit');
        Route::put('/jakarta-aktif/{id}', [JakartaAktifController::class, 'updateJakartaAktif'])->name('jakarta-aktif.update');
        Route::get('/jakarta-aktif/filtered-ids', [JakartaAktifController::class, 'getFilteredIds'])->name('jakarta-aktif.filtered-ids');
        Route::post('/jakarta-aktif/get-modal-data', [JakartaAktifController::class, 'getModalData'])->name('jakarta-aktif.get-modal-data');
        Route::get('/jakarta-aktif/export', [JakartaAktifController::class, 'exportJakartaAktif'])->name('jakarta-aktif.export');


        // =========================
        // LOGISTIK AKTIF (SKU LG)
        // =========================
        Route::get('/logistik-aktif', [LogistikAktifController::class, 'logistikAktif'])->name('logistik-aktif');
        Route::post('/logistik-aktif/sync-lg', [LogistikAktifController::class, 'syncLgFromBimbashop'])->name('logistik-aktif.sync-lg');
        Route::post('/logistik-aktif/bulk-action', [LogistikAktifController::class, 'bulkActionLogistikAktif'])->name('logistik-aktif.bulk-action');
        Route::get('/logistik-aktif/filtered-ids', [LogistikAktifController::class, 'getFilteredIds'])->name('logistik-aktif.filtered-ids');
        Route::post('/logistik-aktif/get-modal-data', [LogistikAktifController::class, 'getModalData'])->name('logistik-aktif.get-modal-data');
        Route::get('/logistik-aktif/export', [LogistikAktifController::class, 'exportLogistikAktif'])->name('logistik-aktif.export');
        Route::get('/logistik-aktif/{id}/edit', [LogistikAktifController::class, 'editLogistikAktif'])->name('logistik-aktif.edit');
        Route::put('/logistik-aktif/{id}', [LogistikAktifController::class, 'updateLogistikAktif'])->name('logistik-aktif.update');

        // Rekap Aktual Logistik (khusus LG)
        Route::get('/logistik-printed', [RealisasiLogistikAktifController::class, 'logistikPrinted'])->name('logistik-printed');

        Route::get('/realisasi-logistik/picking-list/{id}', [RealisasiLogistikAktifController::class, 'printPickingList'])->name('logistik-picking-list');
        Route::get('/realisasi-logistik/picking-list-pdf/{id}', [RealisasiLogistikAktifController::class, 'printPickingListPdf'])->name('logistik-picking-list.pdf');
        Route::get('/realisasi-logistik/tanda-terima/{id}', [RealisasiLogistikAktifController::class, 'printTandaTerima'])->name('realisasi-logistik.tanda-terima');

        Route::get('/realisasi-logistik/print-pdf', [RealisasiLogistikAktifController::class, 'printRealisasiPdf'])->name('realisasi-logistik.print-pdf');
        Route::get('/realisasi-logistik/print-pemesanan', [RealisasiLogistikAktifController::class, 'printPemesanan'])->name('realisasi-logistik.print-pemesanan');
        Route::get('/realisasi-logistik/print-qc', [RealisasiLogistikAktifController::class, 'printQC'])->name('realisasi-logistik.print-qc');
        Route::get('/realisasi-logistik/print-packing', [RealisasiLogistikAktifController::class, 'printPacking'])->name('realisasi-logistik.print-packing');
        Route::get('/realisasi-logistik/print-ekspedisi', [RealisasiLogistikAktifController::class, 'printEkspedisi'])->name('realisasi-logistik.print-ekspedisi');

        Route::delete('/realisasi-logistik/{id}', [RealisasiLogistikAktifController::class, 'deleteRealisasi'])->name('realisasi-logistik.delete');

        // =========================
        // LOGISTIK PASIF (SKU LG, khusus Stokis Pasif)
        // =========================
        Route::get('/logistik-pasif', [LogistikPasifController::class, 'logistikPasif'])->name('logistik-pasif');
        Route::post('/logistik-pasif/sync-lg', [LogistikPasifController::class, 'syncLgFromBimbashop'])->name('logistik-pasif.sync-lg');
        Route::post('/logistik-pasif/bulk-action', [LogistikPasifController::class, 'bulkActionLogistikPasif'])->name('logistik-pasif.bulk-action');
        Route::get('/logistik-pasif/filtered-ids', [LogistikPasifController::class, 'getFilteredIds'])->name('logistik-pasif.filtered-ids');
        Route::post('/logistik-pasif/get-modal-data', [LogistikPasifController::class, 'getModalData'])->name('logistik-pasif.get-modal-data');
        Route::get('/logistik-pasif/export', [LogistikPasifController::class, 'exportLogistikPasif'])->name('logistik-pasif.export');
        Route::get('/logistik-pasif/{id}/edit', [LogistikPasifController::class, 'editLogistikPasif'])->name('logistik-pasif.edit');
        Route::put('/logistik-pasif/{id}', [LogistikPasifController::class, 'updateLogistikPasif'])->name('logistik-pasif.update');

        // Rekap Aktual Logistik Pasif
        Route::get('/logistik-pasif-printed', [RealisasiLogistikPasifController::class, 'logistikPasifPrinted'])->name('logistik-pasif-printed');

        Route::get('/realisasi-logistik-pasif/picking-list/{id}', [RealisasiLogistikPasifController::class, 'printPickingList'])->name('logistik-pasif-picking-list');
        Route::get('/realisasi-logistik-pasif/picking-list-pdf/{id}', [RealisasiLogistikPasifController::class, 'printPickingListPdf'])->name('logistik-pasif-picking-list.pdf');
        Route::get('/realisasi-logistik-pasif/tanda-terima/{id}', [RealisasiLogistikPasifController::class, 'printTandaTerima'])->name('realisasi-logistik-pasif.tanda-terima');

        Route::get('/realisasi-logistik-pasif/print-pdf', [RealisasiLogistikPasifController::class, 'printRealisasiPdf'])->name('realisasi-logistik-pasif.print-pdf');
        Route::get('/realisasi-logistik-pasif/print-pemesanan', [RealisasiLogistikPasifController::class, 'printPemesanan'])->name('realisasi-logistik-pasif.print-pemesanan');
        Route::get('/realisasi-logistik-pasif/print-qc', [RealisasiLogistikPasifController::class, 'printQC'])->name('realisasi-logistik-pasif.print-qc');
        Route::get('/realisasi-logistik-pasif/print-packing', [RealisasiLogistikPasifController::class, 'printPacking'])->name('realisasi-logistik-pasif.print-packing');
        Route::get('/realisasi-logistik-pasif/print-ekspedisi', [RealisasiLogistikPasifController::class, 'printEkspedisi'])->name('realisasi-logistik-pasif.print-ekspedisi');

        Route::delete('/realisasi-logistik-pasif/{id}', [RealisasiLogistikPasifController::class, 'deleteRealisasi'])->name('realisasi-logistik-pasif.delete');

        // =========================
        // JAKARTA PASIF
        // =========================
        Route::get('/jakarta-pasif', [JakartaPasifController::class, 'jakartaPasif'])->name('jakarta-pasif');
        Route::post('/jakarta-pasif/import', [JakartaPasifController::class, 'importJakartaPasif'])->name('jakarta-pasif.import');
        Route::post('/jakarta-pasif/sync-jkt', [JakartaPasifController::class, 'syncJktPasifFromBimbashop'])->name('jakarta-pasif.sync-jkt');
        Route::post('/jakarta-pasif/bulk-action', [JakartaPasifController::class, 'bulkActionJakartaPasif'])->name('jakarta-pasif.bulk-action');
        Route::get('/jakarta-pasif/{id}/edit', [JakartaPasifController::class, 'editJakartaPasif'])->name('jakarta-pasif.edit');
        Route::put('/jakarta-pasif/{id}', [JakartaPasifController::class, 'updateJakartaPasif'])->name('jakarta-pasif.update');
        Route::get('/jakarta-pasif/filtered-ids', [JakartaPasifController::class, 'getFilteredIdsPasif'])->name('jakarta-pasif.filtered-ids');
        Route::post('/jakarta-pasif/get-modal-data', [JakartaPasifController::class, 'getModalDataPasif'])->name('jakarta-pasif.get-modal-data');
        Route::get('/jakarta-pasif/export', [JakartaPasifController::class, 'exportJakartaPasif'])->name('jakarta-pasif.export');

        // Picking List Pasif (DALAM group → URL = /order/jakarta-pasif/...)
        Route::get('/jakarta-pasif/picking-list/{id}', [RealisasiPasifController::class, 'printPickingListPasif'])
            ->name('jakarta-pasif.picking-list');
        Route::get('/jakarta-pasif/picking-list-pdf/{id}', [RealisasiPasifController::class, 'printPickingListPdfPasif'])
            ->name('jakarta-pasif.picking-list-pdf');
        Route::post('/jakarta-pasif/mark-picking-printed/{id}', [RealisasiPasifController::class, 'markPickingPrintedPasif'])
            ->name('jakarta-pasif.mark-picking-printed');

        // =========================
        // REALISASI
        // =========================
        Route::get('/jakarta-printed', [RealisasiAktifController::class, 'jakartaPrinted'])->name('jakarta-printed');
        Route::get('/jakarta-pasif-printed', [RealisasiPasifController::class, 'jakartaPasifPrinted'])->name('jakarta-pasif-printed');

        Route::delete('/realisasi/{id}', [RealisasiAktifController::class, 'deleteRealisasi'])->name('realisasi.delete');
        Route::post('/realisasi/mark-printed-all', [RealisasiAktifController::class, 'markAllAsPrinted'])->name('realisasi.mark-printed-all');

        // =========================
        // PRINT REALISASI PASIF
        // =========================
        Route::get('/realisasi-pasif/print-pdf', [RealisasiPasifController::class, 'printRealisasiPasifPdf'])
            ->name('realisasi-pasif.print-pdf');

        Route::get('/realisasi-pasif/print-pemesanan', [RealisasiPasifController::class, 'printPemesananPasif'])
            ->name('realisasi-pasif.print-pemesanan');

        Route::get('/realisasi-pasif/print-qc', [RealisasiPasifController::class, 'printQCPasif'])
            ->name('realisasi-pasif.print-qc');

        Route::get('/realisasi-pasif/print-packing', [RealisasiPasifController::class, 'printPackingPasif'])
            ->name('realisasi-pasif.print-packing');

        Route::get('/realisasi-pasif/print-ekspedisi', [RealisasiPasifController::class, 'printEkspedisiPasif'])
            ->name('realisasi-pasif.print-ekspedisi');

        Route::get('/realisasi-pasif/tanda-terima/{id}', [RealisasiPasifController::class, 'printTandaTerima'])
            ->name('realisasi-pasif.tanda-terima');

        // =========================
        // PRINT (AKTIF)
        // =========================
        Route::get('/realisasi/print-pdf', [RealisasiAktifController::class, 'printRealisasiPdf'])->name('realisasi.print-pdf');
        Route::get('/realisasi/print-pdf/{id}', [RealisasiAktifController::class, 'printSingleRealisasi'])->name('realisasi.print-single');
        Route::get('/realisasi/picking-list/{id}', [RealisasiAktifController::class, 'printPickingList'])->name('realisasi.picking-list');
        Route::get('/picking-list/pdf/{id}', [RealisasiAktifController::class, 'printPickingListPdf'])->name('picking-list.pdf');
        Route::get('/realisasi/print-qc', [RealisasiAktifController::class, 'printQC'])->name('realisasi.print-qc');
        Route::get('/realisasi/print-pemesanan', [RealisasiAktifController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
        Route::get('/realisasi/print-ekspedisi', [RealisasiAktifController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');
        Route::get('/realisasi/print-packing', [RealisasiAktifController::class, 'printPacking'])->name('realisasi.print-packing');
        Route::get('/realisasi/picking-list-all', [RealisasiAktifController::class, 'printPickingListAll'])->name('print-picking-list-all');
        Route::get('/realisasi/print-qc-all', [RealisasiAktifController::class, 'printQCAll'])->name('print-qc-all');
        Route::get('/realisasi/print-packing-all', [RealisasiAktifController::class, 'printPackingAll'])->name('print-packing-all');
        Route::get('/realisasi/print-ekspedisi-all', [RealisasiAktifController::class, 'printEkspedisiAll'])->name('print-ekspedisi-all');
        Route::get('/realisasi/print-ra-all', [RealisasiAktifController::class, 'printRealisasiAll'])->name('print-ra-all');
        Route::get('/realisasi/tanda-terima/{id}', [RealisasiAktifController::class, 'printTandaTerima'])
    ->name('realisasi.tanda-terima');
    });

// Di luar group (path penuh)
Route::post('order/jakarta-aktif/sync-manual', [JakartaAktifController::class, 'syncManualToJakartaAktif'])
    ->name('order.jakarta-aktif.sync-manual')
    ->middleware('auth');

Route::post('order/jakarta-aktif/sync-pesanan-majalah', [JakartaAktifController::class, 'syncPesananMajalahToJakartaAktif'])
    ->name('order.jakarta-aktif.sync-pesanan-majalah')
    ->middleware('auth');

//menu baru
Route::get('/order/jakarta-aktif/menu', function () {
    return view('order::order.jakarta-aktif-menu');
})->name('order.jakarta-aktif.menu');

Route::get('/order/jakarta-aktif/realisasi', function () {
    return view('order::order.jakarta-aktif-realisasi');
})->name('order.jakarta-aktif.realisasi');

Route::get('/order/jakarta-aktif/realisasi/modul', [JakartaAktifController::class, 'jakartaAktifModul'])
    ->name('order.jakarta-aktif.modul');

    Route::get('/order/jakarta-aktif/realisasi/majalah', function () {
    return view('order::order.jakarta-aktif-majalah-menu');
})->name('order.jakarta-aktif.majalah');

Route::view(
    '/order/jakarta-aktif/realisasi/modul',
    'order::order.jakarta-aktif-modul-menu'
)->name('order.jakarta-aktif.modul');
