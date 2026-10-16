<?php

// ====================== MASTER DATA (user, produk, supplier, kategori, gudang, unit kemitraan, stokis) ======================
// Di-load oleh MasterServiceProvider (grup middleware "web").

use Illuminate\Support\Facades\Route;
use App\Modules\Master\Http\Controllers\CategoryController;
use App\Modules\Master\Http\Controllers\DatabaseUserController;
use App\Modules\Master\Http\Controllers\ProductController;
use App\Modules\Master\Http\Controllers\StokisMitraController;
use App\Modules\Master\Http\Controllers\StokisPasifController;
use App\Modules\Master\Http\Controllers\SupplierController;
use App\Modules\Master\Http\Controllers\SupplierProductController;
use App\Modules\Master\Http\Controllers\UnitKemitraanController;
use App\Modules\Master\Http\Controllers\UnitKemitraanUserExportController;
use App\Modules\Master\Http\Controllers\UserController;
use App\Modules\Master\Http\Controllers\UserExportController;
use App\Modules\Master\Http\Controllers\WarehouseController;

// ====================== USERS ======================
Route::resource('users', UserController::class);
Route::get('/users/{id}/reset-password', [UserController::class, 'resetForm'])->name('users.reset.form');
Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
Route::get('/users/{id}/access', [UserController::class, 'accessForm'])->name('users.access');
Route::put('/users/{id}/access', [UserController::class, 'updateAccess'])->name('users.access.update');

// ====================== SUPPLIERS ======================
Route::resource('suppliers', SupplierController::class);

// ====================== PRODUCTS ======================
Route::resource('products', ProductController::class)->except(['show']);

Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Tambahkan ini
Route::post('/products/bulk-edit-data', [ProductController::class, 'getBulkEditData'])
     ->name('products.bulk-edit-data');

Route::post('/products/bulk-update', [ProductController::class, 'bulkUpdate'])
     ->name('products.bulk-update');

// ====================== CATEGORIES ======================
Route::resource('categories', CategoryController::class);

// ====================== SUPPLIER PRODUCT ======================
Route::get('/supplier-product', [SupplierProductController::class, 'index'])->name('supplier-product.index');
Route::post('/supplier-product', [SupplierProductController::class, 'store'])->name('supplier-product.store');
Route::get('/supplier-product/{supplierId}/{productId}/edit', [SupplierProductController::class, 'edit'])->name('supplier-product.edit');
Route::put('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'update'])->name('supplier-product.update');
Route::delete('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'destroy'])->name('supplier-product.destroy');

// ====================== WAREHOUSE ======================
Route::resource('warehouses', WarehouseController::class);

//=========user kemitraan
Route::resource('unit-kemitraan', UnitKemitraanController::class);

Route::get('/unit-kemitraan/import', [UnitKemitraanController::class, 'importForm'])
     ->name('unit-kemitraan.import');

Route::post('/unit-kemitraan/import', [UnitKemitraanController::class, 'import'])
     ->name('unit-kemitraan.import.store');   // tetap pakai .import.store biar form tidak berubah
     Route::get('/unit-kemitraan/fix-status', [App\Modules\Master\Http\Controllers\UnitKemitraanController::class, 'fixStatusPengelolaan'])
     ->name('unit-kemitraan.fix-status');

//============= untuk user export bimba shop
// Import User biMBA Shop
Route::get('/user-export', [UserExportController::class, 'index'])->name('user.export');
Route::post('/user-export/import', [UserExportController::class, 'import'])->name('user-export.import');

// Optional: Delete (jika kamu pakai)
Route::delete('/user-export/{id}', [UserExportController::class, 'destroy'])->name('user-export.destroy');

//stokis apps
// Stokis Mitra
// Stokis Aktif
Route::prefix('stokis-mitra')->name('stokis.')->group(function () {
    Route::get('/', [StokisMitraController::class, 'index'])->name('index');
    Route::get('/{id}/edit', [StokisMitraController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StokisMitraController::class, 'update'])->name('update');
    Route::delete('/{id}', [StokisMitraController::class, 'destroy'])->name('destroy');
    Route::post('/import', [StokisMitraController::class, 'import'])->name('import');
    Route::post('/{id}/pasifkan', [StokisMitraController::class, 'pasifkan'])->name('pasifkan'); // baru
});

// Stokis Pasif (baru)
Route::prefix('stokis-pasif')->name('stokis-pasif.')->group(function () {
    Route::get('/', [StokisPasifController::class, 'index'])->name('index');
    Route::get('/create', [StokisPasifController::class, 'create'])->name('create');
    Route::post('/', [StokisPasifController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [StokisPasifController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StokisPasifController::class, 'update'])->name('update');
    Route::post('/{id}/aktifkan', [StokisPasifController::class, 'aktifkan'])->name('aktifkan');
    Route::delete('/{id}', [StokisPasifController::class, 'destroy'])->name('destroy');
});

Route::resource('unit-kemitraan-user', UnitKemitraanUserExportController::class);
Route::post('unit-kemitraan-user/import-unit', [UnitKemitraanUserExportController::class, 'importUnit'])->name('unit-kemitraan-user.import-unit');
Route::post('unit-kemitraan-user/import-user', [UnitKemitraanUserExportController::class, 'importUserExport'])->name('unit-kemitraan-user.import-user');

Route::post('/unit-kemitraan-user/generate-match', [UnitKemitraanUserExportController::class, 'generateMatch'])
     ->name('unit-kemitraan-user.generate-match');


     //menu untuk index ada di dalam sini
     // ====================== DATABASE USER HUB ======================
Route::prefix('database-user')
    ->name('database-user.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [App\Modules\Master\Http\Controllers\DatabaseUserController::class, 'index'])
             ->name('index');
    });
         Route::get('/ops2', [DatabaseUserController::class, 'ops2'])->name('ops2.index');
