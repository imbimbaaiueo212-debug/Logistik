<?php

// ====================== PACKING ======================
// Di-load oleh PackingServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Packing\Http\Controllers\PackingController;
use App\Modules\Packing\Http\Controllers\PackingManualController;
use App\Modules\Packing\Http\Controllers\PackingPasifController;

    // ====================== PACKING ======================
Route::prefix('packing')->group(function () {

    Route::get('/', [PackingController::class, 'index'])
        ->name('packing.index');

    Route::get('/jakarta-aktif', [PackingController::class, 'jakartaAktif'])
        ->name('packing.jakarta.aktif');

    Route::get('/jakarta-pasif', [PackingPasifController::class, 'jakartaPasif'])
        ->name('packing.jakarta-pasif');

    // ← PASTIKAN BARIS INI ADA
    Route::put('/pasif/{id}', [PackingPasifController::class, 'updatePasif'])
        ->name('packing.pasif.update');

    Route::put('/{id}', [PackingController::class, 'update'])
        ->name('packing.update');

    Route::post('/store', [PackingController::class, 'store'])
        ->name('packing.store');

    // ===== ORDER MANUAL =====
    Route::get('/order-manual', [PackingManualController::class, 'orderManual'])
        ->name('packing.order-manual');

    Route::put('/order-manual/{id}', [PackingManualController::class, 'updateManual'])
        ->name('packing.order-manual.update');
});
