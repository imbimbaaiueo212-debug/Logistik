<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatabaseUserController extends Controller
{
    public function index()
    {
        return view('database-user.index'); // sesuaikan dengan nama folder & file blade kamu
    }
}