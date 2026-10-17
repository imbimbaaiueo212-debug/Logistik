<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dasar untuk rumah yang punya tabel sendiri (DLC, EBT, INV, BSS).
 * Struktur kolom sama dengan jakarta_pasif; bedanya kolom penghubung bernama jakarta_id.
 * Subclass cukup menentukan $table, $itemClass dan $realisasiClass.
 */
abstract class JakartaRumahBaru extends JakartaPasif
{
    protected string $itemClass;
    protected string $realisasiClass;

    public function items(): HasMany
    {
        return $this->hasMany($this->itemClass, 'jakarta_id');
    }

    public function realisasi()
    {
        return $this->hasMany($this->realisasiClass, 'jakarta_id');
    }
}
