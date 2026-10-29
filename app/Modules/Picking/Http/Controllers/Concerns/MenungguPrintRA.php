<?php

namespace App\Modules\Picking\Http\Controllers\Concerns;

/**
 * Picking / Persiapan sudah tampil begitu data masuk Rekap Aktual (RA),
 * tetapi TERKUNCI (tidak bisa dicek, diubah statusnya, diganti PIC, atau dihapus)
 * sampai RA-nya di-print (printed_at terisi).
 *
 * Berlaku untuk Jakarta Aktif, Jakarta Pasif (JKTP/DLC/EBT/INV/BSS), Logistik Aktif, Logistik Pasif.
 */
trait MenungguPrintRA
{
    /** True bila RA ada tetapi belum di-print. Picking lama tanpa RA ($ra = null) tidak dikunci. */
    protected function raBelumPrint($ra): bool
    {
        return $ra !== null && $ra->printed_at === null;
    }

    protected function pesanTerkunci(): string
    {
        return 'Picking ini terkunci. Selesaikan dulu print di Rekap Aktual, setelah itu baru bisa diubah.';
    }

    protected function responTerkunci()
    {
        return response()->json([
            'success' => false,
            'message' => $this->pesanTerkunci(),
        ], 423);
    }
}
