<?php

namespace App\Console\Commands;

use App\Modules\Import\Services\CasdanaStatusSync;
use Illuminate\Console\Command;

class SyncShopStatus extends Command
{
    protected $signature = 'shop:sync-status';

    protected $description = 'Sinkron status order biMBA Shop dengan Casdana: SUCCESS/SETTLED -> processing, on-hold tanpa pembayaran > 24 jam -> cancelled';

    public function handle(CasdanaStatusSync $sync): int
    {
        $r = $sync->sync();

        $this->info("Casdana sukses: {$r['casdana_sukses']} | -> processing: {$r['order_diubah']} baris | -> cancelled: {$r['order_dibatalkan']} baris");

        return self::SUCCESS;
    }
}
