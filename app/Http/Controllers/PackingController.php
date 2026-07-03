<?php

namespace App\Http\Controllers;

use App\Models\Packing;
use App\Models\QcOutgoing;

class PackingController extends Controller
{
    public function index()
    {
        return view('packing.index');
    }

    public function jakartaAktif()
    {
        $data = QcOutgoing::latest()->paginate(20);

        return view('packing.jakarta-aktif', compact('data'));
    }
}