<?php

namespace App\Enums;

class StockMovementType
{
    const IN = 'IN';
    const OUT = 'OUT';
    const TRANSFER_IN = 'TRANSFER_IN';
    const TRANSFER_OUT = 'TRANSFER_OUT';
    const ADJUSTMENT = 'ADJUSTMENT';
    const RETURN = 'RETURN';

    /**
     * Semua type yang MENAMBAH stok
     */
    public static function inTypes()
    {
        return [
            self::IN,
            self::TRANSFER_IN,
            self::RETURN,
        ];
    }

    /**
     * Semua type yang MENGURANGI stok
     */
    public static function outTypes()
    {
        return [
            self::OUT,
            self::TRANSFER_OUT,
        ];
    }

    /**
     * Cek apakah type menambah stok
     */
    public static function isIn($type)
    {
        return in_array($type, self::inTypes());
    }

    /**
     * Cek apakah type mengurangi stok
     */
    public static function isOut($type)
    {
        return in_array($type, self::outTypes());
    }

    /**
     * 🔥 AUTO arah qty (+ / -)
     */
    public static function normalizeQty($type, $qty)
    {
        if (self::isOut($type)) {
            return -abs($qty);
        }

        if (self::isIn($type)) {
            return abs($qty);
        }

        // ADJUSTMENT bebas
        return $qty;
    }

    /**
     * Label untuk UI (opsional tapi recommended)
     */
    public static function label($type)
    {
        return match ($type) {
            self::IN => 'Stock Masuk',
            self::OUT => 'Stock Keluar',
            self::TRANSFER_IN => 'Transfer Masuk',
            self::TRANSFER_OUT => 'Transfer Keluar',
            self::ADJUSTMENT => 'Penyesuaian',
            self::RETURN => 'Retur',
            default => $type,
        };
    }

    /**
     * Warna badge (untuk UI)
     */
    public static function badge($type)
    {
        return match ($type) {
            self::IN => 'success',
            self::TRANSFER_IN => 'primary',
            self::RETURN => 'info',

            self::OUT => 'danger',
            self::TRANSFER_OUT => 'warning',

            self::ADJUSTMENT => 'secondary',

            default => 'dark',
        };
    }
}