<?php

// ====================== MAJALAH (2026, Korwil, Kotamadya, PUW1) ======================
// Di-load oleh MajalahServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Majalah\Http\Controllers\Majalah2026Controller;
use App\Modules\Majalah\Http\Controllers\PesananMajalahController;
use App\Modules\Majalah\Http\Controllers\PesananMajalahKotamadyaController;
use App\Modules\Majalah\Http\Controllers\PesananMajalahPuw1Controller;

Route::post('pesanan-majalah/{pesananMajalah}/kirim-ke-jakarta-aktif', [PesananMajalahController::class, 'kirimKeJakartaAktif'])
    ->name('pesanan-majalah.kirim-ke-jakarta-aktif')
    ->middleware('auth');

    // ====================== MAJALAH 2026 ======================
    Route::get('/realisasi/majalah/2026', [Majalah2026Controller::class, 'index'])
    ->name('majalah.2026');
    Route::get('/realisasi/majalah/2026/{edisi}', [Majalah2026Controller::class, 'show'])
    ->name('majalah.2026.show');
    Route::get('/realisasi/majalah/2026/{edisi}/diproses', [Majalah2026Controller::class, 'diproses'])
    ->name('majalah.2026.diproses');

    Route::get('/realisasi/majalah/2026/{edisi}/batal', [Majalah2026Controller::class, 'batal'])
        ->name('majalah.2026.batal');
        Route::get(
    '/realisasi/majalah/2026/{edisi}/diproses/{kategori}',
    [Majalah2026Controller::class, 'kategori']
        )->name('majalah.2026.kategori');

// ======================= MAJALAH KORWIL =======================

Route::resource(
    'pesanan-majalah',
    PesananMajalahController::class
);

Route::post(
    '/pesanan-majalah/import',
    [PesananMajalahController::class, 'import']
)->name('pesanan-majalah.import');

Route::post('pesanan-majalah/{pesananMajalah}/kirim-ke-manual', 
    [PesananMajalahController::class, 'kirimKeManual']
)->name('pesanan-majalah.kirim-ke-manual');
Route::patch('/pesanan-majalah/{id}/update-no-ps', [\App\Modules\Majalah\Http\Controllers\PesananMajalahController::class, 'updateNoPs'])
    ->name('pesanan-majalah.update-no-ps');

// ====================== MAJALAH PINWIL =========================
/*
|--------------------------------------------------------------------------
| Pesanan Majalah Kotamadya
|--------------------------------------------------------------------------
*/

Route::prefix('pesanan-majalah-kotamadya')
    ->name('pesanan-majalah-kotamadya.')
    ->group(function () {

        // =========================================================
        // DAFTAR KOTAMADYA / INDEX
        // =========================================================
        Route::get(
            '/',
            [PesananMajalahKotamadyaController::class, 'index']
        )->name('index');


        // =========================================================
        // FORM TAMBAH KOTAMADYA
        // =========================================================
        Route::get(
            '/create',
            [PesananMajalahKotamadyaController::class, 'create']
        )->name('create');


        // =========================================================
        // SIMPAN KOTAMADYA
        // =========================================================
        Route::post(
            '/',
            [PesananMajalahKotamadyaController::class, 'store']
        )->name('store');


        // =========================================================
        // IMPORT EXCEL
        // =========================================================
        Route::post(
            '/import',
            [PesananMajalahKotamadyaController::class, 'import']
        )->name('import');


        // =========================================================
        // HAPUS SELURUH PERIODE
        // =========================================================
        Route::delete(
            '/periode/{id}',
            [PesananMajalahKotamadyaController::class, 'destroyPeriode']
        )->name('destroy-periode');


        // =========================================================
        // DETAIL KOTAMADYA + DAFTAR UNIT
        // =========================================================
        Route::get(
            '/{id}',
            [PesananMajalahKotamadyaController::class, 'show']
        )->name('show');


        // =========================================================
        // FORM EDIT KOTAMADYA
        // =========================================================
        Route::get(
            '/{id}/edit',
            [PesananMajalahKotamadyaController::class, 'edit']
        )->name('edit');


        // =========================================================
        // UPDATE KOTAMADYA
        // =========================================================
        Route::put(
            '/{id}',
            [PesananMajalahKotamadyaController::class, 'update']
        )->name('update');


        // =========================================================
        // HAPUS KOTAMADYA
        // =========================================================
        Route::delete(
            '/{id}',
            [PesananMajalahKotamadyaController::class, 'destroy']
        )->name('destroy');


        // =========================================================
        // UPDATE NO. PS
        // =========================================================
        Route::post(
            '/{id}/update-no-ps',
            [PesananMajalahKotamadyaController::class, 'updateNoPs']
        )->name('update-no-ps');


        /*
        |--------------------------------------------------------------------------
        | UNIT KOTAMADYA
        |--------------------------------------------------------------------------
        */

        // =========================================================
        // TAMBAH UNIT KE KOTAMADYA
        // =========================================================
        Route::post(
            '/{kotamadyaId}/unit',
            [PesananMajalahKotamadyaController::class, 'storeUnit']
        )->name('unit.store');


        // =========================================================
        // UPDATE UNIT
        // =========================================================
        Route::put(
            '/unit/{unitId}',
            [PesananMajalahKotamadyaController::class, 'updateUnit']
        )->name('unit.update');


        // =========================================================
        // HAPUS UNIT
        // =========================================================
        Route::delete(
            '/unit/{unitId}',
            [PesananMajalahKotamadyaController::class, 'destroyUnit']
        )->name('unit.destroy');

    });

  /*
|--------------------------------------------------------------------------
| Pesanan Majalah PUW1
|--------------------------------------------------------------------------
*/

Route::prefix('pesanan-majalah-puw1')
    ->name('pesanan-majalah-puw1.')
    ->group(function () {

        // Index
        Route::get(
            '/',
            [PesananMajalahPuw1Controller::class, 'index']
        )->name('index');


        // Import Excel
        Route::post(
            '/import',
            [PesananMajalahPuw1Controller::class, 'import']
        )->name('import');


        // Create
        Route::get(
            '/create',
            [PesananMajalahPuw1Controller::class, 'create']
        )->name('create');


        // Store
        Route::post(
            '/',
            [PesananMajalahPuw1Controller::class, 'store']
        )->name('store');


        // Show
        Route::get(
            '/{id}',
            [PesananMajalahPuw1Controller::class, 'show']
        )->name('show');


        // Edit
        Route::get(
            '/{id}/edit',
            [PesananMajalahPuw1Controller::class, 'edit']
        )->name('edit');


        // Update
        Route::put(
            '/{id}',
            [PesananMajalahPuw1Controller::class, 'update']
        )->name('update');


        // Destroy
        Route::delete(
            '/{id}',
            [PesananMajalahPuw1Controller::class, 'destroy']
        )->name('destroy');


        // Update No. PS
        Route::post(
            '/{id}/update-no-ps',
            [PesananMajalahPuw1Controller::class, 'updateNoPs']
        )->name('update-no-ps');

    });
