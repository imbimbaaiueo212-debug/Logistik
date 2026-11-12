<?php

namespace App\Modules\PengeluaranLogistik\Services;

use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Master\Models\Product;
use App\Modules\PengeluaranLogistik\Models\TransferGudangManual;
use App\Modules\PengeluaranLogistik\Support\DaftarStokis;
use Carbon\Carbon;

/**
 * Report Pengeluaran Logistik (selain Kalender dan Majalah, keduanya punya pos sendiri).
 *
 *  1. Logistik -> Stokis : order biMBA Shop ber-SKU LG. Stokis dikenali dari nama billing "Stokis <nama> <no>.S".
 *  2. Stokis -> Unit     : order biMBA Shop yang SKU-nya memuat kode stokis (mis. MDL01-DPK).
 *                          Unit = Billing Name, Billing Company dipakai sebagai referensi (mis. S0024).
 *  3. Transfer Gudang    : input manual, hanya qty, tanpa nominal uang.
 *
 * Yang dihitung hanya order berstatus processing / completed (sudah dibayar). Tidak memakai syarat Casdana,
 * karena order unit ke stokis dibayar di luar Casdana (cod / wallet / other).
 */
class LaporanPengeluaranLogistik
{
    private const STATUS_DIBAYAR = ['processing', 'completed'];

    /** @var array<string,string> label produk (kode awal SKU) => kategori */
    private array $kategoriPerLabel = [];

    /** @var array<string,array{nama:string,kategori:string}> */
    private array $produkPerLabel = [];

    /** @var array<string,string> nama produk (huruf besar) => kategori */
    private array $kategoriPerNama = [];

    public function build(?Carbon $dari, ?Carbon $sampai, ?string $filterStokis = null, ?string $filterKategori = null): array
    {
        $this->muatProduk();

        $namaStokis = DaftarStokis::PASIF + DaftarStokis::AKTIF + DaftarStokis::INVENTARIS;   // untuk nama billing "Stokis ..."
        uasort($namaStokis, fn ($a, $b) => strlen($b) <=> strlen($a));
        $kodeStokis = array_keys(DaftarStokis::semua());

        $lg    = [];   // [kode] => ['qty','order'=>set,'kategori'=>[kat=>['qty','produk'=>[key=>[...]]]]]
        $unit  = [];   // [kode][unitKey] => ['nama','ref','qty','order'=>set,'kategori'=>[kat=>qty]]
        $tidakDikenal = [];   // order LG yang stokisnya tidak terbaca dari nama billing
        $kategoriSemua = [];

        $query = BimbashopOrder::query()
            ->whereNotNull('item_sku')
            ->where('item_sku', '!=', '')
            ->where(function ($q) {
                $q->where('status', 'like', '%processing%')->orWhere('status', 'like', '%completed%');
            });

        if ($dari) {
            $query->where('order_date', '>=', $dari->copy()->startOfDay());
        }
        if ($sampai) {
            $query->where('order_date', '<=', $sampai->copy()->endOfDay());
        }

        $kolom = ['order_id', 'order_date', 'status', 'billing_first_name', 'billing_last_name',
            'billing_company', 'item_sku', 'item_name', 'item_qty'];

        foreach ($query->get($kolom) as $o) {
            $status = preg_replace('/^wc-/', '', strtolower(trim((string) $o->status)));
            if (! in_array($status, self::STATUS_DIBAYAR, true)) {
                continue;
            }

            $sku = strtoupper(trim((string) $o->item_sku));
            if ($this->kalenderAtauMajalah($sku, (string) $o->item_name)) {
                continue;   // punya pos sendiri
            }

            $token = DaftarStokis::token($sku);
            if (array_intersect($token, DaftarStokis::BUKAN_STOKIS)) {
                continue;   // rumah Jakarta, bukan logistik
            }

            $qty = max((int) $o->item_qty, 0);
            if ($qty === 0) {
                continue;
            }

            [$kategori, $namaProduk, $labelProduk] = $this->infoProduk($sku, (string) $o->item_name);

            if ($filterKategori !== null && $filterKategori !== '' && $kategori !== $filterKategori) {
                continue;
            }

            // Order ke logistik = SKU berkode LG, ATAU nama billing berpola "Stokis <nama>"
            // (ada order ke logistik yang SKU-nya tidak berformat LG).
            $kodeDariNama = $this->stokisDariNama($o, $namaStokis);

            if (in_array('LG', $token, true) || $kodeDariNama !== null) {
                // ---- Logistik -> Stokis
                $kode = $kodeDariNama;
                if ($kode === null) {
                    $tidakDikenal[] = [
                        'order_id' => $o->order_id,
                        'billing'  => trim($o->billing_first_name . ' ' . $o->billing_last_name . ' ' . $o->billing_company),
                        'sku'      => $sku,
                        'qty'      => $qty,
                    ];

                    continue;
                }
                if ($filterStokis && $kode !== $filterStokis) {
                    continue;
                }

                $lg[$kode] ??= ['qty' => 0, 'order' => [], 'kategori' => []];
                $lg[$kode]['qty'] += $qty;
                $lg[$kode]['order'][$o->order_id] = true;
                $lg[$kode]['kategori'][$kategori]['qty'] = ($lg[$kode]['kategori'][$kategori]['qty'] ?? 0) + $qty;
                $p = &$lg[$kode]['kategori'][$kategori]['produk'][$labelProduk];
                $p = ['nama' => $namaProduk, 'sku' => $labelProduk, 'qty' => ($p['qty'] ?? 0) + $qty];
                unset($p);

                $kategoriSemua[$kategori]['lg'] = ($kategoriSemua[$kategori]['lg'] ?? 0) + $qty;

                continue;
            }

            // ---- Stokis -> Unit (SKU memuat kode stokis)
            $kode = null;
            foreach ($kodeStokis as $k) {
                if (in_array($k, $token, true)) {
                    $kode = $k;
                    break;
                }
            }
            if ($kode === null) {
                continue;   // SKU tanpa kode stokis dikenal: bukan order stokis
            }
            if ($filterStokis && $kode !== $filterStokis) {
                continue;
            }

            $nama = trim(preg_replace('/\s+/', ' ', trim($o->billing_first_name . ' ' . $o->billing_last_name)));
            if ($nama === '') {
                $nama = trim((string) $o->billing_company) ?: '(tanpa nama)';
            }
            $ref      = trim((string) $o->billing_company);
            $unitKunci = strtoupper($nama);

            $unit[$kode][$unitKunci] ??= ['nama' => $nama, 'ref' => $ref, 'qty' => 0, 'order' => [], 'kategori' => []];
            $unit[$kode][$unitKunci]['qty'] += $qty;
            $unit[$kode][$unitKunci]['order'][$o->order_id] = true;
            $unit[$kode][$unitKunci]['kategori'][$kategori] = ($unit[$kode][$unitKunci]['kategori'][$kategori] ?? 0) + $qty;

            $kategoriSemua[$kategori]['unit'] = ($kategoriSemua[$kategori]['unit'] ?? 0) + $qty;
        }

        // ---- gabung per stokis
        $kodeAda = array_unique(array_merge(array_keys($lg), array_keys($unit)));
        usort($kodeAda, function ($a, $b) {
            return [DaftarStokis::urutKelompok($a), DaftarStokis::nama($a)]
                <=> [DaftarStokis::urutKelompok($b), DaftarStokis::nama($b)];
        });

        $stokis = [];
        foreach ($kodeAda as $kode) {
            $dataLg   = $lg[$kode] ?? ['qty' => 0, 'order' => [], 'kategori' => []];
            $daftarUnit = array_values($unit[$kode] ?? []);
            usort($daftarUnit, fn ($a, $b) => $b['qty'] <=> $a['qty']);

            foreach ($dataLg['kategori'] as &$kat) {
                $kat['produk'] = array_values($kat['produk']);
                usort($kat['produk'], fn ($a, $b) => $b['qty'] <=> $a['qty']);
            }
            unset($kat);
            uasort($dataLg['kategori'], fn ($a, $b) => $b['qty'] <=> $a['qty']);

            $qtyUnit = array_sum(array_column($daftarUnit, 'qty'));

            $stokis[] = [
                'kode'       => $kode,
                'nama'       => DaftarStokis::nama($kode),
                'kelompok'   => DaftarStokis::kelompok($kode),
                'qty_lg'     => $dataLg['qty'],
                'order_lg'   => count($dataLg['order']),
                'kategori'   => $dataLg['kategori'],
                'unit'       => array_map(function ($u) {
                    $u['order'] = count($u['order']);

                    return $u;
                }, $daftarUnit),
                'qty_unit'   => $qtyUnit,
                'selisih'    => $dataLg['qty'] - $qtyUnit,   // masuk dari LG dikurangi terjual ke unit
            ];
        }

        ksort($kategoriSemua);

        // ---- transfer gudang manual (hanya qty)
        $transfer = $this->transfer($dari, $sampai, $filterKategori);

        $totalLg   = array_sum(array_column($stokis, 'qty_lg'));
        $totalUnit = array_sum(array_column($stokis, 'qty_unit'));

        return [
            'stokis'         => $stokis,
            'kategori_semua' => $kategoriSemua,
            'transfer'       => $transfer,
            'tidak_dikenal'  => $tidakDikenal,
            'total_lg'       => $totalLg,
            'total_unit'     => $totalUnit,
            'total_transfer' => $transfer['total'],
            'total_keluar'   => $totalLg + $transfer['total'],   // pengeluaran logistik = ke stokis + transfer gudang
            'jumlah_stokis'  => count($stokis),
        ];
    }

    /** @return string[] semua kategori produk yang ada (untuk filter) */
    public function daftarKategori(): array
    {
        return Product::query()
            ->whereNotNull('kategori')->where('kategori', '!=', '')
            ->distinct()->orderBy('kategori')->pluck('kategori')->all();
    }

    // ------------------------------------------------------------------ transfer gudang

    private function transfer(?Carbon $dari, ?Carbon $sampai, ?string $filterKategori): array
    {
        $q = TransferGudangManual::query();
        if ($dari) {
            $q->whereDate('tanggal', '>=', $dari->toDateString());
        }
        if ($sampai) {
            $q->whereDate('tanggal', '<=', $sampai->toDateString());
        }
        if ($filterKategori) {
            $q->where('kategori', $filterKategori);
        }

        $tujuan = [];
        $total  = 0;

        foreach ($q->orderBy('tanggal')->get() as $t) {
            $kat = $t->kategori ?: 'Tanpa Kategori';
            $tujuan[$t->tujuan]['qty'] = ($tujuan[$t->tujuan]['qty'] ?? 0) + $t->qty;
            $tujuan[$t->tujuan]['kategori'][$kat] = ($tujuan[$t->tujuan]['kategori'][$kat] ?? 0) + $t->qty;
            $total += $t->qty;
        }
        ksort($tujuan);

        return ['tujuan' => $tujuan, 'total' => $total];
    }

    // ------------------------------------------------------------------ helper

    /** Kalender dan Majalah punya pos sendiri, jadi tidak ikut di report ini. */
    private function kalenderAtauMajalah(string $sku, string $nama): bool
    {
        $namaKecil = strtolower($nama);

        if (str_contains($namaKecil, 'kalender') || str_contains($namaKecil, 'majalah')) {
            return true;
        }
        foreach (ShopMatcher::KALENDER_SKU_PREFIX as $prefix) {
            if (str_starts_with($sku, $prefix)) {
                return true;
            }
        }

        // SKU majalah berkode edisi, mis. M159
        return ShopMatcher::edisiFrom($sku) !== null;
    }

    private function muatProduk(): void
    {
        foreach (Product::get(['label', 'name', 'kategori']) as $p) {
            $kategori = trim((string) $p->kategori) ?: 'Tanpa Kategori';
            $label    = strtoupper(trim((string) $p->label));

            if ($label !== '') {
                // dicocokkan lewat potongan pertama label (sama seperti findProductBySku)
                $awal = preg_split('/[\s\-]+/', $label)[0] ?? $label;
                $this->kategoriPerLabel[$awal] ??= $kategori;
                $this->produkPerLabel[$awal]   ??= ['nama' => (string) $p->name, 'kategori' => $kategori];
            }
            if (trim((string) $p->name) !== '') {
                $this->kategoriPerNama[strtoupper(trim((string) $p->name))] ??= $kategori;
            }
        }
    }

    /** @return array{0:string,1:string,2:string} [kategori, nama produk, label produk] */
    private function infoProduk(string $sku, string $itemName): array
    {
        $awal = trim(explode('-', $sku)[0] ?? '');

        if ($awal !== '' && isset($this->produkPerLabel[$awal])) {
            $p = $this->produkPerLabel[$awal];

            return [$p['kategori'], $p['nama'] !== '' ? $p['nama'] : $itemName, $awal];
        }

        $nama = strtoupper(trim($itemName));
        if ($nama !== '' && isset($this->kategoriPerNama[$nama])) {
            return [$this->kategoriPerNama[$nama], $itemName, $awal ?: $sku];
        }

        return ['Tanpa Kategori', $itemName !== '' ? $itemName : $sku, $awal ?: $sku];
    }

    /** Kode stokis dari nama billing berpola "Stokis <nama stokis> <no>.S", atau null. */
    private function stokisDariNama($o, array $daftar): ?string
    {
        $nama = strtoupper(trim(implode(' ', array_filter([
            $o->billing_first_name, $o->billing_last_name, $o->billing_company,
        ]))));

        if ($nama === '') {
            return null;
        }

        foreach ($daftar as $kode => $label) {
            $pola = str_replace(' ', '\\s*', preg_quote(strtoupper($label), '/'));
            if (preg_match('/(?<![A-Z0-9])STOKIS\s+' . $pola . '(?![A-Z0-9])/', $nama)) {
                return $kode;
            }
        }

        // Pengelola Unit Aktif Khusus: nama billing "Pengelola Unit Aktif Khusus 2 T0022", tanpa awalan Stokis
        foreach (DaftarStokis::PENGELOLA as $kode => $label) {
            $pola = str_replace(' ', '\\s*', preg_quote(strtoupper($label), '/'));
            if (preg_match('/(?<![A-Z0-9])' . $pola . '(?![A-Z0-9])/', $nama)) {
                return $kode;
            }
        }

        return null;
    }
}