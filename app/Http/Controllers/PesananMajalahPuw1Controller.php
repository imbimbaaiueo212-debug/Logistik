<?php

namespace App\Http\Controllers;

use App\Models\PesananMajalahPuw1;
use App\Models\PesananMajalahUnitPuw1;
use App\Imports\PesananMajalahPuw1Import;
use App\Models\UnitNamaMismatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PesananMajalahPuw1Controller extends Controller
{
    /**
     * ============================================================
     * INDEX
     * ============================================================
     */
    public function index(Request $request)
{
    $query = PesananMajalahPuw1::query();

    if ($request->filled('judul')) {
        $query->where('judul', 'like', '%' . $request->judul . '%');
    }

    if ($request->filled('bulan')) {
        $query->where('bulan', $request->bulan);
    }

    if ($request->filled('tahun')) {
        $query->where('tahun', $request->tahun);
    }

    if ($request->filled('periode')) {
        $query->where('periode', $request->periode);
    }

    $data = $query
        ->withCount('units')
        ->withSum('units', 'jumlah_pesanan')
        ->orderByDesc('tahun')
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    // Data untuk Select2
    $listJudul = PesananMajalahPuw1::select('judul')
        ->whereNotNull('judul')
        ->where('judul', '!=', '')
        ->distinct()
        ->orderBy('judul')
        ->pluck('judul');

    $listBulan = PesananMajalahPuw1::select('bulan')
        ->whereNotNull('bulan')
        ->where('bulan', '!=', '')
        ->distinct()
        ->orderBy('bulan')
        ->pluck('bulan');

    $listTahun = PesananMajalahPuw1::select('tahun')
        ->whereNotNull('tahun')
        ->distinct()
        ->orderByDesc('tahun')
        ->pluck('tahun');

    $listPeriode = PesananMajalahPuw1::select('periode')
        ->whereNotNull('periode')
        ->where('periode', '!=', '')
        ->distinct()
        ->orderByDesc('periode')
        ->pluck('periode');

    $periodeImport = $this->daftarPeriodeImport();

    return view('pesanan-majalah-puw1.index', compact(
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
     * SHOW
     * ============================================================
     */
    public function show(Request $request, $id)
{
    $data = PesananMajalahPuw1::findOrFail($id);

    $unitsQuery = $data->units()->orderBy('no');

    if ($request->filled('nama_unit')) {
        $unitsQuery->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('no_cabang')) {
        $unitsQuery->where('no_cabang', $request->no_cabang);
    }
    if ($request->filled('kabupaten_kota')) {
        $unitsQuery->where('kabupaten_kota', $request->kabupaten_kota);
    }

    $units = $unitsQuery->get();

    $listNamaUnit = $data->units()
        ->select('nama_unit')->distinct()->orderBy('nama_unit')->pluck('nama_unit');

    $listNoCabang = $data->units()
        ->select('no_cabang')
        ->whereNotNull('no_cabang')->where('no_cabang', '!=', '')
        ->distinct()->orderBy('no_cabang')->pluck('no_cabang');

    $listKabupaten = $data->units()
        ->select('kabupaten_kota')
        ->whereNotNull('kabupaten_kota')->where('kabupaten_kota', '!=', '')
        ->distinct()->orderBy('kabupaten_kota')->pluck('kabupaten_kota');

    // Mismatch PUW1 (by periode + sumber)
    $mismatches = UnitNamaMismatch::where('periode', $data->periode)
        ->where('sumber', 'import_puw1')
        ->where('is_resolved', false)
        ->orderBy('no_cab')
        ->get();

    return view('pesanan-majalah-puw1.show', compact(
        'data',
        'units',
        'listNamaUnit',
        'listNoCabang',
        'listKabupaten',
        'mismatches'
    ));
}


    /**
     * ============================================================
     * CREATE
     * ============================================================
     */
    public function create()
    {
        return view(
            'pesanan-majalah-puw1.create'
        );
    }


    /**
     * ============================================================
     * STORE
     * ============================================================
     */
    public function store(Request $request)
    {
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

            'contact_person' => [
                'nullable',
                'string',
            ],

            'telepon_contact_person' => [
                'nullable',
                'string',
            ],

        ]);


        $data =
            PesananMajalahPuw1::create(
                $validated
            );


        return redirect()
            ->route(
                'pesanan-majalah-puw1.show',
                $data->id
            )
            ->with(
                'success',
                'Periode pesanan majalah PUW1 berhasil dibuat.'
            );
    }


    /**
     * ============================================================
     * EDIT
     * ============================================================
     */
    public function edit($id)
    {
        $data =
            PesananMajalahPuw1::findOrFail(
                $id
            );


        return view(
            'pesanan-majalah-puw1.edit',
            compact('data')
        );
    }


    /**
     * ============================================================
     * UPDATE
     * ============================================================
     */
    public function update(
        Request $request,
        $id
    ) {

        $data =
            PesananMajalahPuw1::findOrFail(
                $id
            );


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

            'contact_person' => [
                'nullable',
                'string',
            ],

            'telepon_contact_person' => [
                'nullable',
                'string',
            ],

        ]);


        $data->update(
            $validated
        );


        return redirect()
            ->route(
                'pesanan-majalah-puw1.show',
                $data->id
            )
            ->with(
                'success',
                'Data periode PUW1 berhasil diperbarui.'
            );
    }


    /**
     * ============================================================
     * DESTROY
     * ============================================================
     */
    public function destroy($id)
    {
        $data =
            PesananMajalahPuw1::findOrFail(
                $id
            );


        $data->delete();


        return redirect()
            ->route(
                'pesanan-majalah-puw1.index'
            )
            ->with(
                'success',
                'Data pesanan majalah PUW1 berhasil dihapus.'
            );
    }


    public function import(Request $request)
{
    $validated = $request->validate([
        'periode' => ['required', 'date_format:Y-m'],
        'file'    => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
    ]);

    $periodeValue = $validated['periode'];
    $tanggal = Carbon::createFromFormat('Y-m', $periodeValue);

    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $bulan = $namaBulan[(int) $tanggal->month];
    $tahun = $tanggal->year;

    try {
        DB::beginTransaction();

        $pesananMajalah = PesananMajalahPuw1::firstOrCreate(
            ['periode' => $periodeValue],
            [
                'judul' => 'PESANAN MAJALAH SAHABAT biMBA PUW I',
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        );

        $import = new PesananMajalahPuw1Import($pesananMajalah);
        Excel::import($import, $validated['file']);

        DB::commit();

        $redirect = redirect()
            ->route('pesanan-majalah-puw1.show', $pesananMajalah->id)
            ->with(
                'success',
                'Import berhasil. '
                . 'Unit baru: ' . $import->unitBaru
                . ' | Unit diperbarui: ' . $import->unitDiperbarui
                . ' | Baris dilewati: ' . $import->barisDilewati
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
        DB::rollBack();

        return redirect()
            ->route('pesanan-majalah-puw1.index')
            ->with('error', 'Import gagal: ' . $e->getMessage());
    }
}


    /**
     * ============================================================
     * DAFTAR PERIODE IMPORT
     * ============================================================
     */
    private function daftarPeriodeImport()
    {
        $periode = [];


        $tanggalMulai =
            now()->startOfMonth();


        for (
            $i = -12;
            $i <= 12;
            $i++
        ) {

            $tanggal =
                $tanggalMulai
                    ->copy()
                    ->addMonths($i);


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


            $periode[] = [

                'value'
                    => $tanggal->format('Y-m'),

                'label'
                    => $namaBulan[
                        (int) $tanggal->month
                    ]
                    . ' '
                    . $tanggal->year,

                'bulan'
                    => $namaBulan[
                        (int) $tanggal->month
                    ],

                'tahun'
                    => $tanggal->year,

            ];

        }


        return $periode;
    }
}