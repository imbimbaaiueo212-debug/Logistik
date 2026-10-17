<?php

namespace App\Modules\Order\Models;

class RealisasiEbt extends RealisasiRumahBaru
{
    protected $table = 'realisasi_ebt';

    protected string $headerClass = JakartaEbt::class;
}
