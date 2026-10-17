<?php

namespace App\Modules\Order\Models;

class JakartaBss extends JakartaRumahBaru
{
    protected $table = 'jakarta_bss';

    protected string $itemClass      = JakartaBssItem::class;
    protected string $realisasiClass = RealisasiBss::class;
}
