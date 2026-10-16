<?php

// ====================== ORDER MANUAL (umum, modul, sertifikat) ======================
// Di-load oleh OrderManualServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\OrderManual\Http\Controllers\Modul\ManualModulOrderController;
use App\Modules\OrderManual\Http\Controllers\Modul\ManualModulRealisasiController;
use App\Modules\OrderManual\Http\Controllers\Modul\ManualModulSyncController;
use App\Modules\OrderManual\Http\Controllers\OrderManualController;
use App\Modules\OrderManual\Http\Controllers\Sertifikat\ManualSertifikatOrderController;
use App\Modules\OrderManual\Http\Controllers\Sertifikat\ManualSertifikatRealisasiController;

    // DATA ORDER MANUAL
    Route::get('/order-manual', [OrderManualController::class, 'index'])
    ->name('order-manual.index')
    ->middleware('auth');

Route::prefix('order-manual-modul')
    ->name('order-manual-modul.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [ManualModulOrderController::class, 'index'])->name('index');

        Route::get('/manual', [ManualModulOrderController::class, 'manual'])->name('manual');
        Route::get('/manual/create', [ManualModulOrderController::class, 'manualCreate'])->name('manual.create');
        Route::post('/manual', [ManualModulOrderController::class, 'manualStore'])->name('manual.store');
        Route::get('/manual/{id}/edit', [ManualModulOrderController::class, 'manualEdit'])->name('manual.edit');
        Route::put('/manual/{id}', [ManualModulOrderController::class, 'manualUpdate'])->name('manual.update');

        Route::post('/manual/sync-bimbashop-casdana', [ManualModulSyncController::class, 'runSyncModul'])
            ->name('manual.sync');

        Route::get('/manual/filtered-ids', [ManualModulOrderController::class, 'getFilteredIds'])->name('manual.filtered-ids');
        Route::post('/manual/get-modal-data', [ManualModulOrderController::class, 'getModalData'])->name('manual.get-modal-data');
        Route::post('/manual/bulk-action', [ManualModulOrderController::class, 'bulkAction'])->name('manual.bulk-action');

        Route::get('/manual-printed', [ManualModulRealisasiController::class, 'printed'])->name('manual-printed');
        Route::get('/search-products', [ManualModulOrderController::class, 'searchProducts'])->name('search-products');

        // ✅ BENAR
        Route::get('/realisasi', [ManualModulRealisasiController::class, 'realisasi'])->name('realisasi');
        Route::get('/realisasi', [ManualModulRealisasiController::class, 'realisasi'])->name('realisasi');
        Route::get('/realisasi/picking-list/{id}', [ManualModulRealisasiController::class, 'printPickingList'])->name('realisasi.picking-list');
        Route::get('/realisasi/print-prising', [ManualModulRealisasiController::class, 'printPrising'])->name('realisasi.print-prising');
        Route::get('/realisasi/print-pemesanan', [ManualModulRealisasiController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
        Route::get('/realisasi/print-qc', [ManualModulRealisasiController::class, 'printQc'])->name('realisasi.print-qc');
        Route::get('/realisasi/print-packing', [ManualModulRealisasiController::class, 'printPacking'])->name('realisasi.print-packing');
        Route::get('/realisasi/print-ekspedisi', [ManualModulRealisasiController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');
    });

        Route::prefix('order-manual-sertifikat')->name('order-manual-sertifikat.')->group(function () {
            Route::get('/', [ManualSertifikatOrderController::class, 'index'])->name('index');

            // List + CRUD
            Route::get('/manual', [ManualSertifikatOrderController::class, 'manual'])->name('manual');
            Route::get('/manual/create', [ManualSertifikatOrderController::class, 'manualCreate'])->name('manual.create');
            Route::post('/manual', [ManualSertifikatOrderController::class, 'manualStore'])->name('manual.store');
            Route::get('/manual/{id}/edit', [ManualSertifikatOrderController::class, 'manualEdit'])->name('manual.edit');
            Route::put('/manual/{id}', [ManualSertifikatOrderController::class, 'manualUpdate'])->name('manual.update');

            // Bulk & modal
            Route::get('/manual/filtered-ids', [ManualSertifikatOrderController::class, 'getFilteredIds'])->name('manual.filtered-ids');
            Route::post('/manual/get-modal-data', [ManualSertifikatOrderController::class, 'getModalData'])->name('manual.get-modal-data');
            Route::post('/manual/bulk-action', [ManualSertifikatOrderController::class, 'bulkAction'])->name('manual.bulk-action');

            // Sync & search
            Route::post('/manual/sync', [ManualSertifikatOrderController::class, 'runSync'])->name('manual.sync');
            Route::get('/search-products', [ManualSertifikatOrderController::class, 'searchProducts'])->name('search-products');

            // Realisasi
            Route::get('/realisasi', [ManualSertifikatRealisasiController::class, 'realisasi'])->name('realisasi');

            // Print routes
            Route::get('/realisasi/print-prising', [ManualSertifikatRealisasiController::class, 'printPrising'])->name('realisasi.print-prising');
            Route::get('/realisasi/print-pemesanan', [ManualSertifikatRealisasiController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
            Route::get('/realisasi/print-qc', [ManualSertifikatRealisasiController::class, 'printQc'])->name('realisasi.print-qc');
            Route::get('/realisasi/print-packing', [ManualSertifikatRealisasiController::class, 'printPacking'])->name('realisasi.print-packing');
            Route::get('/realisasi/print-ekspedisi', [ManualSertifikatRealisasiController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');

            // Picking list (dipakai di confirmPrintPicking)
            Route::get('/realisasi/picking-list/{id}', [ManualSertifikatRealisasiController::class, 'pickingList'])->name('realisasi.picking-list');
        });
