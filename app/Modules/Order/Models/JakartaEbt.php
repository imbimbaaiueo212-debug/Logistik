<?php

namespace App\Modules\Order\Models;

class JakartaEbt extends JakartaRumahBaru
{
    protected $table = 'jakarta_ebt';

    protected string $itemClass      = JakartaEbtItem::class;
    protected string $realisasiClass = RealisasiEbt::class;
}
