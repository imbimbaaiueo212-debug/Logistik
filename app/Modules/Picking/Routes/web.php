<?php

// ====================== PICKING ======================
// Di-load oleh PickingServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Picking\Http\Controllers\PickingController;
use App\Modules\Picking\Http\Controllers\PickingManualController;
use App\Modules\Picking\Http\Controllers\PickingPasifController;
use App\Modules\Picking\Http\Controllers\PickingLogistikController;

// === PICKING ROUTES ===
Route::prefix('picking')->name('picking.')->group(function () {
    Route::get('/', [PickingController::class, 'index'])->name('index');
    Route::get('/create', [PickingController::class, 'create'])->name('create');
    Route::post('/store', [PickingController::class, 'store'])->name('store');
    
    // Edit & Update
    Route::get('/{id}/edit', [PickingController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PickingController::class, 'update'])->name('update');
    
    // Generate Massal
    Route::post('/generate-all', [PickingController::class, 'generateAll'])->name('generate-all');
    
    // Hapus
    Route::delete('/{id}', [PickingController::class, 'destroy'])->name('destroy');
    
    // Jakarta Aktif
    Route::get('/jakarta/aktif', [PickingController::class, 'jakartaAktif'])->name('jakarta.aktif');

    // Jakarta Pasif
    Route::get('/jakarta/pasif', [PickingPasifController::class, 'jakartaPasif'])->name('jakarta.pasif');

    // ===== JAKARTA PASIF - UPDATE (perbaiki di sini) =====
    Route::post('/pasif/checklist', [PickingPasifController::class, 'updateChecklistPasif'])->name('pasif.checklist');
    Route::post('/pasif/pic', [PickingPasifController::class, 'updatePicPasif'])->name('pasif.pic');
    Route::post('/pasif/status', [PickingPasifController::class, 'updateStatusPasif'])->name('pasif.status');
    
    // ===== LOGISTIK AKTIF & PASIF (Persiapan, data dari RA yang sudah di-print) =====
    Route::get('/logistik/aktif', [PickingLogistikController::class, 'aktif'])->name('logistik.aktif');
    Route::get('/logistik/pasif', [PickingLogistikController::class, 'pasif'])->name('logistik.pasif');

    foreach (['aktif', 'pasif'] as $jenisLogistik) {
        Route::post("/logistik/{$jenisLogistik}/checklist", [PickingLogistikController::class, 'updateChecklist'])
            ->defaults('jenis', $jenisLogistik)->name("logistik.{$jenisLogistik}.checklist");
        Route::post("/logistik/{$jenisLogistik}/pic", [PickingLogistikController::class, 'updatePic'])
            ->defaults('jenis', $jenisLogistik)->name("logistik.{$jenisLogistik}.pic");
        Route::post("/logistik/{$jenisLogistik}/status", [PickingLogistikController::class, 'updateStatus'])
            ->defaults('jenis', $jenisLogistik)->name("logistik.{$jenisLogistik}.status");
    }

    // Checklist, PIC, Status (Normal / Aktif)
    Route::post('/checklist/update', [PickingController::class, 'updateChecklist'])->name('checklist.update');
    Route::post('/pic/update', [PickingController::class, 'updatePic'])->name('pic.update');
    Route::post('/status/update', [PickingController::class, 'updateStatus'])->name('status.update');

    // Order Manual (list)
    Route::get('/order-manual', [PickingManualController::class, 'orderManual'])->name('order-manual');

    // ===== MANUAL PICKING =====
    Route::post('/manual/checklist', [PickingManualController::class, 'updateChecklistManual'])->name('manual.checklist.update');
    Route::post('/manual/status', [PickingManualController::class, 'updateStatusManual'])->name('manual.status.update');
    Route::post('/manual/pic', [PickingManualController::class, 'updatePicManual'])->name('manual.pic.update');
});
