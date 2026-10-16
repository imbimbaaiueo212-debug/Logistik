<?php

// ====================== KEUANGAN ======================
// Di-load oleh KeuanganServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Keuangan\Http\Controllers\PengeluaranController;

Route::get('/pengeluaran', [PengeluaranController::class, 'index'])
    ->middleware('auth')
    ->name('pengeluaran.index');
