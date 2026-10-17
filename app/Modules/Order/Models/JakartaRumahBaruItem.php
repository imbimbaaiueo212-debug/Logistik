<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Item order untuk rumah bertabel sendiri. Subclass menentukan $table dan $headerClass. */
abstract class JakartaRumahBaruItem extends JakartaPasifItem
{
    protected string $headerClass;

    public function jakartaPasif(): BelongsTo
    {
        return $this->belongsTo($this->headerClass, 'jakarta_id');
    }
}
