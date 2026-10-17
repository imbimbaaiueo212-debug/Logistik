<?php

namespace App\Modules\Order\Models;

/**
 * Rekap Aktual untuk rumah bertabel sendiri. Subclass menentukan $table dan $headerClass.
 * Picking belum tersambung untuk rumah ini, jadi relasi pickingPasif sengaja dikosongkan
 * (supaya tidak salah menempel ke picking_pasif milik JKTP yang id-nya bisa sama).
 */
abstract class RealisasiRumahBaru extends RealisasiPasif
{
    protected string $headerClass;

    public function __construct(array $attributes = [])
    {
        $this->mergeFillable(['jakarta_id']);

        parent::__construct($attributes);
    }

    public function jakartaPasif()
    {
        return $this->belongsTo($this->headerClass, 'jakarta_id');
    }

    public function pickingPasif()
    {
        return $this->hasOne(\App\Modules\Picking\Models\PickingPasif::class, 'realisasi_pasif_id')->whereRaw('1 = 0');
    }
}
