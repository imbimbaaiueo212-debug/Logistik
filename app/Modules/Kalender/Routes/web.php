<?php

// ====================== KALENDER (Korwil, Pinwil/Kotamadya, PUW1, DLC, Unit Pasif) ======================
// Di-load oleh KalenderServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Kalender\Http\Controllers\PesananKalenderController;
use App\Modules\Kalender\Http\Controllers\PesananKalenderKotamadyaController;
use App\Modules\Kalender\Http\Controllers\PesananKalenderPuw1Controller;
use App\Modules\Kalender\Http\Controllers\KalenderDlcController;
use App\Modules\Kalender\Http\Controllers\KalenderPasifController;
use App\Modules\Kalender\Http\Controllers\KalenderPasifBacaanController;
use App\Modules\Kalender\Http\Controllers\KalenderPasifManualController;
use App\Modules\Kalender\Http\Controllers\KalenderReportAngkaCetakController;

Route::post('pesanan-kalender/{pesananKalender}/kirim-ke-jakarta-aktif', [PesananKalenderController::class, 'kirimKeJakartaAktif'])
    ->name('pesanan-kalender.kirim-ke-jakarta-aktif')
    ->middleware('auth');

// ======================= KALENDER KORWIL =======================

Route::resource(
    'pesanan-kalender',
    PesananKalenderController::class
);

Route::post(
    '/pesanan-kalender/import',
    [PesananKalenderController::class, 'import']
)->name('pesanan-kalender.import');

Route::patch('/pesanan-kalender/{id}/update-no-ps', [\App\Modules\Kalender\Http\Controllers\PesananKalenderController::class, 'updateNoPs'])
    ->name('pesanan-kalender.update-no-ps');

// ====================== KALENDER PINWIL =========================
/*
|--------------------------------------------------------------------------
| Pesanan Kalender Kotamadya
|--------------------------------------------------------------------------
*/

Route::prefix('pesanan-kalender-kotamadya')
    ->name('pesanan-kalender-kotamadya.')
    ->group(function () {

        // =========================================================
        // DAFTAR KOTAMADYA / INDEX
        // =========================================================
        Route::get(
            '/',
            [PesananKalenderKotamadyaController::class, 'index']
        )->name('index');


        // =========================================================
        // FORM TAMBAH KOTAMADYA
        // =========================================================
        Route::get(
            '/create',
            [PesananKalenderKotamadyaController::class, 'create']
        )->name('create');


        // =========================================================
        // SIMPAN KOTAMADYA
        // =========================================================
        Route::post(
            '/',
            [PesananKalenderKotamadyaController::class, 'store']
        )->name('store');


        // =========================================================
        // IMPORT EXCEL
        // =========================================================
        Route::post(
            '/import',
            [PesananKalenderKotamadyaController::class, 'import']
        )->name('import');


        // =========================================================
        // HAPUS SELURUH PERIODE
        // =========================================================
        Route::delete(
            '/periode/{id}',
            [PesananKalenderKotamadyaController::class, 'destroyPeriode']
        )->name('destroy-periode');


        // =========================================================
        // DETAIL KOTAMADYA + DAFTAR UNIT
        // =========================================================
        Route::get(
            '/{id}',
            [PesananKalenderKotamadyaController::class, 'show']
        )->name('show');


        // =========================================================
        // FORM EDIT KOTAMADYA
        // =========================================================
        Route::get(
            '/{id}/edit',
            [PesananKalenderKotamadyaController::class, 'edit']
        )->name('edit');


        // =========================================================
        // UPDATE KOTAMADYA
        // =========================================================
        Route::put(
            '/{id}',
            [PesananKalenderKotamadyaController::class, 'update']
        )->name('update');


        // =========================================================
        // HAPUS KOTAMADYA
        // =========================================================
        Route::delete(
            '/{id}',
            [PesananKalenderKotamadyaController::class, 'destroy']
        )->name('destroy');


        // =========================================================
        // UPDATE NO. PS
        // =========================================================
        Route::post(
            '/{id}/update-no-ps',
            [PesananKalenderKotamadyaController::class, 'updateNoPs']
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
            [PesananKalenderKotamadyaController::class, 'storeUnit']
        )->name('unit.store');


        // =========================================================
        // UPDATE UNIT
        // =========================================================
        Route::put(
            '/unit/{unitId}',
            [PesananKalenderKotamadyaController::class, 'updateUnit']
        )->name('unit.update');


        // =========================================================
        // HAPUS UNIT
        // =========================================================
        Route::delete(
            '/unit/{unitId}',
            [PesananKalenderKotamadyaController::class, 'destroyUnit']
        )->name('unit.destroy');

    });

  /*
|--------------------------------------------------------------------------
| Pesanan Kalender PUW1
|--------------------------------------------------------------------------
*/

Route::prefix('pesanan-kalender-puw1')
    ->name('pesanan-kalender-puw1.')
    ->group(function () {

        // Index
        Route::get(
            '/',
            [PesananKalenderPuw1Controller::class, 'index']
        )->name('index');


        // Import Excel
        Route::post(
            '/import',
            [PesananKalenderPuw1Controller::class, 'import']
        )->name('import');


        // Create
        Route::get(
            '/create',
            [PesananKalenderPuw1Controller::class, 'create']
        )->name('create');


        // Store
        Route::post(
            '/',
            [PesananKalenderPuw1Controller::class, 'store']
        )->name('store');


        // Show
        Route::get(
            '/{id}',
            [PesananKalenderPuw1Controller::class, 'show']
        )->name('show');


        // Edit
        Route::get(
            '/{id}/edit',
            [PesananKalenderPuw1Controller::class, 'edit']
        )->name('edit');


        // Update
        Route::put(
            '/{id}',
            [PesananKalenderPuw1Controller::class, 'update']
        )->name('update');


        // Destroy
        Route::delete(
            '/{id}',
            [PesananKalenderPuw1Controller::class, 'destroy']
        )->name('destroy');


        // Update No. PS
        Route::post(
            '/{id}/update-no-ps',
            [PesananKalenderPuw1Controller::class, 'updateNoPs']
        )->name('update-no-ps');

    });


/*
|--------------------------------------------------------------------------
| KALENDER - DLC, UNIT PASIF, REPORT ANGKA CETAK
| URL: /kalender/...   Nama route: kalender.dlc.*, kalender.pasif.*, kalender.report-angka-cetak
|--------------------------------------------------------------------------
*/
Route::prefix('kalender')
    ->name('kalender.')
    ->middleware('auth')
    ->group(function () {

        // ---------------------------- DLC
        Route::prefix('dlc')->name('dlc.')->group(function () {
            Route::get('/', [KalenderDlcController::class, 'dlcIndex'])->name('index');
            Route::get('/create', [KalenderDlcController::class, 'dlcCreate'])->name('create');
            Route::post('/store', [KalenderDlcController::class, 'dlcStore'])->name('store');
            Route::get('/{id}/edit', [KalenderDlcController::class, 'dlcEdit'])->name('edit');
            Route::put('/{id}', [KalenderDlcController::class, 'dlcUpdate'])->name('update');
            Route::put('/{id}/no-ps', [KalenderDlcController::class, 'dlcUpdateNoPs'])->name('update-no-ps');
            Route::get('/{id}', [KalenderDlcController::class, 'dlcShow'])->name('show');
            Route::delete('/{id}', [KalenderDlcController::class, 'dlcDestroy'])->name('destroy');
            Route::put('/pesanan/{id}', [KalenderDlcController::class, 'dlcUpdateQty'])->name('update-qty');
        });

        // ---------------------------- UNIT PASIF
        Route::prefix('pasif')->name('pasif.')->group(function () {

            Route::get('/', [KalenderPasifController::class, 'pasifMenu'])->name('index');

            Route::put('/{id}/no-ps', [KalenderPasifController::class, 'pasifUpdateNoPs'])
                ->whereNumber('id')->name('update-no-ps');

            // Spare Pasif 3%
            Route::get('/spare', [KalenderPasifController::class, 'sparePasif'])->name('spare');
            Route::put('/spare/{id}/no-ps', [KalenderPasifController::class, 'sparePasifUpdateNoPs'])->name('spare.update-no-ps');

            // Bacaan Unit
            Route::get('/bacaan', [KalenderPasifBacaanController::class, 'pasifBacaan'])->name('bacaan');
            Route::get('/bacaan/{id}', [KalenderPasifBacaanController::class, 'pasifBacaanShow'])->name('bacaan.show');
            Route::put('/bacaan/{id}/no-ps', [KalenderPasifBacaanController::class, 'pasifBacaanUpdateNoPs'])->name('bacaan.update-no-ps');
            Route::delete('/bacaan/{id}', [KalenderPasifBacaanController::class, 'pasifBacaanDestroy'])->name('bacaan.destroy');

            // Import (rekap hasil import)
            Route::get('/rekap', [KalenderPasifController::class, 'pasifRekap'])->name('rekap');
            Route::get('/rekap/{id}', [KalenderPasifController::class, 'pasifRekapShow'])->name('rekap.show');

            // Daftar Pasif
            Route::get('/list', [KalenderPasifController::class, 'pasifIndex'])->name('list');
            Route::get('/create', [KalenderPasifController::class, 'pasifCreate'])->name('create');
            Route::post('/store', [KalenderPasifController::class, 'pasifStore'])->name('store');

            // Create Manual
            Route::get('/manual', [KalenderPasifManualController::class, 'pasifManualIndex'])->name('manual.index');
            Route::get('/manual/create', [KalenderPasifManualController::class, 'pasifManualCreate'])->name('manual.create');
            Route::post('/manual', [KalenderPasifManualController::class, 'pasifManualStore'])->name('manual.store');
            Route::get('/manual/{id}/edit', [KalenderPasifManualController::class, 'pasifManualEdit'])->name('manual.edit');
            Route::put('/manual/{id}', [KalenderPasifManualController::class, 'pasifManualUpdate'])->name('manual.update');
            Route::get('/manual/{id}', [KalenderPasifManualController::class, 'pasifManualShow'])->name('manual.show');
            Route::delete('/manual/{id}', [KalenderPasifManualController::class, 'pasifManualDestroy'])->name('manual.destroy');

            // Detail Unit Pasif (paling bawah)
            Route::get('/{id}', [KalenderPasifController::class, 'pasifShow'])->name('show');
            Route::delete('/{id}', [KalenderPasifController::class, 'pasifDestroy'])->name('destroy');
        });

        // ---------------------------- REPORT ANGKA CETAK
        Route::get('/report-angka-cetak', [KalenderReportAngkaCetakController::class, 'reportAngkaCetak'])
            ->name('report-angka-cetak');
    });
