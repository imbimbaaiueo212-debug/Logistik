<?php

namespace App\Modules\Order\Models;

class JakartaDlcItem extends JakartaRumahBaruItem
{
    protected $table = 'jakarta_dlc_items';

    protected string $headerClass = JakartaDlc::class;
}
