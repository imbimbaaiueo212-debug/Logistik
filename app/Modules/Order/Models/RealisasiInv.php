<?php

namespace App\Modules\Order\Models;

class RealisasiInv extends RealisasiRumahBaru
{
    protected $table = 'realisasi_inv';

    protected string $headerClass = JakartaInv::class;
}
