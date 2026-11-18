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
 * Sebaliknya, order Shop yang MASIH on-hold, dibayar lewat gateway (lunaz_payment_gateway),
 * dan tidak ada pembayaran sukses di Casdana setelah 1x24 jam, otomatis menjadi cancelled.
 *
 * Order yang dibatalkan otomatis (auto_cancelled_at terisi) akan kembali menjadi processing
 * bila pembayaran Casdana-nya muncul belakangan (mis. dibayar lewat 24 jam).
 *
 * Aman dijalankan berulang. Order processing / completed tidak pernah diturunkan statusnya,
 * dan order yang dibatalkan manual tidak pernah dihidupkan kembali.
 */
class CasdanaStatusSync
{
    /** Status Casdana yang dianggap sudah dibayar. */
    public const PAID_STATUSES = ['SUCCESS', 'SETTLED'];

    /** Status Shop yang boleh dinaikkan menjadi processing. */
    public const UPGRADABLE_FROM = ['on-hold', 'pending'];

    /** Metode bayar yang wajib muncul di Casdana. bacs / wallet / dll tidak pernah dibatalkan otomatis. */
    public const CANCEL_PAYMENT_METHODS = ['lunaz_payment_gateway'];

    /** Batas waktu tunggu pembayaran (jam). */
    public const CANCEL_AFTER_HOURS = 24;

    public static function normalizeInvoice($invoice): string
    {
        $v = strtoupper(trim((string) $invoice));

        return preg_replace('/^ID/', '', $v);
    }

    /**
     * @return array{casdana_sukses:int, order_diubah:int, order_dibatalkan:int}
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
        //    Berapa lama setelah order pembayaran masuk TIDAK dipersoalkan: selama Casdana
        //    SUCCESS/SETTLED, order jadi processing.
        foreach ($paidIds->chunk(500) as $chunk) {
            $changed += BimbashopOrder::query()
                ->whereIn('order_id', $chunk->all())
                ->whereIn('status', self::UPGRADABLE_FROM)
                ->update(['status' => 'processing']);

            // 2b. Order yang tadinya dibatalkan OTOMATIS (lewat 24 jam), lalu pembayarannya
            //     ternyata muncul di Casdana -> hidupkan kembali menjadi processing.
            //     Cancel manual (auto_cancelled_at kosong) tidak pernah dihidupkan.
            $changed += BimbashopOrder::query()
                ->whereIn('order_id', $chunk->all())
                ->where('status', 'cancelled')
                ->whereNotNull('auto_cancelled_at')
                ->update(['status' => 'processing', 'auto_cancelled_at' => null]);
        }

        // 3. Batalkan order on-hold yang tidak dibayar dalam 1x24 jam
        $cancelled = $this->cancelUnpaid($paidIds->flip());

        Log::info('CasdanaStatusSync: ' . $paidIds->count() . " Casdana sukses, {$changed} baris order Shop -> processing, {$cancelled} baris -> cancelled");

        return ['casdana_sukses' => $paidIds->count(), 'order_diubah' => $changed, 'order_dibatalkan' => $cancelled];
    }

    /**
     * on-hold + gateway + tidak ada Casdana sukses + order sudah lewat 24 jam -> cancelled.
     *
     * Pengaman: batas waktu juga dihitung dari impor Casdana terakhir. Kalau data Casdana
     * belum diimpor lagi setelah jendela 24 jam order itu lewat, order TIDAK dibatalkan
     * (bisa jadi pembayarannya ada, hanya belum diimpor).
     *
     * @param \Illuminate\Support\Collection $paidSet  key = order_id yang sudah dibayar
     * @return int jumlah baris order Shop yang diubah
     */
    private function cancelUnpaid($paidSet): int
    {
        $lastCasdana = CasdanaTransaction::query()->max('updated_at');
        if (! $lastCasdana) {
            return 0; // Casdana belum pernah diimpor -> jangan batalkan apa pun
        }

        $cutoff = \Illuminate\Support\Carbon::parse($lastCasdana);
        $cutoff = $cutoff->min(now())->subHours(self::CANCEL_AFTER_HOURS);

        $kandidat = BimbashopOrder::query()
            ->where('status', 'on-hold')
            ->whereIn('payment_method', self::CANCEL_PAYMENT_METHODS)
            ->whereNotNull('order_date')
            ->where('order_date', '<=', $cutoff)
            ->distinct()
            ->pluck('order_id')
            ->map(fn ($id) => (string) $id)
            ->filter(fn ($id) => $id !== '' && ! $paidSet->has($id))
            ->values();

        $changed = 0;
        foreach ($kandidat->chunk(500) as $chunk) {
            $changed += BimbashopOrder::query()
                ->whereIn('order_id', $chunk->all())
                ->where('status', 'on-hold')
                ->update(['status' => 'cancelled', 'auto_cancelled_at' => now()]);
        }

        return $changed;
    }
}