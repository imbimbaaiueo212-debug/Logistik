<?php

// ====================== STOK (stok, transfer, movement, kartu stok, opname, reject) ======================
// Di-load oleh StokServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Stok\Http\Controllers\RejectController;
use App\Modules\Stok\Http\Controllers\StockCardController;
use App\Modules\Stok\Http\Controllers\StockController;
use App\Modules\Stok\Http\Controllers\StockMovementController;
use App\Modules\Stok\Http\Controllers\StockOpnameController;
use App\Modules\Stok\Http\Controllers\TransferController;

// ====================== STOCK ======================
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

// ====================== TRANSFER ======================
Route::prefix('transfer')->name('transfer.')->group(function () {
    Route::get('/', [TransferController::class, 'index'])->name('index');
    Route::get('/create', [TransferController::class, 'create'])->name('create');
    Route::post('/', [TransferController::class, 'store'])->name('store');
    Route::get('/{id}', [TransferController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TransferController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TransferController::class, 'update'])->name('update');
    Route::delete('/{id}', [TransferController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/approve', [TransferController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [TransferController::class, 'reject'])->name('reject');
    Route::get('/stock/{id}', [TransferController::class, 'getStockByWarehouse']);
});

// ====================== OTHER MODULES ======================
Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
Route::get('/stock-movements/{id}', [StockMovementController::class, 'show'])->name('stock-movements.show');

Route::get('/stock-card', [StockCardController::class, 'index'])->name('stock-card.index');

Route::resource('stock-opname', StockOpnameController::class);
Route::post('/stock-opname/item/update', [StockOpnameController::class, 'ajaxUpdateItem'])->name('stock-opname.item.update');
Route::post('/stock-opname/{id}/submit', [StockOpnameController::class, 'submit'])->name('stock-opname.submit');
Route::post('/stock-opname/{id}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
Route::post('/stock-opname/{id}/cancel', [StockOpnameController::class, 'cancel'])->name('stock-opname.cancel');

Route::resource('reject', RejectController::class);
Route::post('/reject/{id}/return', [RejectController::class, 'return'])->name('reject.return');
Route::post('/reject/{id}/scrap', [RejectController::class, 'scrap'])->name('reject.scrap');
Route::post('/reject/{id}/repair', [RejectController::class, 'repair'])->name('reject.repair');
