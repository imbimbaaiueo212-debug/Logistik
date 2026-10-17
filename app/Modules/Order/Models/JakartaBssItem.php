<?php

namespace App\Modules\Order\Models;

class JakartaBssItem extends JakartaRumahBaruItem
{
    protected $table = 'jakarta_bss_items';

    protected string $headerClass = JakartaBss::class;
}
