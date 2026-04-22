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
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockCardController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\RejectController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ReturnDistributionController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\UserController;


    Route::get('/', function () {
        return redirect('/login');
    });

    //login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    //end login

    //registrasi
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
    //end registrasi

    //user
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // tambahan reset password
    Route::get('/users/{id}/reset-password', [UserController::class, 'resetForm'])->name('users.reset.form');
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');

    //supplier
    Route::resource('suppliers', SupplierController::class);
    //end supplier

    //produk
    Route::resource('products', ProductController::class);
    //end produk

    //supplier-Produk
    Route::get('/supplier-product', [SupplierProductController::class, 'index'])->name('supplier-product.index');
    Route::post('/supplier-product', [SupplierProductController::class, 'store'])->name('supplier-product.store');
    Route::get('/supplier-product/{supplierId}/{productId}/edit', [SupplierProductController::class, 'edit'])->name('supplier-product.edit');
    Route::put('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'update'])->name('supplier-product.update');
    Route::delete('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'destroy'])->name('supplier-product.destroy');
    //end supplier-produk

    // Purchase Order Routes
    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');

    Route::get('/po/{po}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/{po}', [PurchaseOrderController::class, 'update'])->name('po.update');
    Route::delete('/po/{po}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::get('/po/{id}', [PurchaseOrderController::class, 'show'])->name('po.show');
    //end purchase order

    //good receipt
    Route::get('/gr', [GoodsReceiptController::class, 'index'])->name('gr.index');
    Route::get('/gr/create', [GoodsReceiptController::class, 'create'])->name('gr.create');
    Route::get('/gr/po/{id}', [GoodsReceiptController::class, 'getPO']);
    Route::post('/gr/store', [GoodsReceiptController::class, 'store'])->name('gr.store');
    Route::get('/gr/{id}/edit', [GoodsReceiptController::class, 'edit'])->name('gr.edit');
    Route::put('/gr/{id}', [GoodsReceiptController::class, 'update'])->name('gr.update');
    Route::delete('/gr/{id}', [GoodsReceiptController::class, 'destroy'])->name('gr.destroy');
    Route::get('/gr/{id}/qc', [GoodsReceiptController::class, 'qcPage'])->name('gr.qc.page');
    Route::post('/gr/qc/{id}', [GoodsReceiptController::class, 'qc'])->name('gr.qc');
    //end good receipt
    

    //warehouse
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    //end warehouse

    //stock
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    //end stock

    //transfer
    Route::prefix('transfer')->group(function () {
    Route::get('/', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/create', [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/', [TransferController::class, 'store'])->name('transfer.store');

    Route::get('/{id}', [TransferController::class, 'show'])->name('transfer.show');
    Route::get('/{id}/edit', [TransferController::class, 'edit'])->name('transfer.edit');
    Route::put('/{id}', [TransferController::class, 'update'])->name('transfer.update');
    Route::delete('/{id}', [TransferController::class, 'destroy'])->name('transfer.destroy');
    Route::post('/transfer/{id}/approve', [TransferController::class, 'approve'])
    ->name('transfer.approve');
    Route::post('/transfer/{id}/reject', [TransferController::class, 'reject'])
    ->name('transfer.reject');

    // AJAX
    Route::get('/stock/{id}', [TransferController::class, 'getStockByWarehouse']);
    });
    //end transfer

    //stock movement
    Route::get('/stock-movements', [StockMovementController::class, 'index'])
    ->name('stock-movements.index');
    Route::get('/stock-movements/{id}', [StockMovementController::class, 'show'])
    ->name('stock-movements.show');
    //end stock movement

    //stok card
    Route::get('/stock-card', [StockCardController::class, 'index'])->name('stock-card.index');
    //end stock card

    // Stock Opname
    Route::resource('stock-opname', StockOpnameController::class);
    Route::post('/stock-opname/item/update', [StockOpnameController::class, 'ajaxUpdateItem'])
    ->name('stock-opname.item.update');
    Route::post('/stock-opname/{id}/submit', [StockOpnameController::class, 'submit'])
    ->name('stock-opname.submit');
    Route::post('/stock-opname/{id}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
    Route::post('/stock-opname/{id}/cancel', [StockOpnameController::class, 'cancel'])->name('stock-opname.cancel');
    //end stock opname

    //reject
    Route::get('/reject', [RejectController::class, 'index'])->name('reject.index');
    Route::post('/reject/{id}/return', [RejectController::class, 'return'])->name('reject.return');
    Route::post('/reject/{id}/scrap', [RejectController::class, 'scrap'])->name('reject.scrap');
    Route::post('/reject/{id}/repair', [RejectController::class, 'repair'])->name('reject.repair');
    //end reject

    //distribution
    Route::resource('distribution', DistributionController::class);
    Route::get('/distribution-stock/{id}', [DistributionController::class, 'getStock']);
    Route::post('/distribution/{id}/approve', [DistributionController::class, 'approve'])->name('distribution.approve');
    Route::post('/distribution/{id}/reject', [DistributionController::class, 'reject'])->name('distribution.reject');
    //end distribution

    //return distribution
    Route::resource('return-distribution', ReturnDistributionController::class);

    Route::get('/return-distribution-items/{id}', 
        [ReturnDistributionController::class, 'getItems']);

    Route::post('/return-distribution/{id}/approve', 
        [ReturnDistributionController::class, 'approve'])
        ->name('return-distribution.approve');

    //end return distribution

    //quotation
    Route::resource('quotation', QuotationController::class);
    Route::post('quotation/{id}/convert/{supplier}', [QuotationController::class,'convertToPO']);
    Route::get('/get-products/{supplier}', [QuotationController::class, 'getProductsBySupplier']);
    Route::post('quotation/{id}/send', [QuotationController::class, 'send'])
    ->name('quotation.send');
    Route::resource('quotation', QuotationController::class)
    ->except(['edit', 'update']);
    Route::post('/quotation/{id}/approve', [QuotationController::class, 'approve'])->name('quotation.approve');
    Route::post('/quotation/{id}/reject', [QuotationController::class, 'reject'])->name('quotation.reject');
    Route::post('/quotation/{id}/send', [QuotationController::class, 'send'])->name('quotation.send');
    Route::post('/quotation/{id}/convert-po/{supplier}', 
    [QuotationController::class, 'convertToPO'])
    ->name('quotation.convert.po');
    //end quotation

    //midle
    Route::post('/stock-in', [StockController::class, 'store'])
    ->middleware('freeze');
    Route::post('/stock-in', [StockController::class, 'store'])
    ->middleware('freeze');
    Route::post('/stock-out', [StockController::class, 'out'])
    ->middleware('freeze');
    //end midle


    //dashboard
    Route::get('/dashboard', function () {
    return view('dashboard');
    })->middleware('auth')->name('dashboard');

    //API
    Route::get('/get-products/{supplier}', function ($supplierId) {
    $supplier = \App\Models\Supplier::with('products')->find($supplierId);
    return response()->json($supplier->products);
    });

