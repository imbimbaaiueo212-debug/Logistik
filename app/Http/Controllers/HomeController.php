<?php

namespace App\Http\Controllers;

use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Models\DistributionOrder;
use App\Models\DistributionPasif;
use App\Models\Distribution;
use App\Models\DlcPeriode;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\JakartaAktif;
use App\Models\JakartaPasif;
use App\Models\ManualDistributionOrder;
use App\Models\ManualModulOrder;
use App\Models\ManualOrder;
use App\Models\ManualPacking;
use App\Models\ManualPicking;
use App\Models\ManualQcOutgoing;
use App\Models\ManualRealisasi;
use App\Models\ManualSertifikatOrder;
use App\Models\MatchingUserExport;
use App\Models\Packing;
use App\Models\PackingPasif;
use App\Models\PasifPeriode;
use App\Models\PesananMajalah;
use App\Models\PesananMajalahKotamadya;
use App\Models\PesananMajalahPuw1;
use App\Models\Picking;
use App\Models\PickingPasif;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\QcOutgoing;
use App\Models\QcOutgoingPasif;
use App\Models\Quotation;
use App\Models\RejectItem;
use App\Models\ReturnDistribution;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StokisMitra;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\UnitKemitraan;
use App\Models\User;
use App\Models\UserExportBimbaShop;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $today      = today();
        $awalBulan  = now()->startOfMonth();
        $akhirBulan = now()->endOfDay();

        // Order yang belum diproses (is_processed = 0 / NULL)
        $belumDiproses = fn ($q) => $q->where('is_processed', 0)->orWhereNull('is_processed');

        // =====================================================================
        // 1. ORDER HARI INI  (Aktif, Pasif, Manual: Majalah, Modul, Sertifikat)
        // =====================================================================
        $orderHariIni = [
            'Aktif'      => $this->hitung(fn () => JakartaAktif::whereDate('tgl_pesan', $today)->count()),
            'Pasif'      => $this->hitung(fn () => JakartaPasif::whereDate('tgl_pesan', $today)->count()),
            'Majalah'    => $this->hitung(fn () => ManualOrder::whereDate('order_date', $today)->count()),
            'Modul'      => $this->hitung(fn () => ManualModulOrder::whereDate('order_date', $today)->count()),
            'Sertifikat' => $this->hitung(fn () => ManualSertifikatOrder::whereDate('order_date', $today)->count()),
        ];

        // =====================================================================
        // 2. ALUR KERJA: Pemesanan -> Persiapan -> QC -> Packing -> Distribusi
        //    Angka utama = pekerjaan yang masih menunggu di tahap tsb.
        // =====================================================================

        // --- Pemesanan: order yang belum diproses ---
        $pesanAktif = $this->hitung(fn () => JakartaAktif::where($belumDiproses)->count());
        $pesanPasif = $this->hitung(fn () => JakartaPasif::where($belumDiproses)->count());
        $pesanManual = $this->hitung(fn () => ManualOrder::where($belumDiproses)->count())
            + $this->hitung(fn () => ManualModulOrder::where($belumDiproses)->count())
            + $this->hitung(fn () => ManualSertifikatOrder::where($belumDiproses)->count());

        // --- Persiapan: picking yang belum disiapkan ---
        $pickAktif  = $this->hitung(fn () => Picking::where('status_persiapan', 'Belum')->count());
        $pickPasif  = $this->hitung(fn () => PickingPasif::where('status_persiapan', 'Belum')->count());
        $pickManual = $this->hitung(fn () => ManualPicking::where('status_persiapan', 'Belum')->count());

        // --- QC: masih Pending ---
        $qcAktif  = $this->hitung(fn () => QcOutgoing::where('status_qc', 'Pending')->count());
        $qcPasif  = $this->hitung(fn () => QcOutgoingPasif::where('status_qc', 'Pending')->count());
        $qcManual = $this->hitung(fn () => ManualQcOutgoing::where('status_qc', 'Pending')->count());

        $qcBermasalah = $this->hitung(fn () => QcOutgoing::whereIn('status_qc', ['Reject', 'Revisi'])->count())
            + $this->hitung(fn () => QcOutgoingPasif::whereIn('status_qc', ['Reject', 'Revisi'])->count())
            + $this->hitung(fn () => ManualQcOutgoing::whereIn('status_qc', ['Reject', 'Revisi'])->count());

        // --- Packing: belum selesai (bukan Selesai / Batal) ---
        $packAktif  = $this->hitung(fn () => Packing::whereNotIn('status_packing', ['Selesai', 'Batal'])->count());
        $packPasif  = $this->hitung(fn () => PackingPasif::whereNotIn('status_packing', ['Selesai', 'Batal'])->count());
        $packManual = $this->hitung(fn () => ManualPacking::whereNotIn('status_packing', ['Selesai', 'Batal'])->count());

        // --- Distribusi: siap kirim (belum di-pickup) ---
        $kirimAktif  = $this->hitung(fn () => DistributionOrder::where('status_pengiriman', 'belum_pickup')->count());
        $kirimPasif  = $this->hitung(fn () => DistributionPasif::where('status_pengiriman', 'belum_pickup')->count());
        $kirimManual = $this->hitung(fn () => ManualDistributionOrder::where('status_distribusi', 'Pending')->count());

        $dalamPerjalanan = $this->hitung(fn () => DistributionOrder::whereIn('status_pengiriman', ['pickup', 'transit'])->count())
            + $this->hitung(fn () => DistributionPasif::whereIn('status_pengiriman', ['pickup', 'transit', 'sudah_pickup'])->count())
            + $this->hitung(fn () => ManualDistributionOrder::where('status_distribusi', 'Proses')->count());

        $terkirimHariIni = $this->hitung(fn () => DistributionOrder::whereDate('tgl_diterima', $today)->count())
            + $this->hitung(fn () => DistributionPasif::whereDate('tgl_diterima', $today)->count())
            + $this->hitung(fn () => ManualDistributionOrder::where('status_distribusi', 'Selesai')->whereDate('tgl_kirim', $today)->count());

        $pipeline = [
            [
                'key' => 'pemesanan', 'title' => 'Pemesanan', 'caption' => 'menunggu diproses',
                'route' => 'order.index',
                'total' => $pesanAktif + $pesanPasif + $pesanManual,
                'parts' => ['Aktif' => $pesanAktif, 'Pasif' => $pesanPasif, 'Manual' => $pesanManual],
                'notes' => ['Masuk hari ini' => array_sum($orderHariIni)],
            ],
            [
                'key' => 'persiapan', 'title' => 'Persiapan', 'caption' => 'belum disiapkan',
                'route' => 'picking.index',
                'total' => $pickAktif + $pickPasif + $pickManual,
                'parts' => ['Aktif' => $pickAktif, 'Pasif' => $pickPasif, 'Manual' => $pickManual],
                'notes' => [],
            ],
            [
                'key' => 'qc', 'title' => 'QC', 'caption' => 'menunggu pemeriksaan',
                'route' => 'qc-outgoing.index',
                'total' => $qcAktif + $qcPasif + $qcManual,
                'parts' => ['Aktif' => $qcAktif, 'Pasif' => $qcPasif, 'Manual' => $qcManual],
                'notes' => ['Reject / revisi' => $qcBermasalah],
            ],
            [
                'key' => 'packing', 'title' => 'Packing', 'caption' => 'belum selesai',
                'route' => 'packing.index',
                'total' => $packAktif + $packPasif + $packManual,
                'parts' => ['Aktif' => $packAktif, 'Pasif' => $packPasif, 'Manual' => $packManual],
                'notes' => [],
            ],
            [
                'key' => 'distribusi', 'title' => 'Distribusi', 'caption' => 'siap dikirim',
                'route' => 'distribution-order.index',
                'total' => $kirimAktif + $kirimPasif + $kirimManual,
                'parts' => ['Aktif' => $kirimAktif, 'Pasif' => $kirimPasif, 'Manual' => $kirimManual],
                'notes' => ['Dalam perjalanan' => $dalamPerjalanan, 'Terkirim hari ini' => $terkirimHariIni],
            ],
        ];

        // =====================================================================
        // 3. TREN ORDER MASUK 7 HARI TERAKHIR
        // =====================================================================
        $tren = $this->trenOrder7Hari();

        // Rincian sumber order hari ini (untuk daftar di samping grafik)
        $sumberRoute = [
            'Aktif'      => 'order.jakarta-aktif',
            'Pasif'      => 'order.jakarta-pasif',
            'Majalah'    => 'order-manual.index',
            'Modul'      => 'order-manual-modul.index',
            'Sertifikat' => 'order-manual-sertifikat.index',
        ];
        $sumberOrder = [];
        foreach ($orderHariIni as $label => $jumlah) {
            $sumberOrder[] = ['label' => $label, 'value' => $jumlah, 'route' => $sumberRoute[$label]];
        }

        // =====================================================================
        // 4. PERLU PERHATIAN
        // =====================================================================
        $perhatian = [
            ['label' => 'QC reject / revisi',          'hint' => 'Barang perlu diperiksa ulang',
             'value' => $qcBermasalah, 'route' => 'qc-outgoing.index', 'tone' => 'rust'],

            ['label' => 'Pengiriman bermasalah',       'hint' => 'Hold, retur, atau hilang',
             'value' => $this->hitung(fn () => DistributionOrder::whereIn('status_pengiriman', ['hold', 'retur', 'missing'])->count()),
             'route' => 'distribution-order.index', 'tone' => 'rust'],

            ['label' => 'Stok habis',                  'hint' => 'Produk dengan stok 0 di gudang',
             'value' => $this->hitung(fn () => Stock::where('qty', '<=', 0)->count()),
             'route' => 'stock.index', 'tone' => 'rust'],

            ['label' => 'QC penerimaan barang',        'hint' => 'Item Goods Receipt belum di-QC',
             'value' => $this->hitung(fn () => GoodsReceiptItem::where('qc_status', 'pending')->count()),
             'route' => 'gr.index', 'tone' => 'navy'],

            ['label' => 'Transfer stok',               'hint' => 'Menunggu persetujuan',
             'value' => $this->hitung(fn () => Transfer::where('status', 'pending')->count()),
             'route' => 'transfer.index', 'tone' => 'navy'],

            ['label' => 'Distribusi barang',           'hint' => 'Menunggu persetujuan',
             'value' => $this->hitung(fn () => Distribution::where('status', 'pending')->count()),
             'route' => 'distribution.index', 'tone' => 'navy'],

            ['label' => 'Retur distribusi',            'hint' => 'Menunggu persetujuan',
             'value' => $this->hitung(fn () => ReturnDistribution::where('status', 'pending')->count()),
             'route' => 'return-distribution.index', 'tone' => 'navy'],

            ['label' => 'Barang reject',               'hint' => 'Belum ditangani',
             'value' => $this->hitung(fn () => RejectItem::where('status', 'pending')->count()),
             'route' => 'reject.index', 'tone' => 'navy'],

            ['label' => 'Stock opname',                'hint' => 'Draft atau menunggu approval',
             'value' => $this->hitung(fn () => StockOpname::whereIn('status', ['draft', 'submitted'])->count()),
             'route' => 'stock-opname.index', 'tone' => 'navy'],

            ['label' => 'Quotation',                   'hint' => 'Belum disetujui',
             'value' => $this->hitung(fn () => Quotation::whereIn('status', ['draft', 'sent'])->count()),
             'route' => 'quotation.index', 'tone' => 'navy'],
        ];
        $perhatian      = array_values(array_filter($perhatian, fn ($p) => $p['value'] > 0));
        $totalPerhatian = array_sum(array_column($perhatian, 'value'));

        // =====================================================================
        // 5. GUDANG & STOK  (Produk, Suplier, Gudang, Stok, PO, GR)
        // =====================================================================
        $gudang = [
            ['label' => 'Produk',            'value' => $this->hitung(fn () => Product::count()),   'route' => 'products.index'],
            ['label' => 'Suplier',           'value' => $this->hitung(fn () => Supplier::count()),  'route' => 'suppliers.index'],
            ['label' => 'Gudang',            'value' => $this->hitung(fn () => Warehouse::count()), 'route' => 'warehouses.index'],
            ['label' => 'Total stok (unit)', 'value' => $this->hitung(fn () => Stock::sum('qty')),  'route' => 'stock.index'],
            ['label' => 'Mutasi stok hari ini', 'value' => $this->hitung(fn () => StockMovement::whereDate('created_at', $today)->count()),
             'route' => 'stock-movements.index'],
            ['label' => 'PO bulan ini',      'value' => $this->hitung(fn () => PurchaseOrder::whereBetween('date', [$awalBulan, $akhirBulan])->count()),
             'route' => 'po.index'],
            ['label' => 'Penerimaan bulan ini', 'value' => $this->hitung(fn () => GoodsReceipt::whereBetween('date', [$awalBulan, $akhirBulan])->count()),
             'route' => 'gr.index'],
        ];

        // =====================================================================
        // 6. KEUANGAN BULAN INI (logika sama dengan PengeluaranController)
        // =====================================================================
        $rentang = [$awalBulan, $akhirBulan];

        $bimbashopTotal = (float) $this->aman(fn () => BimbashopOrder::where('status', 'completed')
            ->whereBetween('order_date', $rentang)->sum('order_total'), 0);

        $casdanaTotal = (float) $this->aman(fn () => CasdanaTransaction::whereIn('status', ['SETTLED', 'PAID'])
            ->whereBetween('payment_date', $rentang)->sum('amount'), 0);

        $manualTotal = (float) $this->aman(fn () => ManualRealisasi::whereNotNull('rekap_number')
            ->whereBetween('tgl_bayar', $rentang)->sum('jumlah_bayar'), 0);

        $keuangan = [
            'total' => $bimbashopTotal + $casdanaTotal + $manualTotal,
            'items' => [
                ['label' => 'biMBA Shop', 'value' => $bimbashopTotal, 'route' => 'import.bimbashop', 'color' => '#28447F'],
                ['label' => 'Kasdana',    'value' => $casdanaTotal,   'route' => 'import.casdana',   'color' => '#E85D2A'],
                ['label' => 'Manual',     'value' => $manualTotal,    'route' => 'pengeluaran.index', 'color' => '#9FADC7'],
            ],
        ];

        // =====================================================================
        // 7. DATABASE & MITRA
        // =====================================================================
        $matchTotal = $this->hitung(fn () => MatchingUserExport::count());
        $matchOk    = $this->hitung(fn () => MatchingUserExport::where('status', true)->count());

        $mitra = [
            ['label' => 'Unit kemitraan',    'value' => $this->hitung(fn () => UnitKemitraan::count()),
             'sub' => $this->hitung(fn () => UnitKemitraan::where('status_pengelolaan', 'Unit Aktif')->count()) . ' unit aktif',
             'route' => 'unit-kemitraan.index'],
            ['label' => 'Stokis mitra',      'value' => $this->hitung(fn () => StokisMitra::count()),
             'sub' => null, 'route' => 'stokis.index'],
            ['label' => 'User biMBA Shop',   'value' => $this->hitung(fn () => UserExportBimbaShop::count()),
             'sub' => null, 'route' => 'user.export'],
            ['label' => 'Unit + user cocok', 'value' => $matchOk,
             'sub' => 'dari ' . number_format($matchTotal, 0, ',', '.') . ' data', 'route' => 'unit-kemitraan-user.index'],
            ['label' => 'Pengguna aplikasi', 'value' => $this->hitung(fn () => User::count()),
             'sub' => null, 'route' => 'users.index'],
        ];

        // =====================================================================
        // 8. PERIODE PESANAN MAJALAH, DLC & PASIF
        // =====================================================================
        $periode = [
            ['label' => 'Majalah Korwil',  'value' => $this->hitung(fn () => PesananMajalah::count()),          'route' => 'pesanan-majalah.index'],
            ['label' => 'Majalah Kotamadya', 'value' => $this->hitung(fn () => PesananMajalahKotamadya::count()), 'route' => 'pesanan-majalah-kotamadya.index'],
            ['label' => 'Majalah PUW 1',   'value' => $this->hitung(fn () => PesananMajalahPuw1::count()),      'route' => 'pesanan-majalah-puw1.index'],
            ['label' => 'DLC aktif',       'value' => $this->hitung(fn () => DlcPeriode::where('status', 'aktif')->count()),   'route' => 'import.dlc.index'],
            ['label' => 'Pasif aktif',     'value' => $this->hitung(fn () => PasifPeriode::where('status', 'aktif')->count()), 'route' => 'import.pasif.list'],
        ];

        // =====================================================================
        // Angka ringkas untuk hero
        // =====================================================================
        $stats = [
            'order_hari_ini' => array_sum($orderHariIni),
            'siap_kirim'     => $kirimAktif + $kirimPasif + $kirimManual,
            'proses_qc'      => $qcAktif + $qcPasif + $qcManual,
            'perlu_perhatian' => $totalPerhatian,
        ];

        return view('home', compact(
            'stats', 'pipeline', 'tren', 'sumberOrder', 'perhatian', 'totalPerhatian',
            'gudang', 'keuangan', 'mitra', 'periode'
        ));
    }

    /**
     * Jumlah order masuk per hari (7 hari terakhir, termasuk hari ini),
     * gabungan Aktif, Pasif, Majalah, Modul, dan Sertifikat.
     */
    private function trenOrder7Hari(): array
    {
        $mulai = today()->subDays(6);

        $hari = [];
        for ($i = 0; $i < 7; $i++) {
            $tgl = $mulai->copy()->addDays($i);
            $hari[$tgl->toDateString()] = [
                'tanggal' => $tgl->toDateString(),
                'nama'    => $tgl->copy()->locale('id')->isoFormat('ddd'),
                'tgl'     => $tgl->format('d/m'),
                'value'   => 0,
                'today'   => $tgl->isToday(),
            ];
        }

        $sumber = [
            [JakartaAktif::class, 'tgl_pesan'],
            [JakartaPasif::class, 'tgl_pesan'],
            [ManualOrder::class, 'order_date'],
            [ManualModulOrder::class, 'order_date'],
            [ManualSertifikatOrder::class, 'order_date'],
        ];

        foreach ($sumber as [$model, $kolom]) {
            $rows = $this->aman(function () use ($model, $kolom, $mulai) {
                return $model::whereDate($kolom, '>=', $mulai)
                    ->whereDate($kolom, '<=', today())
                    ->selectRaw("DATE($kolom) as d, COUNT(*) as c")
                    ->groupByRaw("DATE($kolom)")
                    ->pluck('c', 'd');
            }, collect());

            foreach ($rows as $tanggal => $jumlah) {
                $key = Carbon::parse($tanggal)->toDateString();
                if (isset($hari[$key])) {
                    $hari[$key]['value'] += (int) $jumlah;
                }
            }
        }

        return array_values($hari);
    }

    /** Hitung angka; kalau query gagal (tabel/kolom belum ada) kembalikan 0. */
    private function hitung(callable $fn): int
    {
        return (int) $this->aman($fn, 0);
    }

    /**
     * Jalankan query dengan aman supaya satu modul yang error
     * tidak membuat seluruh halaman Home gagal dibuka.
     */
    private function aman(callable $fn, $default = 0)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::warning('Dashboard home: query dilewati - ' . $e->getMessage());
            return $default;
        }
    }
}