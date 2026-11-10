<?php

namespace App\Modules\Import\Services;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use Illuminate\Support\Facades\Log;

/**
 * Jika transaksi Casdana berstatus SUCCESS / SETTLED, order biMBA Shop dengan
 * order_id yang sama otomatis berubah dari on-hold (atau pending) menjadi processing.
 *
 * Pencocokan: Casdana invoice_number "ID877596" == biMBA Shop order_id "877596"
 * (prefix "ID" dibuang, cocok persis -- tidak memakai LIKE supaya 877596 tidak
 * ikut mencocokkan 1877596).
 *
 * Aman dijalankan berulang. Order yang sudah processing / completed / cancelled
 * tidak disentuh (tidak pernah diturunkan statusnya).
 */
class CasdanaStatusSync
{
    /** Status Casdana yang dianggap sudah dibayar. */
    public const PAID_STATUSES = ['SUCCESS', 'SETTLED'];

    /** Status Shop yang boleh dinaikkan menjadi processing. */
    public const UPGRADABLE_FROM = ['on-hold', 'pending'];

    public static function normalizeInvoice($invoice): string
    {
        $v = strtoupper(trim((string) $invoice));

        return preg_replace('/^ID/', '', $v);
    }

    /**
     * @return array{casdana_sukses:int, order_diubah:int}
     */
    public function sync(): array
    {
        // 1. Semua invoice Casdana yang sudah sukses, dinormalkan (tanpa prefix ID)
        $paidIds = CasdanaTransaction::query()
            ->whereIn('status', self::PAID_STATUSES)
            ->pluck('invoice_number')
            ->map(fn ($inv) => self::normalizeInvoice($inv))
            ->filter()
            ->unique()
            ->values();

        $changed = 0;

        // 2. Naikkan order Shop yang cocok. Satu order bisa punya banyak baris item,
        //    semuanya ikut berubah karena filter memakai order_id.
        foreach ($paidIds->chunk(500) as $chunk) {
            $changed += BimbashopOrder::query()
                ->whereIn('order_id', $chunk->all())
                ->whereIn('status', self::UPGRADABLE_FROM)
                ->update(['status' => 'processing']);
        }

        Log::info('CasdanaStatusSync: ' . $paidIds->count() . " Casdana sukses, {$changed} baris order Shop -> processing");

        return ['casdana_sukses' => $paidIds->count(), 'order_diubah' => $changed];
    }
}
