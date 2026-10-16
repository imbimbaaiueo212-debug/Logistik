<?php

// ====================== PENGADAAN (Purchase Order, Goods Receipt, Quotation) ======================
// Di-load oleh PengadaanServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Pengadaan\Http\Controllers\GoodsReceiptController;
use App\Modules\Pengadaan\Http\Controllers\PurchaseOrderController;
use App\Modules\Pengadaan\Http\Controllers\QuotationController;

// ====================== PURCHASE ORDER ======================
Route::prefix('po')->name('po.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
    Route::put('/{po}', [PurchaseOrderController::class, 'update'])->name('update');
    Route::delete('/{po}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
    Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('show');
});

// ====================== GOODS RECEIPT ======================
Route::prefix('gr')->name('gr.')->group(function () {
    Route::get('/', [GoodsReceiptController::class, 'index'])->name('index');
    Route::get('/create', [GoodsReceiptController::class, 'create'])->name('create');
    Route::get('/po/{id}', [GoodsReceiptController::class, 'getPO']);
    Route::post('/store', [GoodsReceiptController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [GoodsReceiptController::class, 'edit'])->name('edit');
    Route::put('/{id}', [GoodsReceiptController::class, 'update'])->name('update');
    Route::delete('/{id}', [GoodsReceiptController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/qc', [GoodsReceiptController::class, 'qcPage'])->name('qc.page');
    Route::post('/qc/{id}', [GoodsReceiptController::class, 'qc'])->name('qc');
});

Route::resource('quotation', QuotationController::class)->except(['edit', 'update']);
Route::post('quotation/{id}/convert/{supplier}', [QuotationController::class, 'convertToPO'])->name('quotation.convert.po');
Route::post('/quotation/{id}/approve', [QuotationController::class, 'approve'])->name('quotation.approve');
Route::post('/quotation/{id}/reject', [QuotationController::class, 'reject'])->name('quotation.reject');
Route::post('/quotation/{id}/send', [QuotationController::class, 'send'])->name('quotation.send');
