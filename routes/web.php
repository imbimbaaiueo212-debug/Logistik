<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransferController;


Route::get('/', function () {
    return redirect('/login');
});

//login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//registrasi
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

//supplier
Route::resource('suppliers', SupplierController::class);

//produk
Route::resource('products', ProductController::class);

//supplier-Produk
Route::get('/supplier-product', [SupplierProductController::class, 'index'])->name('supplier-product.index');
Route::post('/supplier-product', [SupplierProductController::class, 'store'])->name('supplier-product.store');
Route::get('/supplier-product/{supplierId}/{productId}/edit', [SupplierProductController::class, 'edit'])->name('supplier-product.edit');
Route::put('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'update'])->name('supplier-product.update');
Route::delete('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'destroy'])->name('supplier-product.destroy');

// Purchase Order Routes
Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');

Route::get('/po/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
Route::put('/po/{po}', [PurchaseOrderController::class, 'update'])->name('po.update');
Route::delete('/po/{po}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
Route::get('/po/{id}', [PurchaseOrderController::class, 'show'])->name('po.show');

//good receipt
Route::get('/gr', [GoodsReceiptController::class, 'index'])->name('gr.index');
Route::get('/gr/create', [GoodsReceiptController::class, 'create'])->name('gr.create');
Route::get('/gr/po/{id}', [GoodsReceiptController::class, 'getPO']);
Route::post('/gr/store', [GoodsReceiptController::class, 'store'])->name('gr.store');
Route::get('/gr/{id}/edit', [GoodsReceiptController::class, 'edit'])->name('gr.edit');
Route::put('/gr/{id}', [GoodsReceiptController::class, 'update'])->name('gr.update');
Route::delete('/gr/{id}', [GoodsReceiptController::class, 'destroy'])->name('gr.destroy');

//warehouse
Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

//stock
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

//transfer
Route::prefix('transfer')->group(function () {
    Route::get('/', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/create', [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/', [TransferController::class, 'store'])->name('transfer.store');

    Route::get('/{id}', [TransferController::class, 'show'])->name('transfer.show');
    Route::get('/{id}/edit', [TransferController::class, 'edit'])->name('transfer.edit');
    Route::put('/{id}', [TransferController::class, 'update'])->name('transfer.update');
    Route::delete('/{id}', [TransferController::class, 'destroy'])->name('transfer.destroy');

    // AJAX
    Route::get('/stock/{id}', [TransferController::class, 'getStockByWarehouse']);
});

//dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

//API
Route::get('/get-products/{supplier}', function ($supplierId) {
    $supplier = \App\Models\Supplier::with('products')->find($supplierId);
    return response()->json($supplier->products);
});

