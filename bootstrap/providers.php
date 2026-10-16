<?php

use App\Modules\Distribusi\Providers\DistribusiServiceProvider;
use App\Modules\Import\Providers\ImportServiceProvider;
use App\Modules\Keuangan\Providers\KeuanganServiceProvider;
use App\Modules\Majalah\Providers\MajalahServiceProvider;
use App\Modules\Master\Providers\MasterServiceProvider;
use App\Modules\Order\Providers\OrderServiceProvider;
use App\Modules\OrderManual\Providers\OrderManualServiceProvider;
use App\Modules\Packing\Providers\PackingServiceProvider;
use App\Modules\Pengadaan\Providers\PengadaanServiceProvider;
use App\Modules\Picking\Providers\PickingServiceProvider;
use App\Modules\Qc\Providers\QcServiceProvider;
use App\Modules\Stok\Providers\StokServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    // Modul mandiri (app/Modules/<Nama>). Urutan = urutan pendaftaran rute.
    MasterServiceProvider::class,
    ImportServiceProvider::class,
    OrderServiceProvider::class,
    MajalahServiceProvider::class,
    PickingServiceProvider::class,
    PengadaanServiceProvider::class,
    StokServiceProvider::class,
    DistribusiServiceProvider::class,
    QcServiceProvider::class,
    PackingServiceProvider::class,
    OrderManualServiceProvider::class,
    KeuanganServiceProvider::class,
];
