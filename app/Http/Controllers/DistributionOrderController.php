<?php

namespace App\Http\Controllers;

use App\Models\DistributionOrder;
use Illuminate\Http\Request;

class DistributionOrderController extends Controller
{
    /**
     * Menu Distribution Order
     */
    public function index()
    {
        return view('distribution-order.index');
    }

    /**
     * Jakarta Aktif
     */
    public function jakartaAktif()
{
    $distributionOrders = DistributionOrder::with('packing')
        ->latest()
        ->get();

    return view('distribution-order.jakarta-aktif', compact('distributionOrders'));
}

    /**
     * Jakarta Pasif
     */
    public function jakartaPasif()
    {
        return view('distribution-order.jakarta-pasif');
    }

    /**
     * InterVio
     */
    public function intervio()
    {
        return view('distribution-order.intervio');
    }

    /**
     * English biMBA Talk
     */
    public function ebt()
    {
        return view('distribution-order.ebt');
    }
}