<?php

namespace App\Http\Controllers;

use App\Models\JakartaAktif;
use App\Models\JakartaPasif;
use App\Models\ManualOrder;
use App\Models\ManualModulOrder;
use App\Models\ManualSertifikatOrder;
use App\Models\QcOutgoing;
use App\Models\QcOutgoingPasif;
use App\Models\ManualQcOutgoing;
use App\Models\DistributionOrder;
use App\Models\DistributionPasif;
use App\Models\ManualDistributionOrder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // ===== 1. ORDER HARI INI =====
        // Order baru yang masuk hari ini dari: Aktif, Pasif, dan 3 kategori Order Manual
        // (Majalah, Modul, Sertifikat) - dihitung dari tabel ORDER-nya langsung,
        // bukan dari tabel picking, supaya order langsung kehitung begitu dibuat.
        $orderAktifHariIni = JakartaAktif::whereDate('tgl_pesan', today())->count();
        $orderPasifHariIni = JakartaPasif::whereDate('tgl_pesan', today())->count();

        $orderMajalahHariIni    = ManualOrder::whereDate('order_date', today())->count();
        $orderModulHariIni      = ManualModulOrder::whereDate('order_date', today())->count();
        $orderSertifikatHariIni = ManualSertifikatOrder::whereDate('order_date', today())->count();

        $orderHariIni = $orderAktifHariIni + $orderPasifHariIni
            + $orderMajalahHariIni + $orderModulHariIni + $orderSertifikatHariIni;

        // ===== 2. SIAP DIKIRIM =====
        // Sudah selesai packing tapi belum di-pickup / belum dikirim
        $siapKirimAktif = DistributionOrder::where('status_pengiriman', 'belum_pickup')->count();
        $siapKirimPasif = DistributionPasif::where('status_pengiriman', 'belum_pickup')->count();
        $siapKirimManual = ManualDistributionOrder::where('status_distribusi', 'Pending')->count();

        $siapKirim = $siapKirimAktif + $siapKirimPasif + $siapKirimManual;

        // ===== 3. DALAM QC =====
        // Masih berstatus Pending di tahap QC (belum Lolos/Reject/Revisi)
        $qcAktif = QcOutgoing::where('status_qc', 'Pending')->count();
        $qcPasif = QcOutgoingPasif::where('status_qc', 'Pending')->count();
        $qcManual = ManualQcOutgoing::where('status_qc', 'Pending')->count();

        $prosesQc = $qcAktif + $qcPasif + $qcManual;

        $stats = [
            'order_hari_ini' => $orderHariIni,
            'siap_kirim'     => $siapKirim,
            'proses_qc'      => $prosesQc,
        ];

        return view('home', compact('stats'));
    }
}