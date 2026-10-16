<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (inti aplikasi)
|--------------------------------------------------------------------------
| Rute fitur ada di masing-masing modul: app/Modules/<Nama>/Routes/web.php,
| dimuat oleh <Nama>ServiceProvider (didaftarkan di bootstrap/providers.php).
| File ini hanya berisi rute inti: login/registrasi, home, dashboard, API kecil.
*/

require __DIR__.'/auth.php';          // login, logout, registrasi

// ====================== HOME & DASHBOARD ======================
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// ====================== API ======================
Route::get('/get-products/{supplier}', function ($supplierId) {
    $supplier = \App\Modules\Master\Models\Supplier::with('products')->find($supplierId);
    return response()->json($supplier?->products ?? []);
})->name('get-products');
