<?php

namespace App\Modules\Import\Services;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\DlcPeriode;
use App\Modules\Order\Support\NamaDlc;

/**
 * Rekap per qty pesanan Majalah DLC terhadap biMBA Shop.
 *
 *   proses = total qty Shop untuk unit & edisi itu (semua yang masuk)
 *   sisa   = qty pesan - proses   (selisih; boleh MINUS bila Shop melebihi pesanan)
 *
 * Status: Belum masuk (Shop 0) / Kurang (sisa > 0) / Sesuai (sisa = 0) / Lebih (sisa < 0)
 *
 * Order Shop DLC dibuat per murid; unitnya dibaca dari ujung Billing Name
 * (lihat NamaDlc). Dihitung ulang tiap dibuka, jadi aman diulang (idempotent).
 */
class DlcRekapMajalah
{
    /**
     * @return array{
     *   baris: array<int, array<string,mixed>>,
     *   tidak_dikenali: array<int, array<string,mixed>>,
     *   total: array{pesan:int, proses:int, sisa:int}
     * }
     */
    public function hitung(DlcPeriode $periode): array
    {
        $edisi = strtoupper(trim((string) $periode->edisi));
        $unit  = $periode->pesanan->pluck('nama_unit')->map(fn ($u) => trim((string) $u))->filter()->values()->all();

        // qty Shop per kunci unit, dan daftar order yang unitnya tidak dikenali
        $perUnit = [];
        $tidakDikenali = [];

        foreach ($this->orderShop($edisi) as $r) {
            $nama  = trim(trim((string) $r->billing_first_name) . ' ' . trim((string) $r->billing_last_name));
            $pisah = NamaDlc::pisah($nama, $unit);
            $qty   = max(0, (int) $r->item_qty);

            if ($pisah['unit'] !== null) {
                $k = NamaDlc::kunciUnit($pisah['unit']);
                $perUnit[$k]['qty']    = ($perUnit[$k]['qty'] ?? 0) + $qty;
                $perUnit[$k]['order'][$r->order_id] = true;
                continue;
            }

            // Bukan unit DLC periode ini. Hanya tampilkan bila memang order DLC (SKU memuat DLC),
            // supaya order majalah PUW / Kab / Kota edisi yang sama tidak membanjiri daftar.
            if ($this->skuDlc($r->item_sku)) {
                $tidakDikenali[] = [
                    'order_id'      => $r->order_id,
                    'billing_name'  => $nama,
                    'qty'           => $qty,
                    'sku'           => $r->item_sku,
                ];
            }
        }

        $baris = [];
        $total = ['pesan' => 0, 'proses' => 0, 'sisa' => 0];

        foreach ($periode->pesanan as $p) {
            $k      = NamaDlc::kunciUnit($p->nama_unit);
            $pesan  = (int) $p->qty;
            $proses = (int) ($perUnit[$k]['qty'] ?? 0);
            $sisa   = $pesan - $proses;          // selisih, boleh minus

            $status = match (true) {
                $proses === 0 => 'Belum masuk',
                $sisa > 0     => 'Kurang',
                $sisa === 0   => 'Sesuai',
                default       => 'Lebih',
            };

            $baris[$p->id] = [
                'pesan'  => $pesan,
                'proses' => $proses,
                'sisa'   => $sisa,
                'order'  => count($perUnit[$k]['order'] ?? []),
                'status' => $status,
            ];

            $total['pesan']  += $pesan;
            $total['proses'] += $proses;
            $total['sisa']   += $sisa;
        }

        return ['baris' => $baris, 'tidak_dikenali' => $tidakDikenali, 'total' => $total];
    }

    /** Baris Shop untuk satu edisi, tanpa order batal, tanpa duplikat baris. */
    private function orderShop(string $edisi)
    {
        if ($edisi === '') {
            return collect();
        }

        return BimbashopOrder::query()
            ->where(function ($q) use ($edisi) {
                $q->where('item_sku', 'like', "%{$edisi}%")->orWhere('item_name', 'like', "%{$edisi}%");
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere(function ($q2) {
                    $q2->where('status', 'not like', '%cancel%')
                       ->where('status', 'not like', '%fail%')
                       ->where('status', 'not like', '%refund%');
                });
            })
            ->get(['order_id', 'item_sku', 'item_name', 'item_qty', 'billing_first_name', 'billing_last_name'])
            // edisi harus persis (M162, bukan M16 / M1620)
            ->filter(fn ($r) => strtoupper((string) ShopMatcher::edisiFrom($r->item_sku, $r->item_name)) === $edisi)
            // file Shop yang terimport dua kali tidak boleh menggandakan qty
            ->unique(fn ($r) => $r->order_id . '|' . $r->item_sku . '|' . $r->item_name . '|' . $r->item_qty)
            ->values();
    }

    private function skuDlc(?string $sku): bool
    {
        return (bool) preg_match('/(^|[^A-Z])DLC([^A-Z]|$)/', strtoupper((string) $sku));
    }
}
