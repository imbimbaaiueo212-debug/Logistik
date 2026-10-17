<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Order\Models\JakartaPasif;
use App\Modules\Order\Models\LogistikAktif;
use App\Modules\Order\Models\LogistikPasif;
use App\Modules\Order\Support\RumahJakarta;
use App\Modules\Master\Models\UnitNamaMismatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Menu Penjualan: dua halaman saja.
 *
 *  Jakarta  : satu halaman berisi SEMUA rumah (JKT, JKTP, DLC, EBT, INV, BSS), bisa difilter per rumah.
 *             JKT -> tabel jakarta_aktif | JKTP, DLC, EBT, INV, BSS -> tabel jakarta_pasif (kolom jenis)
 *  Logistik : satu halaman berisi SEMUA vendor (SKU LG), bisa difilter Vendor Aktif / Vendor Pasif.
 *             Aktif -> tabel logistik_aktif | Pasif -> tabel logistik_pasif (dipisah oleh Database Stokis saat sync)
 *
 * Tiap rumah / vendor tetap punya wadah (tabel) dan Rekap Aktual sendiri. Daftar di halaman ini hanyalah
 * gabungan (UNION) dari wadah-wadah itu. Proses massal & rekap berjalan per rumah / vendor, jadi harus
 * memilih rumah / vendor dulu sebelum memproses.
 */
class PenjualanController extends Controller
{
    public const RUMAH_JAKARTA = ['JKT', 'JKTP', 'DLC', 'EBT', 'INV', 'BSS'];
    public const VENDOR        = ['AKTIF', 'PASIF'];

    // =====================================================================
    // JAKARTA
    // =====================================================================
    public function jakarta(Request $request)
    {
        // ?jenis= dipertahankan agar redirect lama (setelah proses Pasif) tetap jalan
        $rumah = strtoupper(trim((string) $request->query('rumah', $request->query('jenis', ''))));
        if (! in_array($rumah, self::RUMAH_JAKARTA, true)) {
            $rumah = '';
        }

        $relasi = ['items.product', 'casdana' => fn ($q) => $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer')];

        // Sumber data: tiap rumah punya tabel sendiri, ditampilkan bergabung di satu halaman.
        $sumber = [];

        if ($rumah === '' || $rumah === 'JKT') {
            $sumber['aktif'] = ['kelas' => JakartaAktif::class, 'rel' => $relasi, 'saring' => null];
        }

        // jakarta_pasif: JKTP, ditambah order lama DLC/EBT/INV/BSS yang sudah terlanjur diproses di sini
        if ($rumah === '' || in_array($rumah, JakartaPasifController::JENIS_PASIF, true)) {
            $sumber['pasif'] = [
                'kelas'  => JakartaPasif::class,
                'rel'    => $relasi,
                'saring' => function ($q) use ($rumah) {
                    if ($rumah !== '') {
                        $q->where(function ($x) use ($rumah) {
                            $x->where('jenis', $rumah);
                            if ($rumah === 'JKTP') {
                                $x->orWhereNull('jenis'); // data lama tanpa jenis dianggap JKTP
                            }
                        });
                    }
                },
            ];
        }

        foreach (RumahJakarta::BARU as $k) {
            if ($rumah === '' || $rumah === $k) {
                $sumber[strtolower($k)] = ['kelas' => RumahJakarta::header($k), 'rel' => $relasi, 'saring' => null];
            }
        }

        $data = $this->gabungBanyak(
            $request,
            $sumber,
            fn ($m, $kunci) => $kunci === 'aktif' ? 'JKT' : ($m->jenis ?: strtoupper($kunci === 'pasif' ? 'JKTP' : $kunci))
        );

        $kolom = fn (string $k) => $this->daftarDistinct(
            array_map(fn ($r) => RumahJakarta::header($r), RumahJakarta::SEMUA),
            $k
        );

        $mismatchNoCab = UnitNamaMismatch::where('is_resolved', false)
            ->pluck('no_cab')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->flip()
            ->all();

        return view('order::penjualan.jakarta', [
            'data'            => $data,
            'rumah'           => $rumah,
            'listStatusBayar' => $kolom('status_pembayaran'),
            'listPesanan'     => $kolom('pesanan'),
            'listNamaUnit'    => $kolom('nama_unit'),
            'listGrup'        => $kolom('grup'),
            'mismatchNoCab'   => $mismatchNoCab,
        ]);
    }

    // =====================================================================
    // LOGISTIK
    // =====================================================================
    public function logistik(Request $request)
    {
        $vendor = strtoupper(trim((string) $request->query('vendor', '')));
        if (! in_array($vendor, self::VENDOR, true)) {
            $vendor = '';
        }

        $data = $this->gabung(
            $request,
            $vendor === '' || $vendor === 'AKTIF' ? LogistikAktif::class : null,
            $vendor === '' || $vendor === 'PASIF' ? LogistikPasif::class : null,
            null,
            ['items.product', 'casdana' => fn ($q) => $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer')],
            ['items.product', 'casdana' => fn ($q) => $q->select('id', 'invoice_number', 'payment_date', 'amount', 'status', 'payment_channel', 'customer')],
            fn ($m, $sumber) => $sumber === 'aktif' ? 'AKTIF' : 'PASIF'
        );

        $kolom = fn (string $k) => $this->daftarDistinct([LogistikAktif::class, LogistikPasif::class], $k);

        $mismatchNoCab = UnitNamaMismatch::where('is_resolved', false)
            ->pluck('no_cab')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->flip()
            ->all();

        return view('order::penjualan.logistik', [
            'data'            => $data,
            'vendor'          => $vendor,
            'listStatusBayar' => $kolom('status_pembayaran'),
            'listPesanan'     => $kolom('pesanan'),
            'listNamaUnit'    => $kolom('nama_unit'),
            'listGrup'        => $kolom('grup'),
            'mismatchNoCab'   => $mismatchNoCab,
        ]);
    }

    // =====================================================================
    // Halaman lama diarahkan ke halaman gabungan (agar semua redirect lama tetap mendarat di sini)
    // =====================================================================
    public function keJakartaAktif(Request $request)
    {
        return redirect()->route('penjualan.jakarta', array_merge($request->query(), ['rumah' => 'JKT']));
    }

    public function keJakartaPasif(Request $request)
    {
        $q = $request->query();
        if (! empty($q['jenis'])) {
            $q['rumah'] = $q['jenis'];
            unset($q['jenis']);
        }

        return redirect()->route('penjualan.jakarta', $q);
    }

    public function keLogistikAktif(Request $request)
    {
        return redirect()->route('penjualan.logistik', array_merge($request->query(), ['vendor' => 'AKTIF']));
    }

    public function keLogistikPasif(Request $request)
    {
        return redirect()->route('penjualan.logistik', array_merge($request->query(), ['vendor' => 'PASIF']));
    }

    // =====================================================================
    // SYNC SATU TOMBOL: tarik semua rumah (JKT, JKTP, DLC, EBT, INV, BSS) dari biMBA Shop + Casdana
    //   JKT                       -> JakartaAktifController::syncJktFromBimbashop
    //   JKTP, DLC, EBT, INV, BSS  -> JakartaPasifController::syncJktPasifFromBimbashop
    // Logika tiap sync TIDAK diubah; di sini hanya dijalankan berurutan lalu pesannya digabung.
    // =====================================================================
    public function syncJakartaSemua()
    {
        $langkah = [
            'JKT'                        => [JakartaAktifController::class, 'syncJktFromBimbashop'],
            'JKTP / DLC / EBT / INV / BSS' => [JakartaPasifController::class, 'syncJktPasifFromBimbashop'],
        ];

        $sukses = [];
        $gagal  = [];

        foreach ($langkah as $label => [$kelas, $metode]) {
            try {
                app($kelas)->{$metode}();
                $pesan = session('success');
                $sukses[] = '<strong>' . $label . '</strong><br>' . ($pesan ?: 'Selesai.');
            } catch (\Throwable $e) {
                \Log::error("Sync Jakarta ({$label}) gagal: " . $e->getMessage());
                $gagal[] = "{$label}: " . $e->getMessage();
            }
        }

        $redirect = redirect()->route('penjualan.jakarta');

        if ($sukses) {
            $redirect->with('success', implode('<hr class="my-2">', $sukses));
        }
        if ($gagal) {
            $redirect->with('error', '❌ Sync gagal:<br>• ' . implode('<br>• ', array_map('e', $gagal)));
        }

        return $redirect;
    }

    // =====================================================================
    // Helper
    // =====================================================================

    /** Filter yang sama dengan halaman lama; kolomnya ada di semua tabel Jakarta & Logistik. */
    private function terapkanFilter($q, Request $r): void
    {
        foreach (['id_pesan', 'kirim', 'nama_unit', 'pesanan'] as $f) {
            if ($r->filled($f)) {
                $q->where($f, 'like', '%' . $r->input($f) . '%');
            }
        }
        foreach (['status_pembayaran', 'status_pesan', 'validasi', 'grup'] as $f) {
            if ($r->filled($f)) {
                $q->where($f, $r->input($f));
            }
        }
        if ($r->filled('start_date')) {
            $q->whereDate('tgl_pesan', '>=', $r->start_date);
        }
        if ($r->filled('end_date')) {
            $q->whereDate('tgl_pesan', '<=', $r->end_date);
        }
    }

    /**
     * Gabungkan dua tabel (aktif & pasif) menjadi satu daftar berhalaman, urut Order Date.
     * Dipakai halaman Logistik. Halaman Jakarta memakai gabungBanyak() karena sumbernya lebih dari dua.
     */
    private function gabung(Request $request, ?string $kelasAktif, ?string $kelasPasif, ?\Closure $saringPasif, array $relAktif, array $relPasif, \Closure $labelRumah)
    {
        $sumber = [];

        if ($kelasAktif) {
            $sumber['aktif'] = ['kelas' => $kelasAktif, 'rel' => $relAktif, 'saring' => null];
        }
        if ($kelasPasif) {
            $sumber['pasif'] = ['kelas' => $kelasPasif, 'rel' => $relPasif, 'saring' => $saringPasif];
        }

        return $this->gabungBanyak($request, $sumber, $labelRumah);
    }

    /**
     * Gabungkan BANYAK tabel menjadi satu daftar berhalaman, urut Order Date.
     * Yang di-UNION hanya id + tgl_pesan; model lengkap dimuat per halaman dari tabelnya masing-masing.
     * Tiap baris diberi atribut: sumber (kunci sumber, mis. 'aktif'|'pasif'|'dlc') dan rumah (label badge).
     *
     * @param array<string, array{kelas: string, rel: array, saring: ?\Closure}> $sumber
     */
    private function gabungBanyak(Request $request, array $sumber, \Closure $labelRumah)
    {
        $perPage = (int) $request->get('per_page', 50);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100, 200, 500], true) ? $perPage : 50;

        $subs = [];

        foreach ($sumber as $kunci => $def) {
            $q = $def['kelas']::query();
            $this->terapkanFilter($q, $request);
            if (! empty($def['saring'])) {
                ($def['saring'])($q);
            }
            $subs[] = $q->select('id', 'tgl_pesan')->selectRaw("'{$kunci}' as sumber")->toBase();
        }

        $union = array_shift($subs);
        foreach ($subs as $s) {
            $union->unionAll($s);
        }

        $page = DB::query()
            ->fromSub($union, 'u')
            ->orderBy('tgl_pesan')
            ->orderBy('sumber')
            ->orderBy('id')
            ->paginate($perPage)
            ->appends($request->query());

        $rows = $page->getCollection();

        // muat model lengkap per sumber
        $dimuat = [];
        foreach ($sumber as $kunci => $def) {
            $ids = $rows->where('sumber', $kunci)->pluck('id')->all();
            $dimuat[$kunci] = $ids
                ? $def['kelas']::with($def['rel'])->whereIn('id', $ids)->get()->keyBy('id')
                : collect();
        }

        $page->setCollection(
            $rows->map(function ($r) use ($dimuat, $labelRumah) {
                $m = isset($dimuat[$r->sumber]) ? $dimuat[$r->sumber]->get($r->id) : null;
                if (! $m) {
                    return null;
                }
                $m->setAttribute('sumber', $r->sumber);
                $m->setAttribute('rumah', $labelRumah($m, $r->sumber));

                return $m;
            })->filter()->values()
        );

        return $page;
    }

    /** Nilai unik satu kolom dari beberapa tabel, untuk isi dropdown filter. */
    private function daftarDistinct(array $kelas, string $kolom)
    {
        return collect($kelas)
            ->flatMap(fn ($k) => $k::select($kolom)
                ->whereNotNull($kolom)
                ->where($kolom, '!=', '')
                ->distinct()
                ->pluck($kolom))
            ->unique()
            ->sort()
            ->values();
    }
}
