<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Kalender\Models\PesananKalender;
use App\Modules\Kalender\Models\PesananKalenderKotamadya;
use App\Modules\Kalender\Models\PesananKalenderUnitKotamadya;
use App\Modules\Kalender\Models\PesananKalenderMismatch;
use App\Modules\Kalender\Imports\PesananKalenderKotamadyaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PesananKalenderKotamadyaController extends Controller
{
    /**
     * INDEX – Daftar Periode (dari tabel pesanan_kalender)
     */
    public function index(Request $request)
    {
        $query = PesananKalender::query()
            ->with(['kotamadya.units'])
            ->whereHas('kotamadya');

        if ($request->filled('judul')) {
            $query->where('judul', $request->judul);
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
            ->orderByDesc('tahun')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $data->getCollection()->transform(function ($item) {
            $item->total_units = $item->kotamadya->sum(function ($kotamadya) {
                return $kotamadya->units->count();
            });

            $item->total_pesanan = $item->kotamadya->sum(function ($kotamadya) {
                return $kotamadya->units->sum('jumlah_pesanan');
            });

            return $item;
        });

        $baseQuery = PesananKalender::query()->whereHas('kotamadya');

        $listJudul   = (clone $baseQuery)->select('judul')->distinct()->orderBy('judul')->pluck('judul')->filter();
        $listBulan   = (clone $baseQuery)->select('bulan')->distinct()->orderBy('bulan')->pluck('bulan')->filter();
        $listTahun   = (clone $baseQuery)->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun')->filter();
        $listPeriode = (clone $baseQuery)->select('periode')->distinct()->orderByDesc('periode')->pluck('periode')->filter();

        return view('kalender::pesanan-kalender-kotamadya.index', compact(
            'data',
            'listJudul',
            'listBulan',
            'listTahun',
            'listPeriode'
        ));
    }

    /**
     * SHOW – Detail Periode + semua Kotamadya + Unit (dengan filter + mismatch)
     */
    public function show(Request $request, $id)
    {
        $data = PesananKalender::with([
            'kotamadya' => function ($q) {
                $q->orderBy('urutan')->orderBy('nama_kotamadya');
            },
            'kotamadya.units' => function ($q) {
                $q->orderBy('no');
            }
        ])->findOrFail($id);

        $allUnits = collect();
        foreach ($data->kotamadya as $kotamadya) {
            foreach ($kotamadya->units as $unit) {
                $unit->nama_kotamadya = $kotamadya->nama_kotamadya;
                $unit->contact_person = $kotamadya->contact_person;
                $unit->telepon_contact_person = $kotamadya->telepon_contact_person;
                $allUnits->push($unit);
            }
        }

        $listNamaUnit  = $allUnits->pluck('nama_unit')->filter()->unique()->sort()->values();
        $listNoCabang  = $allUnits->pluck('no_cabang')->filter()->unique()->sort()->values();
        $listKotamadya = $allUnits->pluck('nama_kotamadya')->filter()->unique()->sort()->values();

        $units = $allUnits;

        if ($request->filled('nama_unit')) {
            $units = $units->where('nama_unit', $request->nama_unit);
        }
        if ($request->filled('no_cabang')) {
            $units = $units->where('no_cabang', $request->no_cabang);
        }
        if ($request->filled('kotamadya')) {
            $units = $units->where('nama_kotamadya', $request->kotamadya);
        }

        $units = $units->values();

        $totalUnits   = $units->count();
        $totalPesanan = $units->sum('jumlah_pesanan');

        // Mismatch dari database (sumber kotamadya)
        $mismatches = PesananKalenderMismatch::where('pesanan_kalender_id', $data->id)
            ->where('sumber', 'import_kotamadya')
            ->where('is_resolved', false)
            ->orderBy('no_cab')
            ->get();

        return view('kalender::pesanan-kalender-kotamadya.show', compact(
            'data',
            'units',
            'totalUnits',
            'totalPesanan',
            'listNamaUnit',
            'listNoCabang',
            'listKotamadya',
            'mismatches'
        ));
    }

    /**
     * DESTROY PERIODE – Hapus semua kotamadya di dalam periode ini
     */
    public function destroyPeriode($id)
    {
        $pesanan = PesananKalender::findOrFail($id);
        $pesanan->kotamadya()->delete();

        return redirect()
            ->route('pesanan-kalender-kotamadya.index')
            ->with('success', 'Seluruh data Kotamadya pada periode ini berhasil dihapus.');
    }

    /**
     * IMPORT EXCEL
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'file'    => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $periodeValue = $validated['periode'];
            $tanggal = Carbon::createFromFormat('Y-m', $periodeValue);

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            $bulan = $namaBulan[(int) $tanggal->month];
            $tahun = $tanggal->year;

            $pesananKalender = PesananKalender::firstOrCreate(
                ['periode' => $periodeValue],
                [
                    'judul' => 'PESANAN KALENDER KOTAMADYA',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]
            );

            $import = new PesananKalenderKotamadyaImport($pesananKalender);
            Excel::import($import, $validated['file']);

            $pesan = "Import berhasil.\n\n"
                . "Kotamadya baru     : {$import->getKotamadyaBaru()}\n"
                . "Kotamadya sudah ada: {$import->getKotamadyaLama()}\n"
                . "Unit baru          : {$import->getUnitBaru()}\n"
                . "Unit diperbarui    : {$import->getUnitDiupdate()}\n"
                . "Baris dilewati     : {$import->getBarisDilewati()}";

            $redirect = redirect()
                ->route('pesanan-kalender-kotamadya.show', $pesananKalender->id)
                ->with('success', $pesan);

            // Flash mismatch list (untuk modal langsung setelah import)
            if (!empty($import->mismatchList)) {
                $unique = collect($import->mismatchList)
                    ->unique('no_cab')
                    ->values()
                    ->all();
                $redirect->with('unit_nama_mismatch', $unique);
            }

            return $redirect;

        } catch (\Throwable $e) {
            Log::error('Import Pesanan Kalender Kotamadya gagal', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('pesanan-kalender-kotamadya.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $kotamadya = PesananKalenderKotamadya::findOrFail($id);
        $pesananId = $kotamadya->pesanan_kalender_id;
        $kotamadya->delete();

        return redirect()
            ->route('pesanan-kalender-kotamadya.show', $pesananId)
            ->with('success', 'Data Kotamadya berhasil dihapus.');
    }

    public function updateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:50',
    ]);

    $item = PesananKalender::findOrFail($id);
    $item->update([
        'no_ps' => $request->no_ps,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil disimpan',
        'no_ps'   => $item->no_ps,
    ]);
}
}