<?php

namespace App\Modules\OrderManual\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class OrderManualController extends Controller
{
    public function index()
    {
        return view('ordermanual::order-manual.index');
    }
}