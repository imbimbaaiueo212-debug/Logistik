<?php

namespace App\Http\Controllers;

use App\Models\PesananMajalah;
use App\Imports\PesananMajalahImport;
use App\Models\UnitKemitraan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PesananMajalahController extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     *
     * Menampilkan daftar periode pesanan majalah.
     *
     * Sekaligus mengirim daftar periode yang tersedia
     * untuk dropdown Import Excel.
     */
   public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | QUERY DATA PERIODE
    |--------------------------------------------------------------------------
    */
    $query = PesananMajalah::query();

    /*
    |--------------------------------------------------------------------------
    | FILTER (exact match agar cocok dengan Select2)
    |--------------------------------------------------------------------------
    */
    if ($request->filled('judul')) {
        $query->where('judul', $request->input('judul'));
    }

    if ($request->filled('bulan')) {
        $query->where('bulan', $request->input('bulan'));
    }

    if ($request->filled('tahun')) {
        $query->where('tahun', $request->input('tahun'));
    }

    if ($request->filled('periode')) {
        $query->where('periode', $request->input('periode'));
    }

    /*
    |--------------------------------------------------------------------------
    | DATA TABEL
    |--------------------------------------------------------------------------
    */
    $data = $query
        ->with([
            'kabupaten' => function ($q) {
                $q->orderBy('urutan');
            },
            'kabupaten.units' => function ($q) {
                $q->orderBy('no');
            },
        ])
        ->orderByDesc('tahun')
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | LIST UNIK UNTUK SELECT2
    |--------------------------------------------------------------------------
    */
    $listJudul   = PesananMajalah::select('judul')->distinct()->orderBy('judul')->pluck('judul')->filter();
    $listBulan   = PesananMajalah::select('bulan')->distinct()->orderBy('bulan')->pluck('bulan')->filter();
    $listTahun   = PesananMajalah::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun')->filter();
    $listPeriode = PesananMajalah::select('periode')->distinct()->orderByDesc('periode')->pluck('periode')->filter();

    /*
    |--------------------------------------------------------------------------
    | DAFTAR PERIODE UNTUK IMPORT
    |--------------------------------------------------------------------------
    */
    $periodeImport = $this->daftarPeriodeImport();

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    return view('pesanan-majalah.index', compact(
        'data',
        'periodeImport',
        'listJudul',
        'listBulan',
        'listTahun',
        'listPeriode'
    ));
}


    /**
     * ============================================================
     * CREATE
     * ============================================================
     *
     * Menampilkan form tambah periode manual.
     */
    public function create()
    {
        return view(
            'pesanan-majalah.create'
        );
    }


    /**
     * ============================================================
     * STORE
     * ============================================================
     *
     * Menyimpan periode baru secara manual.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'judul' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bulan' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'periode' => [
                'nullable',
                'string',
                'max:7',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | SIMPAN DATA
        |--------------------------------------------------------------------------
        */

        $data = PesananMajalah::create(
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'pesanan-majalah.show',
                $data->id
            )
            ->with(
                'success',
                'Periode pesanan majalah berhasil dibuat.'
            );
    }


public function show(Request $request, PesananMajalah $pesananMajalah)
{
    /*
    |--------------------------------------------------------------------------
    | LOAD RELATIONSHIP
    |--------------------------------------------------------------------------
    */
    $pesananMajalah->load([
        'kabupaten' => function ($q) {
            $q->orderBy('urutan');
        },
        'kabupaten.units' => function ($q) {
            $q->orderBy('no');
        },
    ]);

    /*
    |--------------------------------------------------------------------------
    | KUMPULKAN SEMUA UNIT
    |--------------------------------------------------------------------------
    */
    $allUnits = collect();

    foreach ($pesananMajalah->kabupaten as $kabupaten) {
        foreach ($kabupaten->units as $unit) {
            $unit->nama_kabupaten = $kabupaten->nama_kabupaten;
            $unit->contact_person = $kabupaten->contact_person;
            $allUnits->push($unit);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA UNIT KEMITRAAN (match no_cabang ↔ no_cab)
    |--------------------------------------------------------------------------
    */
    $noCabangs = $allUnits
        ->pluck('no_cabang')
        ->filter()
        ->map(fn ($v) => trim($v))
        ->unique()
        ->values();

    $unitKemitraanMap = UnitKemitraan::whereIn('no_cab', $noCabangs)
        ->get()
        ->keyBy(fn ($u) => trim($u->no_cab));

    $allUnits = $allUnits->map(function ($unit) use ($unitKemitraanMap) {
        $noCab = trim($unit->no_cabang ?? '');
        $uk = $unitKemitraanMap->get($noCab);

        if ($uk) {
            $unit->mitra_pengelolaan   = $uk->mitra_pengelolaan ?? '-';
            $unit->dari_unit_kemitraan = true;
        } else {
            $unit->mitra_pengelolaan   = '-';
            $unit->dari_unit_kemitraan = false;
        }

        return $unit;
    });

    /*
    |--------------------------------------------------------------------------
    | LIST UNIK UNTUK SELECT2
    |--------------------------------------------------------------------------
    */
    $listNamaUnit = $allUnits->pluck('nama_unit')->filter()->unique()->sort()->values();
    $listNoCabang = $allUnits->pluck('no_cabang')->filter()->unique()->sort()->values();
    $listKabupaten = $allUnits->pluck('nama_kabupaten')->filter()->unique()->sort()->values();
    $listMitraPengelola = $allUnits
        ->pluck('mitra_pengelolaan')
        ->filter(fn ($v) => $v && $v !== '-')
        ->unique()
        ->sort()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | TERAPKAN FILTER
    |--------------------------------------------------------------------------
    */
    $units = $allUnits;

    if ($request->filled('nama_unit')) {
        $units = $units->where('nama_unit', $request->nama_unit);
    }

    if ($request->filled('no_cabang')) {
        $units = $units->where('no_cabang', $request->no_cabang);
    }

    if ($request->filled('kabupaten')) {
        $units = $units->where('nama_kabupaten', $request->kabupaten);
    }

    if ($request->filled('mitra_pengelolaan')) {
        $units = $units->where('mitra_pengelolaan', $request->mitra_pengelolaan);
    }

    $units = $units->values();

    $totalUnits   = $units->count();
    $totalPesanan = $units->sum(fn ($u) => (float) ($u->jumlah_pesanan ?? 0));

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    return view('pesanan-majalah.show', [
        'data'               => $pesananMajalah,
        'units'              => $units,
        'totalUnits'         => $totalUnits,
        'totalPesanan'       => $totalPesanan,
        'listNamaUnit'       => $listNamaUnit,
        'listNoCabang'       => $listNoCabang,
        'listKabupaten'      => $listKabupaten,
        'listMitraPengelola' => $listMitraPengelola,
    ]);
}


    /**
     * ============================================================
     * EDIT
     * ============================================================
     *
     * Menampilkan form edit periode.
     */
    public function edit(
        PesananMajalah $pesananMajalah
    ) {

        return view(
            'pesanan-majalah.edit',
            [
                'data' => $pesananMajalah
            ]
        );
    }


    /**
     * ============================================================
     * UPDATE
     * ============================================================
     *
     * Mengupdate data periode.
     */
    public function update(
        Request $request,
        PesananMajalah $pesananMajalah
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'judul' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bulan' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'periode' => [
                'nullable',
                'string',
                'max:7',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $pesananMajalah->update(
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'pesanan-majalah.show',
                $pesananMajalah->id
            )
            ->with(
                'success',
                'Data periode berhasil diperbarui.'
            );
    }


    /**
     * ============================================================
     * DESTROY
     * ============================================================
     *
     * Menghapus satu periode.
     *
     * Jika database menggunakan cascadeOnDelete(),
     * maka kabupaten dan unit di dalamnya ikut terhapus.
     */
    public function destroy(
        PesananMajalah $pesananMajalah
    ) {

        /*
        |--------------------------------------------------------------------------
        | DELETE
        |--------------------------------------------------------------------------
        */

        $pesananMajalah->delete();


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'pesanan-majalah.index'
            )
            ->with(
                'success',
                'Data pesanan majalah berhasil dihapus.'
            );
    }


    /**
     * ============================================================
     * IMPORT EXCEL
     * ============================================================
     *
     * Route:
     *
     * POST /pesanan-majalah/import
     *
     * Form mengirim:
     *
     * periode = 2026-07
     * file    = file Excel
     *
     * Contoh:
     *
     * Juli 2026
     *      ↓
     * 2026-07
     *      ↓
     * Cari periode 2026-07
     *      ↓
     * Jika belum ada → buat
     * Jika sudah ada → gunakan
     *      ↓
     * Import Excel
     */
    public function import(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDASI INPUT
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'periode' => [
                'required',
                'date_format:Y-m',
            ],

            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | AMBIL PERIODE
        |--------------------------------------------------------------------------
        */

        $periodeValue = $validated['periode'];


        /*
        |--------------------------------------------------------------------------
        | PARSE TANGGAL
        |--------------------------------------------------------------------------
        */

        $tanggal = Carbon::createFromFormat(
            'Y-m',
            $periodeValue
        );


        /*
        |--------------------------------------------------------------------------
        | NAMA BULAN INDONESIA
        |--------------------------------------------------------------------------
        |
        | Tidak bergantung pada locale server.
        |
        */

        $namaBulan = [

            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',

        ];


        /*
        |--------------------------------------------------------------------------
        | BULAN DAN TAHUN
        |--------------------------------------------------------------------------
        */

        $bulan = $namaBulan[
            (int) $tanggal->month
        ];

        $tahun = $tanggal->year;


        /*
        |--------------------------------------------------------------------------
        | PROSES IMPORT
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | CARI ATAU BUAT PERIODE
            |--------------------------------------------------------------------------
            |
            | Contoh:
            |
            | periode = 2026-07
            |
            | Jika sudah ada:
            | gunakan data yang ada.
            |
            | Jika belum ada:
            | buat data baru.
            |
            */

            $pesananMajalah = PesananMajalah::firstOrCreate(

                [
                    'periode' => $periodeValue,
                ],

                [
                    'judul' => 'Pesanan Majalah',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]

            );


            /*
            |--------------------------------------------------------------------------
            | IMPORT FILE EXCEL
            |--------------------------------------------------------------------------
            */

            Excel::import(

                new PesananMajalahImport(
                    $pesananMajalah
                ),

                $validated['file']

            );


            /*
            |--------------------------------------------------------------------------
            | REDIRECT KE DETAIL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'pesanan-majalah.show',
                    $pesananMajalah->id
                )
                ->with(

                    'success',

                    'Data pesanan majalah periode '
                    . $bulan
                    . ' '
                    . $tahun
                    . ' berhasil diimport.'

                );


        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ERROR IMPORT
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'pesanan-majalah.index'
                )
                ->with(
                    'error',
                    'Import gagal: '
                    . $e->getMessage()
                );

        }
    }


    /**
     * ============================================================
     * DAFTAR PERIODE IMPORT
     * ============================================================
     *
     * Membuat daftar pilihan periode untuk dropdown import.
     *
     * Range:
     *
     * 12 bulan sebelumnya
     * +
     * bulan sekarang
     * +
     * 12 bulan berikutnya
     *
     * Total:
     *
     * 25 pilihan bulan.
     *
     * Contoh jika sekarang Juli 2026:
     *
     * Juli 2025
     * Agustus 2025
     * ...
     * Juni 2026
     * Juli 2026
     * Agustus 2026
     * ...
     * Juli 2027
     */
    private function daftarPeriodeImport()
    {
        /*
        |--------------------------------------------------------------------------
        | ARRAY HASIL
        |--------------------------------------------------------------------------
        */

        $periode = [];


        /*
        |--------------------------------------------------------------------------
        | BULAN SEKARANG
        |--------------------------------------------------------------------------
        */

        $tanggalMulai = now()
            ->startOfMonth();


        /*
        |--------------------------------------------------------------------------
        | RANGE 25 BULAN
        |--------------------------------------------------------------------------
        |
        | -12 = 12 bulan sebelumnya
        |
        | 0 = bulan sekarang
        |
        | +12 = 12 bulan berikutnya
        |
        */

        for (
            $i = -12;
            $i <= 12;
            $i++
        ) {

            /*
            |--------------------------------------------------------------------------
            | HITUNG TANGGAL
            |--------------------------------------------------------------------------
            */

            $tanggal = $tanggalMulai
                ->copy()
                ->addMonths($i);


            /*
            |--------------------------------------------------------------------------
            | NAMA BULAN INDONESIA
            |--------------------------------------------------------------------------
            */

            $namaBulan = [

                1  => 'Januari',
                2  => 'Februari',
                3  => 'Maret',
                4  => 'April',
                5  => 'Mei',
                6  => 'Juni',
                7  => 'Juli',
                8  => 'Agustus',
                9  => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',

            ];


            /*
            |--------------------------------------------------------------------------
            | BULAN ANGKA
            |--------------------------------------------------------------------------
            */

            $bulanAngka = (int) $tanggal->month;


            /*
            |--------------------------------------------------------------------------
            | TAHUN
            |--------------------------------------------------------------------------
            */

            $tahun = $tanggal->year;


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA
            |--------------------------------------------------------------------------
            */

            $periode[] = [

                'value' => $tanggal->format(
                    'Y-m'
                ),

                'label' => $namaBulan[
                    $bulanAngka
                ] . ' ' . $tahun,

                'bulan' => $namaBulan[
                    $bulanAngka
                ],

                'tahun' => $tahun,

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return $periode;
    }
}