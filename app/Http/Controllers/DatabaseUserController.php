<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatabaseUserController extends Controller
{
    public function index()
    {
        return view('database-user.index');
    }

    /**
     * Halaman OPS2 – pilih wilayah (KORWIL / PINWIL / JABODETABEK)
     */
    public function ops2()
    {
        return view('ops2.index');
    }
}