<?php

namespace App\Http\Controllers;

use App\Models\PesananMajalah;
use App\Models\PesananMajalahKotamadya;
use App\Models\PesananMajalahUnitKotamadya;
use App\Imports\PesananMajalahKotamadyaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PesananMajalahKotamadyaController extends Controller
{
    /**
     * INDEX – Daftar Periode (dari tabel pesanan_majalah)
     */
    public function index(Request $request)
{
    $query = PesananMajalah::query()
        ->with(['kotamadya.units'])
        ->whereHas('kotamadya'); // hanya yang punya data kotamadya

    // Filter
    if ($request->filled('judul')) {
        $query->where('judul', 'like', '%' . $request->judul . '%');
    }
    if ($request->filled('bulan')) {
        $query->where('bulan', 'like', '%' . $request->bulan . '%');
    }
    if ($request->filled('tahun')) {
        $query->where('tahun', $request->tahun);
    }
    if ($request->filled('periode')) {
        $query->where('periode', 'like', '%' . $request->periode . '%');
    }

    $data = $query
        ->orderByDesc('tahun')
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    // Hitung total unit & total pesanan untuk setiap baris
    $data->getCollection()->transform(function ($item) {
        $item->total_units = $item->kotamadya->sum(function ($kotamadya) {
            return $kotamadya->units->count();
        });

        $item->total_pesanan = $item->kotamadya->sum(function ($kotamadya) {
            return $kotamadya->units->sum('jumlah_pesanan');
        });

        return $item;
    });

    return view('pesanan-majalah-kotamadya.index', compact('data'));
}


    /**
     * SHOW – Detail Periode + semua Kotamadya + Unit
     */
    public function show($id)
    {
        $data = PesananMajalah::with([
            'kotamadya' => function ($q) {
                $q->orderBy('urutan')->orderBy('nama_kotamadya');
            },
            'kotamadya.units' => function ($q) {
                $q->orderBy('no');
            }
        ])->findOrFail($id);

        $totalUnits = $data->kotamadya->sum(fn ($k) => $k->units->count());
        $totalPesanan = $data->kotamadya->sum(fn ($k) => $k->units->sum('jumlah_pesanan'));

        return view('pesanan-majalah-kotamadya.show', compact(
            'data',
            'totalUnits',
            'totalPesanan'
        ));
    }


    /**
     * DESTROY PERIODE – Hapus semua kotamadya di dalam periode ini
     */
    public function destroyPeriode($id)
    {
        $pesanan = PesananMajalah::findOrFail($id);

        // Hapus semua kotamadya (unit ikut terhapus jika cascade)
        $pesanan->kotamadya()->delete();

        return redirect()
            ->route('pesanan-majalah-kotamadya.index')
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

            // Cari atau buat record di tabel pesanan_majalah
            $pesananMajalah = PesananMajalah::firstOrCreate(
                ['periode' => $periodeValue],
                [
                    'judul' => 'PESANAN MAJALAH KOTAMADYA',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]
            );

            // Jalankan import
            $import = new PesananMajalahKotamadyaImport($pesananMajalah);
            Excel::import($import, $validated['file']);

            $pesan = "Import berhasil.\n\n"
                . "Kotamadya baru     : {$import->getKotamadyaBaru()}\n"
                . "Kotamadya sudah ada: {$import->getKotamadyaLama()}\n"
                . "Unit baru          : {$import->getUnitBaru()}\n"
                . "Unit diperbarui    : {$import->getUnitDiupdate()}\n"
                . "Baris dilewati     : {$import->getBarisDilewati()}";

            return redirect()
                ->route('pesanan-majalah-kotamadya.show', $pesananMajalah->id)
                ->with('success', $pesan);

        } catch (\Throwable $e) {
            Log::error('Import Pesanan Majalah Kotamadya gagal', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()
                ->route('pesanan-majalah-kotamadya.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }


    // ===== Method CRUD Kotamadya (opsional) =====

    public function destroy($id)
    {
        $kotamadya = PesananMajalahKotamadya::findOrFail($id);
        $pesananId = $kotamadya->pesanan_majalah_id;
        $kotamadya->delete();

        return redirect()
            ->route('pesanan-majalah-kotamadya.show', $pesananId)
            ->with('success', 'Data Kotamadya berhasil dihapus.');
    }
}