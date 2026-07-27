<?php

namespace App\Http\Controllers;

use App\Models\PesananMajalah;
use App\Imports\PesananMajalahImport;
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
        | FILTER JUDUL
        |--------------------------------------------------------------------------
        */

        if ($request->filled('judul')) {

            $query->where(
                'judul',
                'like',
                '%' . $request->input('judul') . '%'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BULAN
        |--------------------------------------------------------------------------
        */

        if ($request->filled('bulan')) {

            $query->where(
                'bulan',
                'like',
                '%' . $request->input('bulan') . '%'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FILTER TAHUN
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tahun')) {

            $query->where(
                'tahun',
                $request->input('tahun')
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FILTER PERIODE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('periode')) {

            $query->where(
                'periode',
                'like',
                '%' . $request->input('periode') . '%'
            );

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
        | DAFTAR PERIODE UNTUK IMPORT
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | Januari 2025
        | Februari 2025
        | ...
        | Juli 2026
        | ...
        |
        | Periode dipilih manual oleh user.
        |
        */

        $periodeImport = $this->daftarPeriodeImport();


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'pesanan-majalah.index',
            compact(
                'data',
                'periodeImport'
            )
        );
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


    /**
     * ============================================================
     * SHOW
     * ============================================================
     *
     * Menampilkan detail satu periode.
     */
    public function show(
        PesananMajalah $pesananMajalah
    ) {

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
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'pesanan-majalah.show',
            [
                'data' => $pesananMajalah
            ]
        );
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