<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QcOutgoingController extends Controller
{
    public function index()
    {
        return view('qc-outgoing.index');
    }
}