<?php

namespace App\Modules\Picking\Services;

use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Order\Models\RealisasiLogistikAktif;
use App\Modules\Order\Models\RealisasiLogistikPasif;
use App\Modules\Order\Support\KategoriOrder;
use App\Modules\Picking\Models\KetersediaanBarang;
use App\Modules\Picking\Models\ManualPicking;
use App\Modules\Picking\Models\Picking;
use App\Modules\Picking\Models\PickingPasif;
use Illuminate\Support\Collection;

/**
 * Status "barang belum datang" untuk Majalah (per edisi, mis. M159) dan Kalender (per tahun, mis. 2027).
 *
 * Aturan:
 *  - Pesanan tetap berjalan di Manual / biMBA Shop / Penjualan. Yang menahan hanya PICKING
 *    (tidak bisa dicek, diberi PIC, atau lanjut ke QC) dan Picking pula yang membukanya.
 *  - Edisi/tahun yang belum punya baris di tabel ketersediaan_barang dianggap BELUM datang.
 *  - Majalah dikenali dari kategori_order = Majalah (edisi dibaca dari SKU/nama item).
 *    Kalender dikenali dari kategori_order = Kalender ATAU nama/SKU item memuat "kalender"
 *    (di alur Manual, Kalender masih tercatat sebagai kategori Modul).
 *  - Bila edisi/tahun tidak terbaca, baris TIDAK ditahan (agar tidak macet tanpa sebab).
 *  - Modul, Sertifikat dan lainnya tidak pernah tertahan.
 */
class KetersediaanBarangService
{
    /**
     * Acuan penomoran edisi Majalah: Oktober 2026 = M163, naik 1 setiap bulan
     * (Nov 2026 = M164, Des 2026 = M165, Jan 2027 = M166, dst).
     * Ubah angka ini bila penomoran bergeser.
     */
    public const ACUAN_TAHUN  = 2026;
    public const ACUAN_BULAN  = 10;
    public const ACUAN_EDISI  = 163;

    /** @var array<string,bool>|null  "Jenis|kode" => true (sudah datang) */
    private ?array $datang = null;

    // ------------------------------------------------------------------ kunci

    private static function itemKalender(?string $sku, ?string $nama): bool
    {
        if (str_contains(strtolower((string) $nama), 'kalender')) {
            return true;
        }
        $sku = strtoupper(trim((string) $sku));
        foreach (ShopMatcher::KALENDER_SKU_PREFIX as $p) {
            if ($sku !== '' && str_starts_with($sku, $p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string|null $kategori  kategori_order baris (boleh null)
     * @param  array<int,array<int,string>> $kelompok tiap elemen = teks milik satu item [sku, nama]
     * @return array<int,array{jenis:string,kode:string}>
     */
    public static function kunciDariTeks(?string $kategori, array $kelompok): array
    {
        $kategori = (string) $kategori;
        $hasil    = [];

        foreach ($kelompok as $teks) {
            $sku  = (string) ($teks[0] ?? '');
            $nama = (string) ($teks[1] ?? '');

            if ($kategori === KategoriOrder::KALENDER || self::itemKalender($sku, $nama)) {
                $tahun = ShopMatcher::tahunFrom($sku, $nama);
                if ($tahun) {
                    $hasil['Kalender|' . $tahun] = ['jenis' => 'Kalender', 'kode' => $tahun];
                }
            } elseif ($kategori === KategoriOrder::MAJALAH) {
                $edisi = ShopMatcher::edisiFrom($sku, $nama);
                if ($edisi) {
                    $edisi = strtoupper($edisi);
                    $hasil['Majalah|' . $edisi] = ['jenis' => 'Majalah', 'kode' => $edisi];
                }
            }
        }

        return array_values($hasil);
    }

    /** Dipakai migration: hanya daftar kode untuk satu jenis. */
    public static function kodeDariTeks(string $kategori, array $kelompok): array
    {
        return array_values(array_map(
            fn ($k) => $k['kode'],
            array_filter(self::kunciDariTeks($kategori, $kelompok), fn ($k) => $k['jenis'] === $kategori)
        ));
    }

    /**
     * Kunci untuk satu baris apa pun: Picking, PickingPasif, ManualPicking, RealisasiXxx,
     * atau objek biasa dengan field nama_barang / pesanan.
     *
     * @return array<int,array{jenis:string,kode:string}>
     */
    public function kunciBaris($row): array
    {
        $kategori = (string) ($row->kategori_order ?? '');

        $kelompok = [];
        foreach (['pickingItems', 'items'] as $rel) {
            if ($row instanceof \Illuminate\Database\Eloquent\Model && method_exists($row, $rel)) {
                foreach ($row->{$rel} as $it) {
                    $kelompok[] = [(string) ($it->item_sku ?? ''), (string) ($it->item_name ?? '')];
                }
                break;
            }
        }

        $kunci = self::kunciDariTeks($kategori, $kelompok);
        if ($kunci) {
            return $kunci;
        }

        // Tanpa item (mis. baris Realisasi / Logistik): pakai teks nama barang / pesanan
        $teks = [];
        foreach (['nama_barang', 'pesanan'] as $f) {
            if (! empty($row->{$f})) {
                $teks[] = ['', (string) $row->{$f}];
            }
        }

        return self::kunciDariTeks($kategori, $teks);
    }

    // ------------------------------------------------------------------ cek

    private function muatDatang(): array
    {
        if ($this->datang === null) {
            $this->datang = KetersediaanBarang::where('status', 'datang')->get()
                ->mapWithKeys(fn ($r) => [$r->jenis . '|' . $r->kode => true])
                ->all();
        }

        return $this->datang;
    }

    /** Daftar edisi/tahun yang belum datang untuk baris ini, mis. ["Majalah M159"]. Kosong = boleh jalan. */
    public function belumDatang($row): array
    {
        $datang = $this->muatDatang();
        $hasil  = [];
        foreach ($this->kunciBaris($row) as $k) {
            if (! isset($datang[$k['jenis'] . '|' . $k['kode']])) {
                $hasil[] = $k['jenis'] . ' ' . $k['kode'];
            }
        }

        return $hasil;
    }

    // ------------------------------------------------------------------ daftar untuk halaman Ketersediaan Barang

    /** @return array<int,array{0:class-string,1:?string,2:array<int,string>}> [model, relasi item, kolom teks] */
    private function sumber(): array
    {
        return [
            [Picking::class,                'pickingItems', []],
            [PickingPasif::class,           'items',        ['pesanan']],
            [ManualPicking::class,          'pickingItems', ['pesanan']],
            [RealisasiLogistikAktif::class, null,           ['nama_barang']],
            [RealisasiLogistikPasif::class, null,           ['nama_barang']],
        ];
    }

    /** Baris dari semua jalur Persiapan yang belum selesai disiapkan dan mungkin Majalah/Kalender. */
    private function barisAktif(): \Generator
    {
        foreach ($this->sumber() as [$model, $rel, $kolom]) {
            $q = $model::query()->where(function ($q) {
                $q->whereNull('status_persiapan')->orWhere('status_persiapan', '!=', 'Sudah');
            })->where(function ($q) use ($rel, $kolom) {
                $q->whereIn('kategori_order', KategoriOrder::BISA_HOLD);
                foreach ($kolom as $c) {
                    $q->orWhere($c, 'like', '%kalender%');
                }
                if ($rel) {
                    $q->orWhereHas($rel, fn ($i) => $i->where('item_name', 'like', '%kalender%'));
                }
            });
            if ($rel) {
                $q->with($rel);
            }

            foreach ($q->cursor() as $row) {
                yield [$model, $row];
            }
        }
    }

    /**
     * Semua edisi/tahun yang pernah terlihat di data atau sudah terdaftar, dengan status dan jumlah
     * Picking yang menunggu.
     *
     * @return Collection<int,array{jenis:string,kode:string,status:string,tgl_datang:mixed,jumlah:int}>
     */
    public function daftar(): Collection
    {
        $this->daftarkanEdisiBulanIni();

        $terdaftar = KetersediaanBarang::all()->keyBy(fn ($r) => $r->jenis . '|' . $r->kode);
        $hitung    = [];

        foreach ($this->barisAktif() as [$model, $row]) {
            foreach ($this->kunciBaris($row) as $k) {
                $key = $k['jenis'] . '|' . $k['kode'];
                $hitung[$key] ??= 0;
                $hitung[$key]++;
            }
        }

        $semua = [];
        foreach ($terdaftar as $key => $r) {
            $semua[$key] = [
                'jenis' => $r->jenis, 'kode' => $r->kode, 'status' => $r->status,
                'tgl_datang' => $r->tgl_datang, 'jumlah' => 0,
            ];
        }
        foreach ($hitung as $key => $jumlah) {
            [$jenis, $kode] = explode('|', $key, 2);
            $semua[$key] ??= ['jenis' => $jenis, 'kode' => $kode, 'status' => 'belum_datang', 'tgl_datang' => null, 'jumlah' => 0];
            $semua[$key]['jumlah'] = $jumlah;
        }

        return collect(array_values($semua))
            ->sortBy([['status', 'asc'], ['jenis', 'asc'], ['kode', 'desc']])
            ->values();
    }

    /** Kode edisi Majalah untuk sebuah tanggal, mis. Oktober 2026 => M163. Null bila sebelum acuan. */
    public function edisiUntuk(\Carbon\CarbonInterface $tgl): ?string
    {
        $selisih = ($tgl->year - self::ACUAN_TAHUN) * 12 + ($tgl->month - self::ACUAN_BULAN);

        return $selisih < 0 ? null : 'M' . (self::ACUAN_EDISI + $selisih);
    }

    /**
     * Edisi bulan berjalan otomatis terdaftar sebagai "belum datang" (tanpa menimpa yang sudah ada),
     * jadi edisi baru muncul sendiri di daftar tiap awal bulan.
     */
    public function daftarkanEdisiBulanIni(): void
    {
        $kode = $this->edisiUntuk(now());
        if ($kode === null) {
            return;
        }

        KetersediaanBarang::firstOrCreate(
            ['jenis' => 'Majalah', 'kode' => $kode],
            ['status' => 'belum_datang']
        );
    }

    /** Hanya yang masih menunggu barang (untuk banner di halaman Picking). */
    public function tertahan(): Collection
    {
        return $this->daftar()
            ->filter(fn ($d) => $d['status'] !== 'datang' && $d['jumlah'] > 0)
            ->values();
    }

    public function riwayat(int $limit = 8): Collection
    {
        return KetersediaanBarang::where('status', 'datang')
            ->orderByDesc('tgl_datang')->orderByDesc('id')->limit($limit)->get();
    }

    // ------------------------------------------------------------------ ubah

    public function tandai(string $jenis, string $kode, string $status, ?int $userId): KetersediaanBarang
    {
        $this->datang = null;

        return KetersediaanBarang::updateOrCreate(
            ['jenis' => $jenis, 'kode' => strtoupper($kode)],
            [
                'status'      => $status,
                'tgl_datang'  => $status === 'datang' ? now()->toDateString() : null,
                'diubah_oleh' => $userId,
            ]
        );
    }
}
