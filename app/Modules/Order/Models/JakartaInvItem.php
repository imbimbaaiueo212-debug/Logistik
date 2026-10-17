<?php

namespace App\Modules\Order\Models;

class JakartaInvItem extends JakartaRumahBaruItem
{
    protected $table = 'jakarta_inv_items';

    protected string $headerClass = JakartaInv::class;
}
