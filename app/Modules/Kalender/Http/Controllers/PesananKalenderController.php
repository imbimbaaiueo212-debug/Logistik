<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Kalender\Models\PesananKalender;
use App\Modules\Kalender\Imports\PesananKalenderImport;
use App\Modules\Master\Models\UnitKemitraan;
use App\Modules\Import\Models\ManualOrder;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Kalender\Models\PesananKalenderMismatch;
use App\Modules\Order\Models\JakartaAktif;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PesananKalenderController extends Controller
{
    public function index(Request $request)
    {
        $query = PesananKalender::query();

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

        $data = $query
            ->with([
                'kabupaten' => fn ($q) => $q->orderBy('urutan'),
                'kabupaten.units' => fn ($q) => $q->orderBy('no'),
            ])
            ->orderByDesc('tahun')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $listJudul   = PesananKalender::select('judul')->distinct()->orderBy('judul')->pluck('judul')->filter();
        $listBulan   = PesananKalender::select('bulan')->distinct()->orderBy('bulan')->pluck('bulan')->filter();
        $listTahun   = PesananKalender::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun')->filter();
        $listPeriode = PesananKalender::select('periode')->distinct()->orderByDesc('periode')->pluck('periode')->filter();

        $periodeImport = $this->daftarPeriodeImport();

        return view('kalender::pesanan-kalender.index', compact(
            'data',
            'periodeImport',
            'listJudul',
            'listBulan',
            'listTahun',
            'listPeriode'
        ));
    }

    public function create()
    {
        return view('kalender::pesanan-kalender.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'   => ['nullable', 'string', 'max:255'],
            'bulan'   => ['nullable', 'string', 'max:255'],
            'tahun'   => ['required', 'integer', 'min:2000', 'max:2100'],
            'periode' => ['nullable', 'string', 'max:7'],
        ]);

        $data = PesananKalender::create($validated);

        return redirect()
            ->route('pesanan-kalender.show', $data->id)
            ->with('success', 'Periode pesanan kalender berhasil dibuat.');
    }

    public function show(Request $request, PesananKalender $pesananKalender)
{
    $pesananKalender->load([
        'kabupaten' => fn ($q) => $q->orderBy('urutan'),
        'kabupaten.units' => fn ($q) => $q->orderBy('no'),
    ]);

    $allUnits = collect();

    foreach ($pesananKalender->kabupaten as $kabupaten) {
        foreach ($kabupaten->units as $unit) {
            $unit->nama_kabupaten = $kabupaten->nama_kabupaten;
            $unit->contact_person = $kabupaten->contact_person;
            $allUnits->push($unit);
        }
    }

    // Ambil no_cabang & normalisasi (hilangkan leading zero)
    $noCabangs = $allUnits
        ->pluck('no_cabang')
        ->filter()
        ->map(fn ($v) => ltrim(trim($v), '0') ?: '0')
        ->unique()
        ->values();

    // Query ke UnitKemitraan pakai versi ber-padding 5 digit
    $noCabangsPadded = $noCabangs->map(fn ($v) => str_pad($v, 5, '0', STR_PAD_LEFT));

    $unitKemitraanMap = UnitKemitraan::whereIn('no_cab', $noCabangsPadded)
        ->get()
        ->keyBy(fn ($u) => ltrim(trim($u->no_cab), '0') ?: '0'); // key tanpa leading zero

    $shopSet = app(ShopMatcher::class)->setKalender($pesananKalender->tahun);

    $allUnits = $allUnits->map(function ($unit) use ($unitKemitraanMap, $shopSet) {
        $noCab = ltrim(trim($unit->no_cabang ?? ''), '0') ?: '0';
        $uk = $unitKemitraanMap->get($noCab);

        $unit->status_shop = isset($shopSet[ShopMatcher::noCabKey($unit->no_cabang)])
            ? 'Cocok'
            : 'Menunggu biMBA Shop';

        $unit->mitra_pengelolaan   = $uk?->mitra_pengelolaan ?: '-';
        $unit->dari_unit_kemitraan = (bool) $uk;

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

    $totalSiapKirim = $units->where('status_shop', 'Cocok')->where('jumlah_pesanan', '>', 0)->count();

    $totalUnits   = $units->count();
    $totalPesanan = $units->sum(fn ($u) => (float) ($u->jumlah_pesanan ?? 0));

    // ===== PERBAIKAN: unique berdasarkan no_cab =====
    $mismatches = PesananKalenderMismatch::where('pesanan_kalender_id', $pesananKalender->id)
        ->where('is_resolved', false)
        ->orderBy('no_cab')
        ->get()
        ->unique(function ($item) {
            // normalisasi supaya 01002 dan 1002 dianggap sama
            return ltrim(trim((string) $item->no_cab), '0') ?: '0';
        })
        ->values();

    return view('kalender::pesanan-kalender.show', [
        'data'               => $pesananKalender,
        'units'              => $units,
        'totalUnits'         => $totalUnits,
        'totalSiapKirim'     => $totalSiapKirim,
        'totalPesanan'       => $totalPesanan,
        'listNamaUnit'       => $listNamaUnit,
        'listNoCabang'       => $listNoCabang,
        'listKabupaten'      => $listKabupaten,
        'listMitraPengelola' => $listMitraPengelola,
        'mismatches'         => $mismatches,
    ]);
}

    public function edit(PesananKalender $pesananKalender)
    {
        return view('kalender::pesanan-kalender.edit', ['data' => $pesananKalender]);
    }

    public function update(Request $request, PesananKalender $pesananKalender)
    {
        $validated = $request->validate([
            'judul'   => ['nullable', 'string', 'max:255'],
            'bulan'   => ['nullable', 'string', 'max:255'],
            'tahun'   => ['required', 'integer', 'min:2000', 'max:2100'],
            'periode' => ['nullable', 'string', 'max:7'],
        ]);

        $pesananKalender->update($validated);

        return redirect()
            ->route('pesanan-kalender.show', $pesananKalender->id)
            ->with('success', 'Data periode berhasil diperbarui.');
    }

    public function destroy(PesananKalender $pesananKalender)
    {
        $pesananKalender->delete();

        return redirect()
            ->route('pesanan-kalender.index')
            ->with('success', 'Data pesanan kalender berhasil dihapus.');
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
        $pesananKalender = PesananKalender::firstOrCreate(
            ['periode' => $periodeValue],
            [
                'judul' => 'Pesanan Kalender',
                'bulan' => $bulan,
                'tahun' => $tahun,
            ]
        );

        $import = new PesananKalenderImport($pesananKalender);
        Excel::import($import, $validated['file']);

        // =====================================================
        // SIMPAN MISMATCH (unik berdasarkan no_cab)
        // =====================================================
        $uniqueMismatches = collect();

        if (!empty($import->mismatchList)) {
            $uniqueMismatches = collect($import->mismatchList)
                ->map(function ($m) {
                    // Normalisasi no_cab (hilangkan leading zero)
                    $noCab = $m['no_cab'] ?? null;
                    if ($noCab !== null && $noCab !== '') {
                        $noCab = ltrim(trim((string) $noCab), '0') ?: '0';
                    }

                    return [
                        'no_cab'      => $noCab,
                        'nama_excel'  => $m['nama_excel'] ?? null,
                        'nama_master' => $m['nama_master'] ?? null,
                    ];
                })
                ->filter(fn ($m) => !empty($m['no_cab'])) // buang yang no_cab kosong
                ->unique('no_cab')
                ->values();

            foreach ($uniqueMismatches as $m) {
                PesananKalenderMismatch::updateOrCreate(
                    [
                        'pesanan_kalender_id' => $pesananKalender->id,
                        'no_cab'             => $m['no_cab'],
                    ],
                    [
                        'nama_excel'  => $m['nama_excel'],
                        'nama_master' => $m['nama_master'],
                        'is_resolved' => false,
                    ]
                );
            }
        }

        $redirect = redirect()
            ->route('pesanan-kalender.show', $pesananKalender->id)
            ->with('success', "Data pesanan kalender periode {$bulan} {$tahun} berhasil diimport.");

        // Flash hanya data yang sudah unique
        if ($uniqueMismatches->isNotEmpty()) {
            $redirect->with('unit_nama_mismatch', $uniqueMismatches->all());
        }

        return $redirect;

    } catch (\Throwable $e) {
        return redirect()
            ->route('pesanan-kalender.index')
            ->with('error', 'Import gagal: ' . $e->getMessage());
    }
}
    private function daftarPeriodeImport(): array
    {
        $periode = [];
        $tanggalMulai = now()->startOfMonth();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        for ($i = -12; $i <= 12; $i++) {
            $tanggal = $tanggalMulai->copy()->addMonths($i);
            $bulanAngka = (int) $tanggal->month;
            $tahun = $tanggal->year;

            $periode[] = [
                'value' => $tanggal->format('Y-m'),
                'label' => $namaBulan[$bulanAngka] . ' ' . $tahun,
                'bulan' => $namaBulan[$bulanAngka],
                'tahun' => $tahun,
            ];
        }

        return $periode;
    }

    public function kirimKeJakartaAktif(Request $request, PesananKalender $pesananKalender)
    {
        $pesananKalender->load(['kabupaten.units']);

        $created = 0;
        $skipped = 0;
        $skippedList = [];
        $errors = [];

        $allNoCab = collect();
        foreach ($pesananKalender->kabupaten as $kab) {
            foreach ($kab->units as $unit) {
                if ($unit->no_cabang) {
                    $allNoCab->push(trim($unit->no_cabang));
                }
            }
        }

        $unitKemitraanMap = UnitKemitraan::whereIn('no_cab', $allNoCab->map(fn ($v) => UnitKemitraan::padNoCab($v))->unique()->values())
            ->get()
            ->keyBy(fn ($u) => trim($u->no_cab));

        // Gerbang biMBA Shop: hanya unit yang no cabangnya sudah ada di order Shop (kalender, tahun sama)
        $shopSet = app(ShopMatcher::class)->setKalender($pesananKalender->tahun);

        $productName = 'Pesanan Kalender '
            . ($pesananKalender->judul ?? '')
            . ' bulan '
            . ($pesananKalender->bulan ?? '');

        DB::beginTransaction();

        try {
            foreach ($pesananKalender->kabupaten as $kabupaten) {
                foreach ($kabupaten->units as $unit) {

                    if (($unit->jumlah_pesanan ?? 0) <= 0) {
                        $skipped++;
                        $skippedList[] = ($unit->nama_unit ?? 'Unit #' . $unit->id) . ' (qty=0)';
                        continue;
                    }

                    $noCab = trim($unit->no_cabang ?? '');

                    if (! isset($shopSet[ShopMatcher::noCabKey($noCab)])) {
                        $skipped++;
                        $skippedList[] = ($unit->nama_unit ?? 'Unit #' . $unit->id) . ' (menunggu biMBA Shop)';
                        continue;
                    }

                    $idPesan = 'PK' . $pesananKalender->id . '-' . $unit->id;

                    if (JakartaAktif::where('id_pesan', $idPesan)->exists()) {
                        $skipped++;
                        $skippedList[] = ($unit->nama_unit ?? $idPesan) . ' (sudah ada)';
                        continue;
                    }

                    $namaUnit = $unit->nama_unit ?? '-';
                    $mitra = null;

                    $noCabKey = UnitKemitraan::padNoCab($noCab);

                    if ($noCabKey && $unitKemitraanMap->has($noCabKey)) {
                        $uk = $unitKemitraanMap->get($noCabKey);
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
                if (count($skippedList) > 5) {
                    $preview .= ' ...';
                }
                $message .= " Dilewati: {$skipped} → {$preview}";
            }
            if (count($errors) > 0) {
                $message .= '<br>❌ Error: ' . implode(' | ', array_slice($errors, 0, 3));
            }

            return redirect()
                ->route('pesanan-kalender.show', $pesananKalender->id)
                ->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Gagal kirim ke Jakarta Aktif: ' . $e->getMessage());
        }
    }
    public function updateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:50',
    ]);

    $item = \App\Modules\Kalender\Models\PesananKalender::findOrFail($id);
    
    $item->update([
        'no_ps' => $request->no_ps,
    ]);

    // ===== SINKRONKAN KE PUW1 =====
    if ($item->periode) {
        \App\Modules\Kalender\Models\PesananKalenderPuw1::where('periode', $item->periode)
            ->update([
                'no_ps' => $request->no_ps,
            ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil disimpan',
        'no_ps'   => $item->no_ps,
    ]);
}
}