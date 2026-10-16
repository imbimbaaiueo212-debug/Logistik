<?php

// ====================== QC OUTGOING ======================
// Di-load oleh QcServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Qc\Http\Controllers\QcOutgoingController;

// ====================== QC OUTGOING ======================
Route::prefix('qc-outgoing')
    ->name('qc-outgoing.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'index'])
            ->name('index');

        Route::get('/jakarta-aktif', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'jakartaAktif'])
            ->name('jakarta-aktif');
        
        Route::get('/jakarta-pasif', [QcOutgoingController::class, 'jakartaPasif'])->name('jakarta-pasif');

        Route::post('/pasif/store', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'storePasif'])
            ->name('pasif.store');

        Route::post('/store', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'qcStore'])
            ->name('store');

        // Order Manual
        Route::get('/order-manual', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'orderManual'])
            ->name('order-manual');

        Route::post('/manual/store', [App\Modules\Qc\Http\Controllers\QcOutgoingController::class, 'storeManual'])
            ->name('manual.store');

    });
