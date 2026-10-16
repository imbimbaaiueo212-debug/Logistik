<?php

// ====================== DISTRIBUSI (distribution, return, distribution order) ======================
// Di-load oleh DistribusiServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Distribusi\Http\Controllers\DistributionController;
use App\Modules\Distribusi\Http\Controllers\DistributionOrderController;
use App\Modules\Distribusi\Http\Controllers\ReturnDistributionController;

Route::resource('distribution', DistributionController::class);
Route::post('/distribution/{id}/approve', [DistributionController::class, 'approve'])->name('distribution.approve');
Route::post('/distribution/{id}/reject', [DistributionController::class, 'reject'])->name('distribution.reject');

Route::resource('return-distribution', ReturnDistributionController::class);
Route::get('/return-distribution-items/{id}', [ReturnDistributionController::class, 'getItems']);
Route::post('/return-distribution/{id}/approve', [ReturnDistributionController::class, 'approve'])->name('return-distribution.approve');

// Distribution Order Routes
Route::prefix('distribution-order')
    ->name('distribution-order.')
    ->group(function () {

        Route::get('/', [DistributionOrderController::class, 'index'])->name('index');

        Route::get('/jakarta-aktif', [DistributionOrderController::class, 'jakartaAktif'])->name('jakarta-aktif');
        Route::get('/jakarta-pasif', [DistributionOrderController::class, 'jakartaPasif'])->name('jakarta-pasif');
        Route::get('/intervio', [DistributionOrderController::class, 'intervio'])->name('intervio');
        Route::get('/ebt', [DistributionOrderController::class, 'ebt'])->name('ebt');

        // === MANUAL ===
        Route::get('/manual', [DistributionOrderController::class, 'manual'])->name('manual');
        Route::post('/manual/{id}/update', [DistributionOrderController::class, 'updateManual'])->name('manual.update');

        // === PASIF UPDATE (letakkan SEBELUM route {distributionOrder}) ===
        Route::put('/pasif/{id}', [DistributionOrderController::class, 'updatePasif'])
            ->name('pasif.update');

        // === ROUTE LAIN ===
        Route::get('/create', [DistributionOrderController::class, 'create'])->name('create');
        Route::post('/', [DistributionOrderController::class, 'store'])->name('store');

        Route::get('/{distributionOrder}', [DistributionOrderController::class, 'show'])->name('show');
        Route::get('/{distributionOrder}/edit', [DistributionOrderController::class, 'edit'])->name('edit');
        Route::put('/{distributionOrder}', [DistributionOrderController::class, 'update'])->name('update');
        Route::delete('/{distributionOrder}', [DistributionOrderController::class, 'destroy'])->name('destroy');
    });
