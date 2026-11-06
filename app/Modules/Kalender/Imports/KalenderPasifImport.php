<?php

namespace App\Modules\Kalender\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Import rekap pemesanan kalender Unit Pasif.
 *
 * Hanya sheet PERTAMA yang dibaca. Sheet lain (mis. "Sheet1" berisi VLOOKUP ke sheet data)
 * dilewati: tidak dimuat, jadi rumus lintas-sheet tidak dihitung dan tidak ikut jadi data unit.
 * Aturan kolom ada di KalenderPasifSheetImport.
 */
class KalenderPasifImport implements WithMultipleSheets
{
    protected $pasifPeriodeId;
    protected $bacaanPeriodeId;

    public function __construct($pasifPeriodeId, $bacaanPeriodeId = null)
    {
        $this->pasifPeriodeId  = $pasifPeriodeId;
        $this->bacaanPeriodeId = $bacaanPeriodeId;
    }

    public function sheets(): array
    {
        return [
            0 => new KalenderPasifSheetImport($this->pasifPeriodeId, $this->bacaanPeriodeId),
        ];
    }
}
