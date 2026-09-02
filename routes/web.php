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
use App\Http\Controllers\PesananMajalahController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\Majalah2026Controller;
use App\Http\Controllers\PesananMajalahKotamadyaController;
use App\Http\Controllers\PesananMajalahPuw1Controller;
use App\Http\Controllers\DatabaseUserController;
use App\Http\Controllers\OrderManualController;
use App\Http\Controllers\OrderManualModulController;
use App\Http\Controllers\OrderManualSertifikatController;

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

// ====================== ORDER MANUAL ===============
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
        Route::get('/casdana/{id}/edit', [ImportController::class, 'casdanaEdit'])->name('casdana.edit');
        Route::put('/casdana/{id}', [ImportController::class, 'casdanaUpdate'])->name('casdana.update');
        Route::delete('/casdana/{id}', [ImportController::class, 'casdanaDestroy'])->name('casdana.destroy');

        // =====================================================
        // MANUAL
        // =====================================================
        Route::get('/manual', [ImportController::class, 'manual'])->name('manual');
        Route::post('/manual', [ImportController::class, 'manualImport'])->name('manual.store');
        Route::get('/manual/create', [ImportController::class, 'manualCreate'])->name('manual.create');
        Route::post('/manual/store', [ImportController::class, 'manualStore'])->name('manual.store.single');
        Route::get('/manual/{id}/edit', [ImportController::class, 'manualEdit'])->name('manual.edit');
        Route::put('/manual/{id}', [ImportController::class, 'manualUpdate'])->name('manual.update');
        Route::delete('/manual/{id}', [ImportController::class, 'manualDestroy'])->name('manual.destroy');

        // Bulk proses Manual
        Route::get('/manual/filtered-ids', [ImportController::class, 'getManualFilteredIds'])
            ->name('manual.filtered-ids');

        Route::post('/manual/get-modal-data', [ImportController::class, 'getManualModalData'])
            ->name('manual.get-modal-data');

        Route::post('/manual/bulk-action', [ImportController::class, 'bulkActionManual'])
            ->name('manual.bulk-action');

        // Sync Pesanan Majalah → Manual
        Route::post('/sync-pesanan-majalah', [ImportController::class, 'syncPesananMajalahToJakartaAktif'])
            ->name('sync-pesanan-majalah');

        // =====================================================
        // DLC
        // =====================================================
        Route::prefix('dlc')->name('dlc.')->group(function () {
            Route::get('/', [ImportController::class, 'dlcIndex'])->name('index');
            Route::get('/create', [ImportController::class, 'dlcCreate'])->name('create');
            Route::post('/store', [ImportController::class, 'dlcStore'])->name('store');
            Route::get('/{id}/edit', [ImportController::class, 'dlcEdit'])->name('edit');
            Route::put('/{id}', [ImportController::class, 'dlcUpdate'])->name('update');
            Route::put('/{id}/no-ps', [ImportController::class, 'dlcUpdateNoPs'])->name('update-no-ps');
            Route::get('/{id}', [ImportController::class, 'dlcShow'])->name('show');
            Route::delete('/{id}', [ImportController::class, 'dlcDestroy'])->name('destroy');
            Route::put('/pesanan/{id}', [ImportController::class, 'dlcUpdateQty'])->name('update-qty');
        });

        // =====================================================
        // GROUP PASIF
        // =====================================================
        Route::prefix('pasif')->name('pasif.')->group(function () {

            Route::get('/', [ImportController::class, 'pasifMenu'])->name('index');

            Route::put('/{id}/no-ps', [ImportController::class, 'pasifUpdateNoPs'])->name('update-no-ps');

            // SPARE PASIF 3%
            Route::get('/spare', [ImportController::class, 'sparePasif'])->name('spare');
            Route::get('/spare/{edisi}', [ImportController::class, 'sparePasifShow'])->name('spare.show');
            Route::put('/spare/{id}/no-ps', [ImportController::class, 'sparePasifUpdateNoPs'])->name('spare.update-no-ps');

            // Bacaan Unit
            Route::get('/bacaan', [ImportController::class, 'pasifBacaan'])->name('bacaan');
            Route::get('/bacaan/create', [ImportController::class, 'pasifBacaanCreate'])->name('bacaan.create');
            Route::post('/bacaan/store', [ImportController::class, 'pasifBacaanStore'])->name('bacaan.store');
            Route::get('/bacaan/{id}', [ImportController::class, 'pasifBacaanShow'])->name('bacaan.show');
            Route::put('/bacaan/{id}/no-ps', [ImportController::class, 'pasifBacaanUpdateNoPs'])->name('bacaan.update-no-ps');
            Route::delete('/bacaan/{id}', [ImportController::class, 'pasifBacaanDestroy'])->name('bacaan.destroy');

            // Rekap Total
            Route::get('/rekap', [ImportController::class, 'pasifRekap'])->name('rekap');
            Route::get('/rekap/{id}', [ImportController::class, 'pasifRekapShow'])->name('rekap.show');

            // Unit Pasif (list)
            Route::get('/list', [ImportController::class, 'pasifIndex'])->name('list');
            Route::get('/create', [ImportController::class, 'pasifCreate'])->name('create');
            Route::post('/store', [ImportController::class, 'pasifStore'])->name('store');
            Route::post('/sync-to-manual', [ImportController::class, 'syncPasifToManual'])->name('sync');

            // PASIF MANUAL
            Route::get('/manual', [ImportController::class, 'pasifManualIndex'])->name('manual.index');
            Route::get('/manual/create', [ImportController::class, 'pasifManualCreate'])->name('manual.create');
            Route::post('/manual', [ImportController::class, 'pasifManualStore'])->name('manual.store');
            Route::get('/manual/{id}/edit', [ImportController::class, 'pasifManualEdit'])->name('manual.edit');
            Route::put('/manual/{id}', [ImportController::class, 'pasifManualUpdate'])->name('manual.update');
            Route::get('/manual/{id}', [ImportController::class, 'pasifManualShow'])->name('manual.show');
            Route::delete('/manual/{id}', [ImportController::class, 'pasifManualDestroy'])->name('manual.destroy');

            // Detail Unit Pasif (paling bawah)
            Route::get('/{id}', [ImportController::class, 'pasifShow'])->name('show');
            Route::delete('/{id}', [ImportController::class, 'pasifDestroy'])->name('destroy');
        });

        // =====================================================
        // REPORT ANGKA CETAK
        // =====================================================
        Route::get('/report-angka-cetak', [ImportController::class, 'reportAngkaCetak'])
            ->name('report-angka-cetak');

        // =====================================================
        // MANUAL PRINTED
        // =====================================================
        Route::get('/manual-printed', [ImportController::class, 'manualPrinted'])
            ->name('manual-printed');

        Route::get('/manual-printed/print-pdf', [ImportController::class, 'printManualRealisasiPdf'])
            ->name('manual-printed.pdf');

        Route::get('/manual-printed/picking/{id}', [ImportController::class, 'printManualPickingList'])
            ->name('manual-printed.picking');

        Route::get('/manual-printed/picking-pdf/{id}', [ImportController::class, 'printManualPickingListPdf'])
            ->name('manual-printed.picking-pdf');

        Route::delete('/manual-printed/{id}', [ImportController::class, 'deleteManualRealisasi'])
            ->name('manual-printed.destroy');

        // Update catatan
        Route::post('/manual-printed/{id}/catatan', [ImportController::class, 'updateManualCatatan'])
            ->name('manual-printed.update-catatan');

        // Cetak lanjutan
        Route::get('/manual-printed/pemesanan', [ImportController::class, 'printManualPemesanan'])
            ->name('manual-print-pemesanan');

        Route::get('/manual-printed/qc', [ImportController::class, 'printManualQC'])
            ->name('manual-print-qc');

        Route::get('/manual-printed/packing', [ImportController::class, 'printManualPacking'])
            ->name('manual-print-packing');

        Route::get('/manual-printed/ekspedisi', [ImportController::class, 'printManualEkspedisi'])
            ->name('manual-print-ekspedisi');

        Route::post('/manual/sync-no-ps', [ImportController::class, 'syncNoPsManualExisting'])
            ->name('manual.sync-no-ps');
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
        Route::get('/unit-aktif', [OrderController::class, 'unitAktif'])->name('unit-aktif');
        Route::get('/unit-pasif', [OrderController::class, 'unitPasif'])->name('unit-pasif');

        // =========================
        // JAKARTA AKTIF
        // =========================
        Route::get('/jakarta-aktif', [OrderController::class, 'jakartaAktif'])->name('jakarta-aktif');
        Route::get('/modul', [OrderController::class, 'modul'])->name('modul');
        Route::get('/majalah', [OrderController::class, 'majalah'])->name('majalah');
        Route::get('/sertifikat', [OrderController::class, 'sertifikat'])->name('sertifikat');

        Route::post('/jakarta-aktif/import', [OrderController::class, 'importJakartaAktif'])->name('jakarta-aktif.import');
        Route::post('/jakarta-aktif/sync-jkt', [OrderController::class, 'syncJktFromBimbashop'])->name('jakarta-aktif.sync-jkt');
        Route::post('/jakarta-aktif/bulk-action', [OrderController::class, 'bulkActionJakartaAktif'])->name('jakarta-aktif.bulk-action');
        Route::get('/jakarta-aktif/{id}/edit', [OrderController::class, 'editJakartaAktif'])->name('jakarta-aktif.edit');
        Route::put('/jakarta-aktif/{id}', [OrderController::class, 'updateJakartaAktif'])->name('jakarta-aktif.update');
        Route::get('/jakarta-aktif/filtered-ids', [OrderController::class, 'getFilteredIds'])->name('jakarta-aktif.filtered-ids');
        Route::post('/jakarta-aktif/get-modal-data', [OrderController::class, 'getModalData'])->name('jakarta-aktif.get-modal-data');
        Route::get('/jakarta-aktif/export', [OrderController::class, 'exportJakartaAktif'])->name('jakarta-aktif.export');

        // =========================
        // JAKARTA PASIF
        // =========================
        Route::get('/jakarta-pasif', [OrderController::class, 'jakartaPasif'])->name('jakarta-pasif');
        Route::post('/jakarta-pasif/import', [OrderController::class, 'importJakartaPasif'])->name('jakarta-pasif.import');
        Route::post('/jakarta-pasif/sync-jkt', [OrderController::class, 'syncJktPasifFromBimbashop'])->name('jakarta-pasif.sync-jkt');
        Route::post('/jakarta-pasif/bulk-action', [OrderController::class, 'bulkActionJakartaPasif'])->name('jakarta-pasif.bulk-action');
        Route::get('/jakarta-pasif/{id}/edit', [OrderController::class, 'editJakartaPasif'])->name('jakarta-pasif.edit');
        Route::put('/jakarta-pasif/{id}', [OrderController::class, 'updateJakartaPasif'])->name('jakarta-pasif.update');
        Route::get('/jakarta-pasif/filtered-ids', [OrderController::class, 'getFilteredIdsPasif'])->name('jakarta-pasif.filtered-ids');
        Route::post('/jakarta-pasif/get-modal-data', [OrderController::class, 'getModalDataPasif'])->name('jakarta-pasif.get-modal-data');
        Route::get('/jakarta-pasif/export', [OrderController::class, 'exportJakartaPasif'])->name('jakarta-pasif.export');

        // Picking List Pasif (DALAM group → URL = /order/jakarta-pasif/...)
        Route::get('/jakarta-pasif/picking-list/{id}', [OrderController::class, 'printPickingListPasif'])
            ->name('jakarta-pasif.picking-list');
        Route::get('/jakarta-pasif/picking-list-pdf/{id}', [OrderController::class, 'printPickingListPdfPasif'])
            ->name('jakarta-pasif.picking-list-pdf');
        Route::post('/jakarta-pasif/mark-picking-printed/{id}', [OrderController::class, 'markPickingPrintedPasif'])
            ->name('jakarta-pasif.mark-picking-printed');

        // =========================
        // REALISASI
        // =========================
        Route::get('/jakarta-printed', [OrderController::class, 'jakartaPrinted'])->name('jakarta-printed');
        Route::get('/jakarta-pasif-printed', [OrderController::class, 'jakartaPasifPrinted'])->name('jakarta-pasif-printed');

        Route::delete('/realisasi/{id}', [OrderController::class, 'deleteRealisasi'])->name('realisasi.delete');
        Route::post('/realisasi/mark-printed-all', [OrderController::class, 'markAllAsPrinted'])->name('realisasi.mark-printed-all');

        // =========================
        // PRINT REALISASI PASIF
        // =========================
        Route::get('/realisasi-pasif/print-pdf', [OrderController::class, 'printRealisasiPasifPdf'])
            ->name('realisasi-pasif.print-pdf');

        Route::get('/realisasi-pasif/print-pemesanan', [OrderController::class, 'printPemesananPasif'])
            ->name('realisasi-pasif.print-pemesanan');

        Route::get('/realisasi-pasif/print-qc', [OrderController::class, 'printQCPasif'])
            ->name('realisasi-pasif.print-qc');

        Route::get('/realisasi-pasif/print-packing', [OrderController::class, 'printPackingPasif'])
            ->name('realisasi-pasif.print-packing');

        Route::get('/realisasi-pasif/print-ekspedisi', [OrderController::class, 'printEkspedisiPasif'])
            ->name('realisasi-pasif.print-ekspedisi');

        // =========================
        // PRINT (AKTIF)
        // =========================
        Route::get('/realisasi/print-pdf', [OrderController::class, 'printRealisasiPdf'])->name('realisasi.print-pdf');
        Route::get('/realisasi/print-pdf/{id}', [OrderController::class, 'printSingleRealisasi'])->name('realisasi.print-single');
        Route::get('/realisasi/picking-list/{id}', [OrderController::class, 'printPickingList'])->name('realisasi.picking-list');
        Route::get('/picking-list/pdf/{id}', [OrderController::class, 'printPickingListPdf'])->name('picking-list.pdf');
        Route::get('/realisasi/print-qc', [OrderController::class, 'printQC'])->name('realisasi.print-qc');
        Route::get('/realisasi/print-pemesanan', [OrderController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
        Route::get('/realisasi/print-ekspedisi', [OrderController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');
        Route::get('/realisasi/print-packing', [OrderController::class, 'printPacking'])->name('realisasi.print-packing');
        Route::get('/realisasi/picking-list-all', [OrderController::class, 'printPickingListAll'])->name('print-picking-list-all');
        Route::get('/realisasi/print-qc-all', [OrderController::class, 'printQCAll'])->name('print-qc-all');
        Route::get('/realisasi/print-packing-all', [OrderController::class, 'printPackingAll'])->name('print-packing-all');
        Route::get('/realisasi/print-ekspedisi-all', [OrderController::class, 'printEkspedisiAll'])->name('print-ekspedisi-all');
        Route::get('/realisasi/print-ra-all', [OrderController::class, 'printRealisasiAll'])->name('print-ra-all');
    });

// Di luar group (path penuh)
Route::post('order/jakarta-aktif/sync-manual', [OrderController::class, 'syncManualToJakartaAktif'])
    ->name('order.jakarta-aktif.sync-manual')
    ->middleware('auth');

Route::post('order/jakarta-aktif/sync-pesanan-majalah', [OrderController::class, 'syncPesananMajalahToJakartaAktif'])
    ->name('order.jakarta-aktif.sync-pesanan-majalah')
    ->middleware('auth');

Route::post('pesanan-majalah/{pesananMajalah}/kirim-ke-jakarta-aktif', [PesananMajalahController::class, 'kirimKeJakartaAktif'])
    ->name('pesanan-majalah.kirim-ke-jakarta-aktif')
    ->middleware('auth');

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
    Route::get('/jakarta/pasif', [PickingController::class, 'jakartaPasif'])->name('jakarta.pasif');

    // ===== JAKARTA PASIF - UPDATE (perbaiki di sini) =====
    Route::post('/pasif/checklist', [PickingController::class, 'updateChecklistPasif'])->name('pasif.checklist');
    Route::post('/pasif/pic', [PickingController::class, 'updatePicPasif'])->name('pasif.pic');
    Route::post('/pasif/status', [PickingController::class, 'updateStatusPasif'])->name('pasif.status');
    
    // Checklist, PIC, Status (Normal / Aktif)
    Route::post('/checklist/update', [PickingController::class, 'updateChecklist'])->name('checklist.update');
    Route::post('/pic/update', [PickingController::class, 'updatePic'])->name('pic.update');
    Route::post('/status/update', [PickingController::class, 'updateStatus'])->name('status.update');

    // Order Manual (list)
    Route::get('/order-manual', [PickingController::class, 'orderManual'])->name('order-manual');

    // ===== MANUAL PICKING =====
    Route::post('/manual/checklist', [PickingController::class, 'updateChecklistManual'])->name('manual.checklist.update');
    Route::post('/manual/status', [PickingController::class, 'updateStatusManual'])->name('manual.status.update');
    Route::post('/manual/pic', [PickingController::class, 'updatePicManual'])->name('manual.pic.update');
});
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
         Route::get('/ops2', [DatabaseUserController::class, 'ops2'])->name('ops2.index');

// ====================== QC OUTGOING ======================
Route::prefix('qc-outgoing')
    ->name('qc-outgoing.')
    ->middleware('auth')
    ->group(function () {

        Route::get('/', [App\Http\Controllers\QcOutgoingController::class, 'index'])
            ->name('index');

        Route::get('/jakarta-aktif', [App\Http\Controllers\QcOutgoingController::class, 'jakartaAktif'])
            ->name('jakarta-aktif');
        
        Route::get('/jakarta-pasif', [QcOutgoingController::class, 'jakartaPasif'])->name('jakarta-pasif');

        Route::post('/pasif/store', [App\Http\Controllers\QcOutgoingController::class, 'storePasif'])
            ->name('pasif.store');

        Route::post('/store', [App\Http\Controllers\QcOutgoingController::class, 'qcStore'])
            ->name('store');

        // Order Manual
        Route::get('/order-manual', [App\Http\Controllers\QcOutgoingController::class, 'orderManual'])
            ->name('order-manual');

        Route::post('/manual/store', [App\Http\Controllers\QcOutgoingController::class, 'storeManual'])
            ->name('manual.store');

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

    Route::get('/jakarta-pasif', [PackingController::class, 'jakartaPasif'])
        ->name('packing.jakarta-pasif');

    // ← PASTIKAN BARIS INI ADA
    Route::put('/pasif/{id}', [PackingController::class, 'updatePasif'])
        ->name('packing.pasif.update');

    Route::put('/{id}', [PackingController::class, 'update'])
        ->name('packing.update');

    Route::post('/store', [PackingController::class, 'store'])
        ->name('packing.store');

    // ===== ORDER MANUAL =====
    Route::get('/order-manual', [PackingController::class, 'orderManual'])
        ->name('packing.order-manual');

    Route::put('/order-manual/{id}', [PackingController::class, 'updateManual'])
        ->name('packing.order-manual.update');
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

        // === MANUAL ===
        Route::get('/manual', [DistributionOrderController::class, 'manual'])->name('manual');
        Route::post('/manual/{id}/update', [DistributionOrderController::class, 'updateManual'])->name('manual.update');

        // === PASIF UPDATE (letakkan SEBELUM route {distributionOrder}) ===
        Route::put('/pasif/{id}', [DistributionOrderController::class, 'updatePasif'])
            ->name('pasif.update');

        // === ROUTE LAIN ===
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
Route::patch('/pesanan-majalah/{id}/update-no-ps', [\App\Http\Controllers\PesananMajalahController::class, 'updateNoPs'])
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

    // DATA ORDER MANUAL
    Route::get('/order-manual', [OrderManualController::class, 'index'])
    ->name('order-manual.index')
    ->middleware('auth');

Route::prefix('order-manual-modul')
    ->name('order-manual-modul.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', [OrderManualModulController::class, 'index'])->name('index');

        Route::get('/manual', [OrderManualModulController::class, 'manual'])->name('manual');
        Route::get('/manual/create', [OrderManualModulController::class, 'manualCreate'])->name('manual.create');
        Route::post('/manual', [OrderManualModulController::class, 'manualStore'])->name('manual.store');
        Route::get('/manual/{id}/edit', [OrderManualModulController::class, 'manualEdit'])->name('manual.edit');
        Route::put('/manual/{id}', [OrderManualModulController::class, 'manualUpdate'])->name('manual.update');

        Route::post('/manual/sync-bimbashop-casdana', [OrderManualModulController::class, 'runSyncModul'])
            ->name('manual.sync');

        Route::get('/manual/filtered-ids', [OrderManualModulController::class, 'getFilteredIds'])->name('manual.filtered-ids');
        Route::post('/manual/get-modal-data', [OrderManualModulController::class, 'getModalData'])->name('manual.get-modal-data');
        Route::post('/manual/bulk-action', [OrderManualModulController::class, 'bulkAction'])->name('manual.bulk-action');

        Route::get('/manual-printed', [OrderManualModulController::class, 'printed'])->name('manual-printed');
        Route::get('/search-products', [OrderManualModulController::class, 'searchProducts'])->name('search-products');

        // ✅ BENAR
        Route::get('/realisasi', [OrderManualModulController::class, 'realisasi'])->name('realisasi');
        Route::get('/realisasi', [OrderManualModulController::class, 'realisasi'])->name('realisasi');
        Route::get('/realisasi/picking-list/{id}', [OrderManualModulController::class, 'printPickingList'])->name('realisasi.picking-list');
        Route::get('/realisasi/print-prising', [OrderManualModulController::class, 'printPrising'])->name('realisasi.print-prising');
        Route::get('/realisasi/print-pemesanan', [OrderManualModulController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
        Route::get('/realisasi/print-qc', [OrderManualModulController::class, 'printQc'])->name('realisasi.print-qc');
        Route::get('/realisasi/print-packing', [OrderManualModulController::class, 'printPacking'])->name('realisasi.print-packing');
        Route::get('/realisasi/print-ekspedisi', [OrderManualModulController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');
    });

        Route::prefix('order-manual-sertifikat')->name('order-manual-sertifikat.')->group(function () {
            Route::get('/', [OrderManualSertifikatController::class, 'index'])->name('index');

            // List + CRUD
            Route::get('/manual', [OrderManualSertifikatController::class, 'manual'])->name('manual');
            Route::get('/manual/create', [OrderManualSertifikatController::class, 'manualCreate'])->name('manual.create');
            Route::post('/manual', [OrderManualSertifikatController::class, 'manualStore'])->name('manual.store');
            Route::get('/manual/{id}/edit', [OrderManualSertifikatController::class, 'manualEdit'])->name('manual.edit');
            Route::put('/manual/{id}', [OrderManualSertifikatController::class, 'manualUpdate'])->name('manual.update');

            // Bulk & modal
            Route::get('/manual/filtered-ids', [OrderManualSertifikatController::class, 'getFilteredIds'])->name('manual.filtered-ids');
            Route::post('/manual/get-modal-data', [OrderManualSertifikatController::class, 'getModalData'])->name('manual.get-modal-data');
            Route::post('/manual/bulk-action', [OrderManualSertifikatController::class, 'bulkAction'])->name('manual.bulk-action');

            // Sync & search
            Route::post('/manual/sync', [OrderManualSertifikatController::class, 'runSync'])->name('manual.sync');
            Route::get('/search-products', [OrderManualSertifikatController::class, 'searchProducts'])->name('search-products');

            // Realisasi
            Route::get('/realisasi', [OrderManualSertifikatController::class, 'realisasi'])->name('realisasi');

            // Print routes
            Route::get('/realisasi/print-prising', [OrderManualSertifikatController::class, 'printPrising'])->name('realisasi.print-prising');
            Route::get('/realisasi/print-pemesanan', [OrderManualSertifikatController::class, 'printPemesanan'])->name('realisasi.print-pemesanan');
            Route::get('/realisasi/print-qc', [OrderManualSertifikatController::class, 'printQc'])->name('realisasi.print-qc');
            Route::get('/realisasi/print-packing', [OrderManualSertifikatController::class, 'printPacking'])->name('realisasi.print-packing');
            Route::get('/realisasi/print-ekspedisi', [OrderManualSertifikatController::class, 'printEkspedisi'])->name('realisasi.print-ekspedisi');

            // Picking list (dipakai di confirmPrintPicking)
            Route::get('/realisasi/picking-list/{id}', [OrderManualSertifikatController::class, 'pickingList'])->name('realisasi.picking-list');
        });
            
