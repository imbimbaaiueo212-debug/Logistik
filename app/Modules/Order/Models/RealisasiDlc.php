<?php

namespace App\Modules\Order\Models;

class RealisasiDlc extends RealisasiRumahBaru
{
    protected $table = 'realisasi_dlc';

    protected string $headerClass = JakartaDlc::class;
}
