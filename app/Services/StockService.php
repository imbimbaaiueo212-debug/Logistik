<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Core movement function (SEMUA lewat sini)
     */
    public function move(
        int $productId,
        int $warehouseId,
        int $qty,
        string $type,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null
    ) {
        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $qty,
            $type,
            $refType,
            $refId,
            $notes
        ) {

            // 🔥 AUTO arah qty (+ / -)
            $qty = StockMovementType::normalizeQty($type, $qty);

            // Ambil atau buat stock
            $stock = Stock::lockForUpdate()->firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId
                ],
                ['qty' => 0]
            );

            $before = $stock->qty;
            $after = $before + $qty;

            // ❗ Validasi stok minus
            if (StockMovementType::isOut($type) && $after < 0) {
                throw new \Exception("Stock tidak mencukupi untuk produk ID {$productId}");
            }

            // Update stock
            $stock->update([
                'qty' => $after
            ]);

            // Simpan movement
            StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'qty' => $qty,
                'stock_before' => $before,
                'stock_after' => $after,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'user_id' => auth()->id(),
            ]);

            return $after;
        });
    }

    /**
     * Helper: Goods Receipt
     */
    public function stockIn($productId, $warehouseId, $qty, $refId = null)
    {
        return $this->move(
            $productId,
            $warehouseId,
            $qty,
            StockMovementType::IN,
            'GR',
            $refId
        );
    }

    /**
     * Helper: Distribusi / keluar
     */
    public function stockOut($productId, $warehouseId, $qty, $refId = null)
    {
        return $this->move(
            $productId,
            $warehouseId,
            $qty,
            StockMovementType::OUT,
            'DISTRIBUTION',
            $refId
        );
    }

    /**
     * Helper: Transfer antar gudang
     */
    public function transfer($productId, $fromWarehouse, $toWarehouse, $qty, $refId = null)
    {
        DB::transaction(function () use (
            $productId,
            $fromWarehouse,
            $toWarehouse,
            $qty,
            $refId
        ) {

            // 🔻 keluar dari gudang asal
            $this->move(
                $productId,
                $fromWarehouse,
                $qty,
                StockMovementType::TRANSFER_OUT,
                'TRANSFER',
                $refId
            );

            // 🔺 masuk ke gudang tujuan
            $this->move(
                $productId,
                $toWarehouse,
                $qty,
                StockMovementType::TRANSFER_IN,
                'TRANSFER',
                $refId
            );
        });
    }

    /**
     * Helper: Adjustment (bisa + atau -)
     */
    public function adjustment(array $data)
{
    $qty = $data['type'] === 'in'
        ? abs($data['qty'])
        : -abs($data['qty']);

    return $this->move(
        $data['product_id'],
        $data['warehouse_id'],
        $qty,
        StockMovementType::ADJUSTMENT,
        $data['reference_type'] ?? 'stock_opname',
        $data['reference_id'] ?? null,
        $data['note'] ?? 'Stock Opname Adjustment'
    );
}
}