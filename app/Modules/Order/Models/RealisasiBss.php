<?php

namespace App\Modules\Order\Models;

class RealisasiBss extends RealisasiRumahBaru
{
    protected $table = 'realisasi_bss';

    protected string $headerClass = JakartaBss::class;
}
