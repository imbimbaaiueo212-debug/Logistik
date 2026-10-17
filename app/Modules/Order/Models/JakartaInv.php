<?php

namespace App\Modules\Order\Models;

class JakartaInv extends JakartaRumahBaru
{
    protected $table = 'jakarta_inv';

    protected string $itemClass      = JakartaInvItem::class;
    protected string $realisasiClass = RealisasiInv::class;
}
