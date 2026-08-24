<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingPasif extends Model
{
    protected $table = 'packing_pasif';

    protected $fillable = [
    'picking_pasif_id',
    'qc_outgoing_pasif_id',
    'no_pl',
    'no_ps',
    'tgl_turun_pl',
    'nama_unit',
    'pengiriman',
    'nama_barang',
    'tgl_bayar',
    'jumlah_bayar',
    'tgl_estimasi',
    'berat',
    'berat_aktual',
    'koli',
    'tgl_packing',          // ← wajib
    'status_packing',
    'pic_packing',
    'nama_packer',          // ← wajib
    'packing_at',
    'packing_by',
    'kode_packing',         // ← wajib
    'keterangan',
    'keterangan_packing',   // ← wajib
    'created_by',
];

protected $casts = [
    'tgl_turun_pl'  => 'date',
    'tgl_bayar'     => 'date',
    'tgl_estimasi'  => 'date',
    'tgl_packing'   => 'date',
    'packing_at'    => 'datetime',
    'jumlah_bayar'  => 'decimal:2',
    'berat'         => 'decimal:2',
    'berat_aktual'  => 'decimal:3',   // ← ubah jadi 3
];

    // ====================== RELATIONS ======================

    public function pickingPasif(): BelongsTo
    {
        return $this->belongsTo(PickingPasif::class, 'picking_pasif_id');
    }

    public function qcOutgoingPasif(): BelongsTo
    {
        return $this->belongsTo(QcOutgoingPasif::class, 'qc_outgoing_pasif_id');
    }

    public function packingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packing_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getBeratBimbashopAttribute()
{
    // 1. Prioritas: sudah tersimpan di packing
    if (!empty($this->berat) && $this->berat > 0) {
        return (float) $this->berat;
    }

    // 2. Kumpulkan kemungkinan Order ID
    $orderIds = array_filter([
        $this->no_pl,
        $this->pickingPasif?->id_pesan ?? null,
        $this->pickingPasif?->no_pl ?? null,
    ]);

    if (empty($orderIds)) {
        return null;
    }

    // 3. Cari data biMBA Shop
    $bimbashop = \App\Models\BimbashopOrder::whereIn('order_id', $orderIds)->first();

    if (!$bimbashop) {
        return null;
    }

    // 4. Ambil dari kolom yang benar: order_weight
    $berat = $bimbashop->order_weight
          ?? $bimbashop->berat
          ?? $bimbashop->weight
          ?? null;

    // 5. Bersihkan jika masih string (contoh: "19 gr")
    if (is_string($berat)) {
        $berat = (float) preg_replace('/[^0-9.]/', '', $berat);
    }

    return $berat > 0 ? (float) $berat : null;
}
}