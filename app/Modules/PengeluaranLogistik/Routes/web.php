<?php

// ====================== REPORT PENGELUARAN LOGISTIK ======================
// Di-load oleh PengeluaranLogistikServiceProvider (grup middleware "web").
// URL: /pengeluaran-logistik/...   Nama route: pengeluaran-logistik.*

use App\Modules\PengeluaranLogistik\Http\Controllers\PengeluaranLogistikController;
use App\Modules\PengeluaranLogistik\Http\Controllers\TransferGudangManualController;
use Illuminate\Support\Facades\Route;

Route::prefix('pengeluaran-logistik')
    ->name('pengeluaran-logistik.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [PengeluaranLogistikController::class, 'index'])->name('index');

        // Transfer gudang (input manual, hanya qty, tanpa nominal)
        Route::get('/transfer', [TransferGudangManualController::class, 'index'])->name('transfer.index');
        Route::post('/transfer', [TransferGudangManualController::class, 'store'])->name('transfer.store');
        Route::delete('/transfer/{id}', [TransferGudangManualController::class, 'destroy'])->whereNumber('id')->name('transfer.destroy');
    });
