<?php

namespace App\Modules\Order\Models;

class JakartaDlc extends JakartaRumahBaru
{
    protected $table = 'jakarta_dlc';

    protected string $itemClass      = JakartaDlcItem::class;
    protected string $realisasiClass = RealisasiDlc::class;
}
