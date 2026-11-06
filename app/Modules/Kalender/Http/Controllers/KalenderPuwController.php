<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kalender\Models\KalenderPuwPeriode;
use App\Modules\Kalender\Models\KalenderPuwUnit;
use App\Modules\Master\Models\UnitKemitraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kalender > PUW (satu halaman, tanpa dropdown), disimpan per edisi (KLD27, KLD28, ...).
 *
 * Sumber data: Database Unit Kemitraan
 *   - Jenis Unit      = kolom status (pilih yang mengandung angka 1)
 *   - No Induk Mitra  = mis. PUW001 (Narno Prasetyo, SE)
 * Saat edisi dibuat, data unit DISALIN ke tabel kalender_puw_unit sehingga edisi lama tidak berubah.
 */
class KalenderPuwController extends Controller
{
    public const QTY_PILIHAN = [1, 2, 3];
    public const QTY_DEFAULT = 2;

    public function index(Request $request)
    {
        $daftarPeriode = KalenderPuwPeriode::orderByDesc('tahun')->orderByDesc('id')->get();

        $periode = $request->filled('periode')
            ? $daftarPeriode->firstWhere('id', (int) $request->query('periode'))
            : null;
        $periode = $periode ?: $daftarPeriode->first();

        $units     = null;
        $totalUnit = 0;
        $totalQty  = 0;
        $perPage   = (int) $request->query('per_page', 100);
        $perPage   = in_array($perPage, [25, 50, 100, 200, 500], true) ? $perPage : 100;

        if ($periode) {
            $query = KalenderPuwUnit::where('periode_id', $periode->id);

            if ($request->filled('q')) {
                $kata = '%' . trim($request->query('q')) . '%';
                $query->where(function ($w) use ($kata) {
                    $w->where('nama_unit', 'like', $kata)
                      ->orWhere('no_cab', 'like', $kata)
                      ->orWhere('kab_kota', 'like', $kata)
                      ->orWhere('kecamatan', 'like', $kata);
                });
            }

            $totalUnit = (clone $query)->count();
            $totalQty  = (int) (clone $query)->sum('qty');
            $units     = $query->orderBy('no')->paginate($perPage)->withQueryString();
        }

        return view('kalender::puw.index', [
            'daftarPeriode' => $daftarPeriode,
            'periode'       => $periode,
            'units'         => $units,
            'totalUnit'     => $totalUnit,
            'totalQty'      => $totalQty,
            'qtyPilihan'    => self::QTY_PILIHAN,
            'perPage'       => $perPage,
            // Pilihan untuk form "Buat Edisi Baru"
            'opsiJenisUnit' => $this->opsiJenisUnit(),
            'opsiMitra'     => $this->opsiMitra(),
            'tahunUsulan'   => (int) date('Y') + 1,
        ]);
    }

    /** Buat edisi baru + tarik (salin) data unit dari Database Unit Kemitraan. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'edisi'          => ['required', 'regex:/^KLD\d{2}$/i', 'unique:kalender_puw_periode,edisi'],
            'qty'            => ['required', 'in:1,2,3'],
            'jenis_unit'     => ['required', 'array', 'min:1'],
            'jenis_unit.*'   => ['string'],
            'no_induk_mitra' => ['required', 'string'],
        ], [
            'edisi.regex'  => 'Format edisi harus KLD diikuti 2 angka, contoh KLD27.',
            'edisi.unique' => 'Edisi tersebut sudah dibuat.',
        ]);

        $edisi = strtoupper($data['edisi']);

        $periode = DB::transaction(function () use ($data, $edisi, $request) {
            $periode = KalenderPuwPeriode::create([
                'edisi'          => $edisi,
                'tahun'          => 2000 + (int) substr($edisi, 3),
                'qty'            => (int) $data['qty'],
                'jenis_unit'     => implode(',', $data['jenis_unit']),
                'no_induk_mitra' => $data['no_induk_mitra'],
                'hanya_aktif'    => $request->boolean('hanya_aktif'),
            ]);

            $this->tarikUnit($periode);

            return $periode;
        });

        return redirect()->route('kalender.puw', ['periode' => $periode->id])
            ->with('success', "Edisi {$edisi} dibuat: {$periode->units()->count()} unit ditarik dari Database Unit.");
    }

    /** Ubah Qty untuk SEMUA unit pada edisi ini. */
    public function updateQty(Request $request, $id)
    {
        $request->validate(['qty' => ['required', 'in:1,2,3']]);

        $periode = KalenderPuwPeriode::findOrFail($id);

        DB::transaction(function () use ($periode, $request) {
            $periode->update(['qty' => (int) $request->qty]);
            KalenderPuwUnit::where('periode_id', $periode->id)->update(['qty' => (int) $request->qty]);
        });

        return redirect()->route('kalender.puw', ['periode' => $periode->id]);
    }

    /** Tarik ulang data unit dari Database Unit (mis. ada unit baru sebelum cetak). */
    public function refresh($id)
    {
        $periode = KalenderPuwPeriode::findOrFail($id);

        DB::transaction(function () use ($periode) {
            KalenderPuwUnit::where('periode_id', $periode->id)->delete();
            $this->tarikUnit($periode);
        });

        return redirect()->route('kalender.puw', ['periode' => $periode->id])
            ->with('success', "Data edisi {$periode->edisi} diperbarui: {$periode->units()->count()} unit.");
    }

    public function destroy($id)
    {
        $periode = KalenderPuwPeriode::findOrFail($id);
        $edisi   = $periode->edisi;
        $periode->delete();   // unit ikut terhapus (cascade)

        return redirect()->route('kalender.puw')->with('success', "Edisi {$edisi} dihapus.");
    }

    // ------------------------------------------------------------------ helper

    private function tarikUnit(KalenderPuwPeriode $periode): void
    {
        $jenis = array_filter(array_map('trim', explode(',', (string) $periode->jenis_unit)));

        $query = UnitKemitraan::query()
            ->whereIn('status', $jenis)
            ->where('no_induk_mitra', $periode->no_induk_mitra)
            ->whereNotNull('bimba_aiueo_unit')
            ->where('bimba_aiueo_unit', '!=', '');

        if ($periode->hanya_aktif) {
            $query->whereRaw('LOWER(TRIM(ops)) = ?', ['active']);
        }

        $no   = 0;
        $rows = [];
        $now  = now();

        foreach ($query->orderBy('no_cab')->get() as $u) {
            $rows[] = [
                'periode_id'   => $periode->id,
                'no'           => ++$no,
                'id_record'    => $u->id_record,
                'no_cab'       => $u->no_cab,
                'nama_unit'    => $u->bimba_aiueo_unit,
                'status'       => $u->status,
                'ops'          => $u->ops,
                'no_telp_unit' => $u->no_telp_unit,
                'alamat_unit'  => $u->alamat_unit,
                'rt'           => $u->rt,
                'rw'           => $u->rw,
                'provinsi'     => $u->provinsi,
                'kab_kota'     => $u->kab_kota,
                'kecamatan'    => $u->kecamatan,
                'kel_desa'     => $u->kel_desa,
                'kode_pos'     => $u->kode_pos,
                'qty'          => $periode->qty,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            KalenderPuwUnit::insert($chunk);
        }
    }

    /** Jenis Unit (kolom status) yang mengandung angka 1 dipilih otomatis. */
    private function opsiJenisUnit(): array
    {
        return UnitKemitraan::query()
            ->whereNotNull('status')->where('status', '!=', '')
            ->distinct()->orderBy('status')->pluck('status')
            ->map(fn ($s) => ['nilai' => $s, 'pilih' => (bool) preg_match('/1/', $s)])
            ->all();
    }

    /** No Induk Mitra beserta nama mitra; PUW001 dipilih otomatis. */
    private function opsiMitra(): array
    {
        return UnitKemitraan::query()
            ->whereNotNull('no_induk_mitra')->where('no_induk_mitra', '!=', '')
            ->select('no_induk_mitra', DB::raw('MAX(nama_mitra) as nama_mitra'))
            ->groupBy('no_induk_mitra')
            ->orderBy('no_induk_mitra')
            ->get()
            ->map(fn ($m) => [
                'nilai' => $m->no_induk_mitra,
                'label' => trim($m->no_induk_mitra . ($m->nama_mitra ? ' - ' . $m->nama_mitra : '')),
                'pilih' => stripos($m->no_induk_mitra, 'PUW001') === 0,
            ])
            ->all();
    }
}
