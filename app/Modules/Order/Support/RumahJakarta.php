<?php

namespace App\Modules\Order\Support;

use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Order\Models\JakartaAktifItem;
use App\Modules\Order\Models\JakartaBss;
use App\Modules\Order\Models\JakartaBssItem;
use App\Modules\Order\Models\JakartaDlc;
use App\Modules\Order\Models\JakartaDlcItem;
use App\Modules\Order\Models\JakartaEbt;
use App\Modules\Order\Models\JakartaEbtItem;
use App\Modules\Order\Models\JakartaInv;
use App\Modules\Order\Models\JakartaInvItem;
use App\Modules\Order\Models\JakartaPasif;
use App\Modules\Order\Models\JakartaPasifItem;
use App\Modules\Order\Models\RealisasiAktif;
use App\Modules\Order\Models\RealisasiBss;
use App\Modules\Order\Models\RealisasiDlc;
use App\Modules\Order\Models\RealisasiEbt;
use App\Modules\Order\Models\RealisasiInv;
use App\Modules\Order\Models\RealisasiPasif;

/**
 * Peta rumah Jakarta -> model/tabelnya.
 *
 *   JKT  -> jakarta_aktif                 (+ items, realisasi_aktif)
 *   JKTP -> jakarta_pasif                 (+ items, realisasi_pasif)
 *   DLC  -> jakarta_dlc                   (+ items, realisasi_dlc)
 *   EBT  -> jakarta_ebt                   (+ items, realisasi_ebt)
 *   INV  -> jakarta_inv                   (+ items, realisasi_inv)
 *   BSS  -> jakarta_bss                   (+ items, realisasi_bss)
 *
 * Mau menambah rumah baru: tambah satu baris di PETA + satu migration + tiga model kecil.
 */
final class RumahJakarta
{
    /** Urutan tampil di filter / badge. */
    public const SEMUA = ['JKT', 'JKTP', 'DLC', 'EBT', 'INV', 'BSS'];

    /** Rumah yang memakai alur "pasif" (sync dari SKU, tanpa wajib Casdana). */
    public const PASIF = ['JKTP', 'DLC', 'EBT', 'INV', 'BSS'];

    /** Rumah yang punya tabel sendiri (bukan jakarta_aktif / jakarta_pasif). */
    public const BARU = ['DLC', 'EBT', 'INV', 'BSS'];

    private const PETA = [
        'JKT'  => ['header' => JakartaAktif::class, 'item' => JakartaAktifItem::class, 'realisasi' => RealisasiAktif::class, 'fk' => 'jakarta_aktif_id'],
        'JKTP' => ['header' => JakartaPasif::class, 'item' => JakartaPasifItem::class, 'realisasi' => RealisasiPasif::class, 'fk' => 'jakarta_pasif_id'],
        'DLC'  => ['header' => JakartaDlc::class,   'item' => JakartaDlcItem::class,   'realisasi' => RealisasiDlc::class,   'fk' => 'jakarta_id'],
        'EBT'  => ['header' => JakartaEbt::class,   'item' => JakartaEbtItem::class,   'realisasi' => RealisasiEbt::class,   'fk' => 'jakarta_id'],
        'INV'  => ['header' => JakartaInv::class,   'item' => JakartaInvItem::class,   'realisasi' => RealisasiInv::class,   'fk' => 'jakarta_id'],
        'BSS'  => ['header' => JakartaBss::class,   'item' => JakartaBssItem::class,   'realisasi' => RealisasiBss::class,   'fk' => 'jakarta_id'],
    ];

    public static function normal($rumah): string
    {
        return strtoupper(trim((string) $rumah));
    }

    public static function valid($rumah): bool
    {
        return isset(self::PETA[self::normal($rumah)]);
    }

    public static function punyaTabelSendiri($rumah): bool
    {
        return in_array(self::normal($rumah), self::BARU, true);
    }

    /** Kelas model header order (jakarta_*). */
    public static function header($rumah): string
    {
        return self::PETA[self::normal($rumah)]['header'];
    }

    /** Kelas model item order (jakarta_*_items). */
    public static function item($rumah): string
    {
        return self::PETA[self::normal($rumah)]['item'];
    }

    /** Kelas model Rekap Aktual (realisasi_*). */
    public static function realisasi($rumah): string
    {
        return self::PETA[self::normal($rumah)]['realisasi'];
    }

    /** Nama kolom penghubung item / realisasi ke header. */
    public static function fk($rumah): string
    {
        return self::PETA[self::normal($rumah)]['fk'];
    }

    /** Nama tabel header order. */
    public static function tabel($rumah): string
    {
        $kelas = self::header($rumah);

        return (new $kelas)->getTable();
    }
}
