<?php

use Illuminate\Support\Facades\Route;

// ====================== AUTH CONTROLLERS ======================
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// ====================== MAIN CONTROLLERS ======================
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OrderController;
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
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitKemitraanController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\UserExportController;
use App\Http\Controllers\StokisMitraController;
use App\Http\Controllers\UnitKemitraanUserExportController;
use App\Http\Controllers\QcOutgoingController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\Majalah2026Controller;

use App\Http\Controllers\DistributionOrderController;

Route::get('/', function () {
    return redirect('/login');
});

// ====================== AUTH ======================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// ====================== USERS ======================
Route::resource('users', UserController::class);
Route::get('/users/{id}/reset-password', [UserController::class, 'resetForm'])->name('users.reset.form');
Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');

// ====================== IMPORT BIMBASHOP ======================
Route::prefix('import')
    ->name('import.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [ImportController::class, 'index'])->name('index');
        
        // Bimbashop
        Route::get('/bimbashop', [ImportController::class, 'bimbashop'])->name('bimbashop');
        Route::post('/bimbashop', [ImportController::class, 'bimbashopStore'])->name('bimbashop.store');
        Route::get('/bimbashop/{id}/edit', [ImportController::class, 'bimbashopEdit'])->name('bimbashop.edit');
        Route::put('/bimbashop/{id}', [ImportController::class, 'bimbashopUpdate'])->name('bimbashop.update');
        Route::delete('/bimbashop/{id}', [ImportController::class, 'bimbashopDestroy'])->name('bimbashop.destroy');

        // Casdana
        Route::get('/casdana', [ImportController::class, 'casdana'])->name('casdana');
        Route::post('/casdana', [ImportController::class, 'casdanaStore'])->name('casdana.store');
        // Route edit & delete Casdana (bisa ditambahkan nanti)
        Route::get('/casdana/{id}/edit', [ImportController::class, 'casdanaEdit'])->name('casdana.edit');
        Route::put('/casdana/{id}', [ImportController::class, 'casdanaUpdate'])->name('casdana.update');
        Route::delete('/casdana/{id}', [ImportController::class, 'casdanaDestroy'])->name('casdana.destroy');
    });

// === ORDER ROUTES ===
Route::prefix('order')
    ->name('order.')
    ->middleware('auth')
    ->group(function () {

        // =========================
        // MENU
        // =========================
        Route::get('/', [OrderController::class, 'index'])->name('index');

        Route::get('/unit-aktif', [OrderController::class, 'unitAktif'])
            ->name('unit-aktif');

        Route::get('/unit-pasif', [OrderController::class, 'unitPasif'])
            ->name('unit-pasif');

        // =========================
        // JAKARTA AKTIF
        // =========================
        Route::get('/jakarta-aktif', [OrderController::class, 'jakartaAktif'])
            ->name('jakarta-aktif');

        // Halaman Modul
        Route::get('/modul', [OrderController::class, 'modul'])
            ->name('modul');

        Route::get('/majalah', [OrderController::class, 'majalah'])
            ->name('majalah');

        Route::get('/sertifikat', [OrderController::class, 'sertifikat'])
            ->name('sertifikat');

        Route::post('/jakarta-aktif/import', [OrderController::class, 'importJakartaAktif'])
            ->name('jakarta-aktif.import');

        Route::post('/jakarta-aktif/sync-jkt', [OrderController::class, 'syncJktFromBimbashop'])
            ->name('jakarta-aktif.sync-jkt');

        Route::post('/jakarta-aktif/bulk-action', [OrderController::class, 'bulkActionJakartaAktif'])
            ->name('jakarta-aktif.bulk-action');

        Route::get('/jakarta-aktif/{id}/edit', [OrderController::class, 'editJakartaAktif'])
            ->name('jakarta-aktif.edit');

        Route::put('/jakarta-aktif/{id}', [OrderController::class, 'updateJakartaAktif'])
            ->name('jakarta-aktif.update');

        Route::get('/jakarta-aktif/filtered-ids', [OrderController::class, 'getFilteredIds'])
            ->name('jakarta-aktif.filtered-ids');

        Route::post('/jakarta-aktif/get-modal-data', [OrderController::class, 'getModalData'])
            ->name('jakarta-aktif.get-modal-data');

        Route::get('/jakarta-aktif/export', [OrderController::class, 'exportJakartaAktif'])
            ->name('jakarta-aktif.export');

        // =========================
        // REALISASI
        // =========================
        Route::get('/jakarta-printed', [OrderController::class, 'jakartaPrinted'])
            ->name('jakarta-printed');

        Route::delete('/realisasi/{id}', [OrderController::class, 'deleteRealisasi'])
            ->name('realisasi.delete');

        Route::post('/realisasi/mark-printed-all', [OrderController::class, 'markAllAsPrinted'])
            ->name('realisasi.mark-printed-all');

        // =========================
        // PRINT
        // =========================
        Route::get('/realisasi/print-pdf', [OrderController::class, 'printRealisasiPdf'])
            ->name('realisasi.print-pdf');

        Route::get('/realisasi/print-pdf/{id}', [OrderController::class, 'printSingleRealisasi'])
            ->name('realisasi.print-single');

        Route::get('/realisasi/picking-list/{id}', [OrderController::class, 'printPickingList'])
            ->name('realisasi.picking-list');

        Route::get('/picking-list/pdf/{id}', [OrderController::class, 'printPickingListPdf'])
            ->name('picking-list.pdf');

        Route::get('/realisasi/print-qc', [OrderController::class, 'printQC'])
            ->name('realisasi.print-qc');

        Route::get('/realisasi/print-pemesanan', [OrderController::class, 'printPemesanan'])
            ->name('realisasi.print-pemesanan');

        Route::get('/realisasi/print-ekspedisi', [OrderController::class, 'printEkspedisi'])
            ->name('realisasi.print-ekspedisi');

        Route::get('/realisasi/print-packing', [OrderController::class, 'printPacking'])
            ->name('realisasi.print-packing');
            Route::get('/realisasi/picking-list-all', [OrderController::class, 'printPickingListAll'])
        ->name('print-picking-list-all');

    Route::get('/realisasi/print-qc-all', [OrderController::class, 'printQCAll'])
        ->name('print-qc-all');

    Route::get('/realisasi/print-packing-all', [OrderController::class, 'printPackingAll'])
        ->name('print-packing-all');

    Route::get('/realisasi/print-ekspedisi-all', [OrderController::class, 'printEkspedisiAll'])
        ->name('print-ekspedisi-all');

    Route::get('/realisasi/print-ra-all', [OrderController::class, 'printRealisasiAll'])
        ->name('print-ra-all');
    });

// === PICKING ROUTES ===
Route::prefix('picking')->name('picking.')->group(function () {
    Route::get('/', [PickingController::class, 'index'])->name('index');
    Route::get('/create', [PickingController::class, 'create'])->name('create');
    Route::post('/store', [PickingController::class, 'store'])->name('store');
    
    // Edit & Update
    Route::get('/{id}/edit', [PickingController::class, 'edit'])->name('edit');           // ← Tambahkan ini
    Route::put('/{id}', [PickingController::class, 'update'])->name('update');           // ← Tambahkan ini
    
    // Generate Massal
    Route::post('/generate-all', [PickingController::class, 'generateAll'])->name('generate-all');
    
    // Hapus
    Route::delete('/{id}', [PickingController::class, 'destroy'])->name('destroy');
    
    // Jakarta Aktif
    Route::get('/jakarta/aktif', [PickingController::class, 'jakartaAktif'])->name('jakarta.aktif');
    
    // Checklist
    Route::post('/checklist/update', [PickingController::class, 'updateChecklist'])
         ->name('checklist.update');   // ini sudah benar
    Route::post('/pic/update', [PickingController::class, 'updatePic'])
        ->name('pic.update');
    Route::post('/status/update', [PickingController::class, 'updateStatus'])
        ->name('status.update');
});
// ====================== SUPPLIERS ======================
Route::resource('suppliers', SupplierController::class);

// ====================== PRODUCTS ======================
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
Route::resource('products', ProductController::class)->except(['show']);

// ====================== CATEGORIES ======================
Route::resource('categories', CategoryController::class);

// ====================== SUPPLIER PRODUCT ======================
Route::get('/supplier-product', [SupplierProductController::class, 'index'])->name('supplier-product.index');
Route::post('/supplier-product', [SupplierProductController::class, 'store'])->name('supplier-product.store');
Route::get('/supplier-product/{supplierId}/{productId}/edit', [SupplierProductController::class, 'edit'])->name('supplier-product.edit');
Route::put('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'update'])->name('supplier-product.update');
Route::delete('/supplier-product/{supplierId}/{productId}', [SupplierProductController::class, 'destroy'])->name('supplier-product.destroy');

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

// ====================== WAREHOUSE ======================
Route::resource('warehouses', WarehouseController::class);

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

Route::resource('distribution', DistributionController::class);
Route::post('/distribution/{id}/approve', [DistributionController::class, 'approve'])->name('distribution.approve');
Route::post('/distribution/{id}/reject', [DistributionController::class, 'reject'])->name('distribution.reject');

Route::resource('return-distribution', ReturnDistributionController::class);
Route::get('/return-distribution-items/{id}', [ReturnDistributionController::class, 'getItems']);
Route::post('/return-distribution/{id}/approve', [ReturnDistributionController::class, 'approve'])->name('return-distribution.approve');

Route::resource('quotation', QuotationController::class)->except(['edit', 'update']);
Route::post('quotation/{id}/convert/{supplier}', [QuotationController::class, 'convertToPO'])->name('quotation.convert.po');
Route::post('/quotation/{id}/approve', [QuotationController::class, 'approve'])->name('quotation.approve');
Route::post('/quotation/{id}/reject', [QuotationController::class, 'reject'])->name('quotation.reject');
Route::post('/quotation/{id}/send', [QuotationController::class, 'send'])->name('quotation.send');

// ====================== HOME & DASHBOARD ======================
Route::get('/home', function () {
    return view('home');
})->middleware('auth')->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// ====================== API ======================
Route::get('/get-products/{supplier}', function ($supplierId) {
    $supplier = \App\Models\Supplier::with('products')->find($supplierId);
    return response()->json($supplier?->products ?? []);
})->name('get-products');


//=========user kemitraan
Route::resource('unit-kemitraan', UnitKemitraanController::class);

Route::get('/unit-kemitraan/import', [UnitKemitraanController::class, 'importForm'])
     ->name('unit-kemitraan.import');

Route::post('/unit-kemitraan/import', [UnitKemitraanController::class, 'import'])
     ->name('unit-kemitraan.import.store');   // tetap pakai .import.store biar form tidak berubah
     Route::get('/unit-kemitraan/fix-status', [App\Http\Controllers\UnitKemitraanController::class, 'fixStatusPengelolaan'])
     ->name('unit-kemitraan.fix-status');

//============= untuk user export bimba shop
// Import User biMBA Shop
Route::get('/user-export', [UserExportController::class, 'index'])->name('user.export');
Route::post('/user-export/import', [UserExportController::class, 'import'])->name('user-export.import');

// Optional: Delete (jika kamu pakai)
Route::delete('/user-export/{id}', [UserExportController::class, 'destroy'])->name('user-export.destroy');

//stokis apps
// Stokis Mitra
Route::prefix('stokis-mitra')->name('stokis.')->group(function () {
    Route::get('/', [StokisMitraController::class, 'index'])->name('index');
    Route::post('/import', [StokisMitraController::class, 'import'])->name('import');
});

Route::resource('unit-kemitraan-user', UnitKemitraanUserExportController::class);
Route::post('unit-kemitraan-user/import-unit', [UnitKemitraanUserExportController::class, 'importUnit'])->name('unit-kemitraan-user.import-unit');
Route::post('unit-kemitraan-user/import-user', [UnitKemitraanUserExportController::class, 'importUserExport'])->name('unit-kemitraan-user.import-user');


//menu baru
Route::get('/order/jakarta-aktif/menu', function () {
    return view('order.jakarta-aktif-menu');
})->name('order.jakarta-aktif.menu');

Route::get('/order/jakarta-aktif/realisasi', function () {
    return view('order.jakarta-aktif-realisasi');
})->name('order.jakarta-aktif.realisasi');

Route::get('/order/jakarta-aktif/realisasi/modul', [OrderController::class, 'jakartaAktifModul'])
    ->name('order.jakarta-aktif.modul');

    Route::get('/order/jakarta-aktif/realisasi/majalah', function () {
    return view('order.jakarta-aktif-majalah-menu');
})->name('order.jakarta-aktif.majalah');

Route::view(
    '/order/jakarta-aktif/realisasi/modul',
    'order.jakarta-aktif-modul-menu'
)->name('order.jakarta-aktif.modul');

Route::post('/unit-kemitraan-user/generate-match', [UnitKemitraanUserExportController::class, 'generateMatch'])
     ->name('unit-kemitraan-user.generate-match');



     //menu untuk index ada di dalam sini
     // ====================== DATABASE USER HUB ======================
Route::prefix('database-user')
    ->name('database-user.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [App\Http\Controllers\DatabaseUserController::class, 'index'])
             ->name('index');
    });

// ====================== QC OUTGOING ======================
Route::prefix('qc-outgoing')
    ->name('qc-outgoing.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [App\Http\Controllers\QcOutgoingController::class, 'index'])
             ->name('index');

        Route::get('/jakarta-aktif', [App\Http\Controllers\QcOutgoingController::class, 'jakartaAktif'])
             ->name('jakarta-aktif');

        Route::post('/store', [App\Http\Controllers\QcOutgoingController::class, 'qcStore'])
             ->name('store');
    });

//ORDER MANUAL
Route::get('/import/manual', [ImportController::class,'manual'])
    ->name('import.manual');

Route::get('/import/manual/create', [ImportController::class,'manualCreate'])
    ->name('import.manual.create');
Route::post('/import/manual', [ImportController::class, 'manualImport'])->name('import.manual.store');

Route::post('/import/manual/store', [ImportController::class,'manualStore'])
    ->name('import.manual.store');

Route::get('/import/manual/{id}/edit', [ImportController::class,'manualEdit'])
    ->name('import.manual.edit');

Route::put('/import/manual/{id}', [ImportController::class,'manualUpdate'])
    ->name('import.manual.update');

Route::delete('/import/manual/{id}', [ImportController::class,'manualDestroy'])
    ->name('import.manual.destroy');


    // ====================== PACKING ======================
Route::prefix('packing')->group(function () {

    Route::get('/', [PackingController::class, 'index'])
        ->name('packing.index');

    Route::get('/jakarta-aktif', [PackingController::class, 'jakartaAktif'])
        ->name('packing.jakarta.aktif');

    // Tambahkan route ini
    Route::put('/{id}', [PackingController::class, 'update'])
        ->name('packing.update');

    Route::post('/store', [PackingController::class, 'store'])
        ->name('packing.store');
});

// Distribution Order Routes
Route::prefix('distribution-order')
    ->name('distribution-order.')
    ->group(function () {

        Route::get('/', [DistributionOrderController::class, 'index'])->name('index');

        Route::get('/jakarta-aktif', [DistributionOrderController::class, 'jakartaAktif'])->name('jakarta-aktif');
        Route::get('/jakarta-pasif', [DistributionOrderController::class, 'jakartaPasif'])->name('jakarta-pasif');
        Route::get('/intervio', [DistributionOrderController::class, 'intervio'])->name('intervio');
        Route::get('/ebt', [DistributionOrderController::class, 'ebt'])->name('ebt');

        // === ROUTE BARU YANG DIBUTUHKAN ===
        Route::get('/create', [DistributionOrderController::class, 'create'])->name('create');
        Route::post('/', [DistributionOrderController::class, 'store'])->name('store');

        Route::get('/{distributionOrder}', [DistributionOrderController::class, 'show'])->name('show');
        Route::get('/{distributionOrder}/edit', [DistributionOrderController::class, 'edit'])->name('edit');
        Route::put('/{distributionOrder}', [DistributionOrderController::class, 'update'])->name('update');
        Route::delete('/{distributionOrder}', [DistributionOrderController::class, 'destroy'])->name('destroy');
    });

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