<?php

namespace App\Http\Controllers;

use App\Models\PesananMajalah;
use App\Imports\PesananMajalahImport;
use App\Models\UnitKemitraan;
use App\Models\ManualOrder;
use App\Models\UnitNamaMismatch;
use App\Models\JakartaAktif;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    $pesananMajalah->load([
        'kabupaten' => fn ($q) => $q->orderBy('urutan'),
        'kabupaten.units' => fn ($q) => $q->orderBy('no'),
    ]);

    $allUnits = collect();

    foreach ($pesananMajalah->kabupaten as $kabupaten) {
        foreach ($kabupaten->units as $unit) {
            $unit->nama_kabupaten = $kabupaten->nama_kabupaten;
            $unit->contact_person = $kabupaten->contact_person;
            $allUnits->push($unit);
        }
    }

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

    $listNamaUnit       = $allUnits->pluck('nama_unit')->filter()->unique()->sort()->values();
    $listNoCabang       = $allUnits->pluck('no_cabang')->filter()->unique()->sort()->values();
    $listKabupaten      = $allUnits->pluck('nama_kabupaten')->filter()->unique()->sort()->values();
    $listMitraPengelola = $allUnits
        ->pluck('mitra_pengelolaan')
        ->filter(fn ($v) => $v && $v !== '-')
        ->unique()
        ->sort()
        ->values();

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

    // Mismatch dari database
    $mismatches = UnitNamaMismatch::where('pesanan_majalah_id', $pesananMajalah->id)
        ->where('is_resolved', false)
        ->orderBy('no_cab')
        ->get();

    return view('pesanan-majalah.show', [
        'data'               => $pesananMajalah,
        'units'              => $units,
        'totalUnits'         => $totalUnits,
        'totalPesanan'       => $totalPesanan,
        'listNamaUnit'       => $listNamaUnit,
        'listNoCabang'       => $listNoCabang,
        'listKabupaten'      => $listKabupaten,
        'listMitraPengelola' => $listMitraPengelola,
        'mismatches'         => $mismatches,
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
    public function import(Request $request)
{
    $validated = $request->validate([
        'periode' => ['required', 'date_format:Y-m'],
        'file'    => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
    ]);

    $periodeValue = $validated['periode'];
    $tanggal = Carbon::createFromFormat('Y-m', $periodeValue);

    $namaBulan = [
        1  => 'Januari', 2  => 'Februari', 3  => 'Maret', 4  => 'April',
        5  => 'Mei', 6  => 'Juni', 7  => 'Juli', 8  => 'Agustus',
        9  => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $bulan = $namaBulan[(int) $tanggal->month];
    $tahun = $tanggal->year;

    try {
        $pesananMajalah = PesananMajalah::firstOrCreate(
            ['periode' => $periodeValue],
            [
                'judul' => 'Pesanan Majalah',
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        );

        $import = new PesananMajalahImport($pesananMajalah);
        Excel::import($import, $validated['file']);

        $redirect = redirect()
            ->route('pesanan-majalah.show', $pesananMajalah->id)
            ->with(
                'success',
                'Data pesanan majalah periode ' . $bulan . ' ' . $tahun . ' berhasil diimport.'
            );

        if (!empty($import->mismatchList)) {
            $unique = collect($import->mismatchList)
                ->unique('no_cab')
                ->values()
                ->all();
            $redirect->with('unit_nama_mismatch', $unique);
        }

        return $redirect;

    } catch (\Throwable $e) {
        return redirect()
            ->route('pesanan-majalah.index')
            ->with('error', 'Import gagal: ' . $e->getMessage());
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
    
   public function kirimKeJakartaAktif(Request $request, PesananMajalah $pesananMajalah)
{
    $pesananMajalah->load(['kabupaten.units']);

    $created = 0;
    $skipped = 0;
    $skippedList = [];
    $errors = [];

    // Ambil semua no_cabang untuk lookup mitra & nama unit
    $allNoCab = collect();
    foreach ($pesananMajalah->kabupaten as $kab) {
        foreach ($kab->units as $unit) {
            if ($unit->no_cabang) {
                $allNoCab->push(trim($unit->no_cabang));
            }
        }
    }

    $unitKemitraanMap = UnitKemitraan::whereIn('no_cab', $allNoCab->unique())
        ->get()
        ->keyBy(fn ($u) => trim($u->no_cab));

    $productName = 'Pesanan Majalah Edisi ' 
        . ($pesananMajalah->judul ?? '') 
        . ' bulan ' 
        . ($pesananMajalah->bulan ?? '');

    DB::beginTransaction();

    try {
        foreach ($pesananMajalah->kabupaten as $kabupaten) {
            foreach ($kabupaten->units as $unit) {

                if (($unit->jumlah_pesanan ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($unit->nama_unit ?? 'Unit #'.$unit->id) . ' (qty=0)';
                    continue;
                }

                $noCab = trim($unit->no_cabang ?? '');

                // ID Pesan unik: pakai format PM-{periode}-{unit_id} agar tidak bentrok
                // atau pakai no_cabang + periode
                $idPesan = 'PM' . $pesananMajalah->id . '-' . $unit->id;

                // Skip jika sudah ada
                if (JakartaAktif::where('id_pesan', $idPesan)->exists()) {
                    $skipped++;
                    $skippedList[] = ($unit->nama_unit ?? $idPesan) . ' (sudah ada)';
                    continue;
                }

                // Resolve nama unit dari UnitKemitraan
                $namaUnit = $unit->nama_unit ?? '-';
                $mitra    = null;
                if ($noCab && $unitKemitraanMap->has($noCab)) {
                    $uk = $unitKemitraanMap->get($noCab);
                    if (!empty($uk->bimba_aiueo_unit)) {
                        $namaUnit = $uk->bimba_aiueo_unit;
                    }
                    $mitra = $uk->mitra_pengelolaan ?? null;
                }

                $kirim = $unit->alamat_unit ?: $namaUnit;

                try {
                    JakartaAktif::create([
                        'tgl_input'          => now()->format('Y-m-d'),
                        'tgl_pesan'          => now(),
                        'kirim'              => $kirim,
                        'no_telpon'          => $unit->telepon ?? null,
                        'alamat_kirim'       => $unit->alamat_unit ?? null,
                        'kab_kota_provinsi'  => $kabupaten->nama_kabupaten ?? null,
                        'ongkir'             => 0,
                        'nama_unit'          => $namaUnit,
                        'pesanan'            => $productName,
                        'harga'              => 0,
                        'berat'              => 0,
                        'item_qty'           => (int) $unit->jumlah_pesanan,
                        'total'              => 0,
                        'jenis_bank'         => null,
                        'status_pembayaran'  => 'MANUAL',
                        'status_pesan'       => 'pending',
                        'id_pesan'           => $idPesan,
                        'status'             => 'aktif',
                        'payment_date'       => null,
                        'billing_last_name'  => $noCab ?: null,
                        'billing_company'    => $mitra,
                        'status_kirim'       => 'Diambil',
                        'estimasi_print_pl'  => null,
                        'estimasi_persiapan' => null,
                    ]);

                    $created++;
                } catch (\Throwable $e) {
                    $errors[] = ($unit->nama_unit ?? $idPesan) . ': ' . $e->getMessage();
                    Log::error('Kirim ke Jakarta Aktif gagal', [
                        'unit_id' => $unit->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        DB::commit();

        $message = "✅ Berhasil kirim <strong>{$created}</strong> unit ke Jakarta Aktif.";
        if ($skipped > 0) {
            $preview = implode(', ', array_slice($skippedList, 0, 5));
            if (count($skippedList) > 5) $preview .= ' ...';
            $message .= " Dilewati: {$skipped} → {$preview}";
        }
        if (count($errors) > 0) {
            $message .= "<br>❌ Error: " . implode(' | ', array_slice($errors, 0, 3));
        }

        return redirect()
            ->route('pesanan-majalah.show', $pesananMajalah->id)
            ->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()
            ->back()
            ->with('error', 'Gagal kirim ke Jakarta Aktif: ' . $e->getMessage());
    }
}
}