<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;

class ImportMenuController extends Controller
{

    /**
     * Halaman utama Data biMBA Shop
     */
    public function index()
    {
        return view('import::import.index');   // Halaman dengan kartu pilihan
    }
}
