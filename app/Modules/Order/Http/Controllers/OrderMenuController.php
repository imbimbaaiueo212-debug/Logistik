<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;

class OrderMenuController extends Controller
{

    public function index()
    {
        return view('order::order.index');
    }

    public function unitAktif() { return view('order::order.unit-aktif'); }
    public function unitPasif() { return view('order::order.unit-pasif'); }
}
