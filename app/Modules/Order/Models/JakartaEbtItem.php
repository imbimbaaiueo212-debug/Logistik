<?php

namespace App\Modules\Order\Models;

class JakartaEbtItem extends JakartaRumahBaruItem
{
    protected $table = 'jakarta_ebt_items';

    protected string $headerClass = JakartaEbt::class;
}
