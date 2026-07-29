<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderManualController extends Controller
{
    public function index()
    {
        return view('order-manual.index');
    }
}