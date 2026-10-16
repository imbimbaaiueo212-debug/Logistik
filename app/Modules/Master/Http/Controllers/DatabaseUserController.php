<?php

namespace App\Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class DatabaseUserController extends Controller
{
    public function index()
    {
        return view('master::database-user.index');
    }

    /**
     * Halaman OPS2 – pilih wilayah (KORWIL / PINWIL / JABODETABEK)
     */
    public function ops2()
    {
        return view('master::ops2.index');
    }
}