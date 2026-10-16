<?php

// ====================== IMPORT (BimbaShop, Casdana, Manual, DLC, Pasif, Report Angka Cetak) ======================
// Di-load oleh ImportServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Import\Http\Controllers\BimbashopImportController;
use App\Modules\Import\Http\Controllers\CasdanaImportController;
use App\Modules\Import\Http\Controllers\DlcController;
use App\Modules\Import\Http\Controllers\ImportMenuController;
use App\Modules\Import\Http\Controllers\ManualOrderController;
use App\Modules\Import\Http\Controllers\ManualRealisasiController;
use App\Modules\Import\Http\Controllers\ManualSyncController;
use App\Modules\Import\Http\Controllers\PasifBacaanController;
use App\Modules\Import\Http\Controllers\PasifController;
use App\Modules\Import\Http\Controllers\PasifManualController;
use App\Modules\Import\Http\Controllers\ReportAngkaCetakController;

// ====================== ORDER MANUAL ===============
Route::prefix('import')
    ->name('import.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [ImportMenuController::class, 'index'])->name('index');

        // Bimbashop
        Route::get('/bimbashop', [BimbashopImportController::class, 'bimbashop'])->name('bimbashop');
        Route::post('/bimbashop', [BimbashopImportController::class, 'bimbashopStore'])->name('bimbashop.store');
        Route::get('/bimbashop/{id}/edit', [BimbashopImportController::class, 'bimbashopEdit'])->name('bimbashop.edit');
        Route::put('/bimbashop/{id}', [BimbashopImportController::class, 'bimbashopUpdate'])->name('bimbashop.update');
        Route::delete('/bimbashop/{id}', [BimbashopImportController::class, 'bimbashopDestroy'])->name('bimbashop.destroy');

        // Casdana
        Route::get('/casdana', [CasdanaImportController::class, 'casdana'])->name('casdana');
        Route::post('/casdana', [CasdanaImportController::class, 'casdanaStore'])->name('casdana.store');
        Route::get('/casdana/{id}/edit', [CasdanaImportController::class, 'casdanaEdit'])->name('casdana.edit');
        Route::put('/casdana/{id}', [CasdanaImportController::class, 'casdanaUpdate'])->name('casdana.update');
        Route::delete('/casdana/{id}', [CasdanaImportController::class, 'casdanaDestroy'])->name('casdana.destroy');

        // =====================================================
        // MANUAL
        // =====================================================
        Route::get('/manual', [ManualOrderController::class, 'manual'])->name('manual');
        Route::post('/manual', [ManualOrderController::class, 'manualImport'])->name('manual.store');
        Route::get('/manual/create', [ManualOrderController::class, 'manualCreate'])->name('manual.create');
        Route::post('/manual/store', [ManualOrderController::class, 'manualStore'])->name('manual.store.single');
        Route::get('/manual/{id}/edit', [ManualOrderController::class, 'manualEdit'])->name('manual.edit');
        Route::put('/manual/{id}', [ManualOrderController::class, 'manualUpdate'])->name('manual.update');
        Route::delete('/manual/{id}', [ManualOrderController::class, 'manualDestroy'])->name('manual.destroy');

        // Bulk proses Manual
        Route::get('/manual/filtered-ids', [ManualOrderController::class, 'getManualFilteredIds'])
            ->name('manual.filtered-ids');

        Route::post('/manual/get-modal-data', [ManualOrderController::class, 'getManualModalData'])
            ->name('manual.get-modal-data');

        Route::post('/manual/bulk-action', [ManualOrderController::class, 'bulkActionManual'])
            ->name('manual.bulk-action');

        // Sync Pesanan Majalah → Manual
        Route::post('/sync-pesanan-majalah', [ManualSyncController::class, 'syncPesananMajalahToJakartaAktif'])
            ->name('sync-pesanan-majalah');

        // =====================================================
        // DLC
        // =====================================================
        Route::prefix('dlc')->name('dlc.')->group(function () {
            Route::get('/', [DlcController::class, 'dlcIndex'])->name('index');
            Route::get('/create', [DlcController::class, 'dlcCreate'])->name('create');
            Route::post('/store', [DlcController::class, 'dlcStore'])->name('store');
            Route::get('/{id}/edit', [DlcController::class, 'dlcEdit'])->name('edit');
            Route::put('/{id}', [DlcController::class, 'dlcUpdate'])->name('update');
            Route::put('/{id}/no-ps', [DlcController::class, 'dlcUpdateNoPs'])->name('update-no-ps');
            Route::get('/{id}', [DlcController::class, 'dlcShow'])->name('show');
            Route::delete('/{id}', [DlcController::class, 'dlcDestroy'])->name('destroy');
            Route::put('/pesanan/{id}', [DlcController::class, 'dlcUpdateQty'])->name('update-qty');
        });

        // =====================================================
        // GROUP PASIF
        // =====================================================
        Route::prefix('pasif')->name('pasif.')->group(function () {

            Route::get('/', [PasifController::class, 'pasifMenu'])->name('index');

            Route::put('/{id}/no-ps', [PasifController::class, 'pasifUpdateNoPs'])->name('update-no-ps');

            // SPARE PASIF 3%
            Route::get('/spare', [PasifController::class, 'sparePasif'])->name('spare');
            Route::get('/spare/{edisi}', [PasifController::class, 'sparePasifShow'])->name('spare.show');
            Route::put('/spare/{id}/no-ps', [PasifController::class, 'sparePasifUpdateNoPs'])->name('spare.update-no-ps');

            // Bacaan Unit
            Route::get('/bacaan', [PasifBacaanController::class, 'pasifBacaan'])->name('bacaan');
            Route::get('/bacaan/create', [PasifBacaanController::class, 'pasifBacaanCreate'])->name('bacaan.create');
            Route::post('/bacaan/store', [PasifBacaanController::class, 'pasifBacaanStore'])->name('bacaan.store');
            Route::get('/bacaan/{id}', [PasifBacaanController::class, 'pasifBacaanShow'])->name('bacaan.show');
            Route::put('/bacaan/{id}/no-ps', [PasifBacaanController::class, 'pasifBacaanUpdateNoPs'])->name('bacaan.update-no-ps');
            Route::delete('/bacaan/{id}', [PasifBacaanController::class, 'pasifBacaanDestroy'])->name('bacaan.destroy');

            // Rekap Total
            Route::get('/rekap', [PasifController::class, 'pasifRekap'])->name('rekap');
            Route::get('/rekap/{id}', [PasifController::class, 'pasifRekapShow'])->name('rekap.show');

            // Unit Pasif (list)
            Route::get('/list', [PasifController::class, 'pasifIndex'])->name('list');
            Route::get('/create', [PasifController::class, 'pasifCreate'])->name('create');
            Route::post('/store', [PasifController::class, 'pasifStore'])->name('store');
            Route::post('/sync-to-manual', [PasifController::class, 'syncPasifToManual'])->name('sync');

            // PASIF MANUAL
            Route::get('/manual', [PasifManualController::class, 'pasifManualIndex'])->name('manual.index');
            Route::get('/manual/create', [PasifManualController::class, 'pasifManualCreate'])->name('manual.create');
            Route::post('/manual', [PasifManualController::class, 'pasifManualStore'])->name('manual.store');
            Route::get('/manual/{id}/edit', [PasifManualController::class, 'pasifManualEdit'])->name('manual.edit');
            Route::put('/manual/{id}', [PasifManualController::class, 'pasifManualUpdate'])->name('manual.update');
            Route::get('/manual/{id}', [PasifManualController::class, 'pasifManualShow'])->name('manual.show');
            Route::delete('/manual/{id}', [PasifManualController::class, 'pasifManualDestroy'])->name('manual.destroy');

            // Detail Unit Pasif (paling bawah)
            Route::get('/{id}', [PasifController::class, 'pasifShow'])->name('show');
            Route::delete('/{id}', [PasifController::class, 'pasifDestroy'])->name('destroy');
        });

        // =====================================================
        // REPORT ANGKA CETAK
        // =====================================================
        Route::get('/report-angka-cetak', [ReportAngkaCetakController::class, 'reportAngkaCetak'])
            ->name('report-angka-cetak');

        // =====================================================
        // MANUAL PRINTED
        // =====================================================
        Route::get('/manual-printed', [ManualRealisasiController::class, 'manualPrinted'])
            ->name('manual-printed');

        Route::get('/manual-printed/print-pdf', [ManualRealisasiController::class, 'printManualRealisasiPdf'])
            ->name('manual-printed.pdf');

        Route::get('/manual-printed/picking/{id}', [ManualRealisasiController::class, 'printManualPickingList'])
            ->name('manual-printed.picking');

        Route::get('/manual-printed/picking-pdf/{id}', [ManualRealisasiController::class, 'printManualPickingListPdf'])
            ->name('manual-printed.picking-pdf');

        Route::delete('/manual-printed/{id}', [ManualRealisasiController::class, 'deleteManualRealisasi'])
            ->name('manual-printed.destroy');

        // Update catatan
        Route::post('/manual-printed/{id}/catatan', [ManualRealisasiController::class, 'updateManualCatatan'])
            ->name('manual-printed.update-catatan');

        // Cetak lanjutan
        Route::get('/manual-printed/pemesanan', [ManualRealisasiController::class, 'printManualPemesanan'])
            ->name('manual-print-pemesanan');

        Route::get('/manual-printed/qc', [ManualRealisasiController::class, 'printManualQC'])
            ->name('manual-print-qc');

        Route::get('/manual-printed/packing', [ManualRealisasiController::class, 'printManualPacking'])
            ->name('manual-print-packing');

        Route::get('/manual-printed/ekspedisi', [ManualRealisasiController::class, 'printManualEkspedisi'])
            ->name('manual-print-ekspedisi');

        Route::post('/manual/sync-no-ps', [ManualOrderController::class, 'syncNoPsManualExisting'])
            ->name('manual.sync-no-ps');
    });

//ORDER MANUAL
Route::get('/import/manual', [ManualOrderController::class, 'manual'])
    ->name('import.manual');

Route::get('/import/manual/create', [ManualOrderController::class, 'manualCreate'])
    ->name('import.manual.create');
Route::post('/import/manual', [ManualOrderController::class, 'manualImport'])->name('import.manual.store');

Route::post('/import/manual/store', [ManualOrderController::class, 'manualStore'])
    ->name('import.manual.store');

Route::get('/import/manual/{id}/edit', [ManualOrderController::class, 'manualEdit'])
    ->name('import.manual.edit');

Route::put('/import/manual/{id}', [ManualOrderController::class, 'manualUpdate'])
    ->name('import.manual.update');

Route::delete('/import/manual/{id}', [ManualOrderController::class, 'manualDestroy'])
    ->name('import.manual.destroy');
