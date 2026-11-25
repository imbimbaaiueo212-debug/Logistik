<?php

namespace App\Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Master\Models\PenyesuaianHarga;
use App\Modules\Master\Models\PenyesuaianHargaBatch;
use App\Modules\Master\Models\PenyesuaianHargaFile;
use App\Modules\Master\Models\Product;
use App\Modules\Master\Services\PenyesuaianHargaPdfParser;
use App\Modules\Master\Support\PenyesuaianHargaData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Penyesuaian Harga: harga beli baru dari percetakan (PDF) -> harga jual otomatis -> ACC -> publikasi.
 *
 * Alur:
 *  1. Import 3 file: surat permohonan + PDF data perubahan (Modul & Raport, Seragam/Tas/KPK).
 *     Data masuk sebagai satu batch berstatus "menunggu"; belum ada harga di Master Product yang berubah.
 *  2. Halaman menampilkan perbandingan harga 2024, harga beli aplikasi, harga 2026 (pengajuan percetakan),
 *     dan harga jual otomatis (Modul: beli x 1,49; selain Modul: beli x 1,20).
 *  3. Admin menyetujui (ACC) -> harga beli & harga jual di Master Product diperbarui (dipublikasikan),
 *     atau menolak -> tidak ada harga yang berubah.
 */
class PenyesuaianHargaController extends Controller
{
    /** true = admin yang meng-import boleh meng-ACC batch-nya sendiri. Ubah ke false untuk pemisahan tugas. */
    private const BOLEH_SETUJUI_SENDIRI = true;

    /** Daftar perubahan harga (per tanggal berlaku), dengan filter tahun / bulan / status. */
    public function index(Request $request)
    {
        return $this->daftar($request, false);
    }

    /** Daftar perubahan harga versi JUAL (data sama dengan Beli, tampilannya khusus harga jual). */
    public function indexJual(Request $request)
    {
        return $this->daftar($request, true);
    }

    /** Detail perubahan harga jual: harga jual lama, baru, dan persentase kenaikannya. */
    public function showJual(Request $request, int $id)
    {
        $batch = PenyesuaianHargaBatch::with(['files', 'supplier'])->findOrFail($id);
        $data  = $this->dataTampilan($request, $batch);

        $bolehReviewJual = $batch->isMenungguJual() && Auth::user()->isAdmin();

        return view('master::penyesuaian_harga.show_jual', $data + [
            'batch'           => $batch,
            'bolehReviewJual' => $bolehReviewJual,
            'pengaliModul' => $batch->pengaliModul(),
            'pengaliLain'  => $batch->pengaliLain(),
        ]);
    }

    /** Simpan pengali harga jual (diisi manual): Modul mis. 1,49 dan selain Modul mis. 1,2. Hanya sebelum di-ACC. */
    public function simpanPengali(Request $request, int $id)
    {
        $batch = PenyesuaianHargaBatch::findOrFail($id);

        if (! $batch->isBelumFinal()) {
            return back()->with('error', 'Pengali tidak bisa diubah karena import ini sudah diproses.');
        }

        // Boleh ketik koma (1,49) maupun titik (1.49).
        // Kosong atau 0 = tanpa pengali (x1): harga jual mengikuti harga beli.
        $bersih = function ($v) {
            $v = str_replace(',', '.', trim((string) $v));
            return ($v === '' || (is_numeric($v) && (float) $v == 0.0)) ? '1' : $v;
        };
        $request->merge([
            'pengali_modul' => $bersih($request->input('pengali_modul')),
            'pengali_lain'  => $bersih($request->input('pengali_lain')),
        ]);

        $data = $request->validate([
            'pengali_modul' => ['required', 'numeric', 'min:1', 'max:5'],
            'pengali_lain'  => ['required', 'numeric', 'min:1', 'max:5'],
        ], [
            '*.numeric' => 'Pengali harus berupa angka, mis. 1,49',
            '*.min'     => 'Pengali minimal 1 (kosongkan atau isi 0 supaya harga jual sama dengan harga beli).',
            '*.max'     => 'Pengali maksimal 5.',
        ]);

        $batch->update([
            'pengali_modul' => $data['pengali_modul'],
            'pengali_lain'  => $data['pengali_lain'],
        ]);

        return redirect()->route('penyesuaian-harga-jual.show', $batch->id)->with('success', 'Pengali harga jual disimpan. Harga jual dihitung ulang.');
    }

    /** Unduh PDF harga jual sebelum & sesudah (filter ikut terbawa). */
    public function pdfJual(Request $request)
    {
        $batch = PenyesuaianHargaBatch::findOrFail((int) $request->get('batch'));
        $data  = $this->dataTampilan($request, $batch);

        return Pdf::loadView('master::penyesuaian_harga.pdf_jual', $data + ['batch' => $batch])
            ->setPaper('a4', 'landscape')
            ->stream('Penyesuaian-Harga-Jual-' . $batch->id . '.pdf');
    }

    private function daftar(Request $request, bool $jual)
    {
        $q = PenyesuaianHargaBatch::with('supplier')->withCount('rows')->orderByDesc('berlaku_mulai')->orderByDesc('id');
        $tgl = 'COALESCE(berlaku_mulai, DATE(created_at))';

        if ($request->filled('tahun')) {
            $q->whereRaw("YEAR($tgl) = ?", [(int) $request->get('tahun')]);
        }
        if ($request->filled('bulan')) {
            $q->whereRaw("MONTH($tgl) = ?", [(int) $request->get('bulan')]);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->get('status'));
        }

        $batches = $q->paginate(20)->withQueryString();

        // Nomor "perubahan ke-N" per tahun berlaku (dari semua data, bukan hanya halaman ini)
        $semua = PenyesuaianHargaBatch::get(['id', 'berlaku_mulai', 'created_at']);
        $urut = [];
        $perTahun = $semua->sortBy(fn ($b) => [($b->berlaku_mulai ?? $b->created_at)->format('Y-m-d'), $b->id])
            ->groupBy(fn ($b) => ($b->berlaku_mulai ?? $b->created_at)->format('Y'));
        foreach ($perTahun as $grup) {
            foreach ($grup->values() as $i => $b) {
                $urut[$b->id] = $i + 1;
            }
        }
        $tahunList = $semua->map(fn ($b) => (int) ($b->berlaku_mulai ?? $b->created_at)->format('Y'))->unique()->sortDesc()->values();

        return view('master::penyesuaian_harga.index', [
            'batches'   => $batches,
            'urutBatch' => $urut,
            'tahunList' => $tahunList,
            'modeJual'  => $jual,
        ]);
    }

    /** Halaman form import. */
    public function create()
    {
        return view('master::penyesuaian_harga.create', [
            'punyaMenunggu' => PenyesuaianHargaBatch::whereIn('status', [PenyesuaianHargaBatch::MENUNGGU, PenyesuaianHargaBatch::MENUNGGU_JUAL])->exists(),
            'parserSiap'    => class_exists(\Smalot\PdfParser\Parser::class),
            'suppliers'     => \App\Modules\Master\Models\Supplier::orderBy('nama_supplier')->get(['id', 'nama_supplier']),
        ]);
    }

    /** Detail satu perubahan harga: info surat, ACC, tabel perbandingan. */
    public function show(Request $request, int $id)
    {
        $batch = PenyesuaianHargaBatch::with(['files', 'creator', 'reviewer', 'supplier'])->findOrFail($id);
        $data  = $this->dataTampilan($request, $batch);

        $bolehReview = $batch->isMenunggu() && Auth::user()->isAdmin()
            && (self::BOLEH_SETUJUI_SENDIRI || $batch->created_by !== Auth::id());

        return view('master::penyesuaian_harga.show', $data + [
            'batch'        => $batch,
            'bolehReview'  => $bolehReview,
            'suppliers'    => \App\Modules\Master\Models\Supplier::orderBy('nama_supplier')->get(['id', 'nama_supplier']),
            'pengaliModul' => $batch->pengaliModul(),
            'pengaliLain'  => $batch->pengaliLain(),
        ]);
    }

    /** Unduh PDF tabel yang sedang tampil (filter ikut terbawa). */
    public function pdf(Request $request)
    {
        $batch = PenyesuaianHargaBatch::findOrFail((int) $request->get('batch'));
        $data  = $this->dataTampilan($request, $batch);

        return Pdf::loadView('master::penyesuaian_harga.pdf', $data + ['batch' => $batch])
            ->setPaper('a4', 'landscape')
            ->stream('Penyesuaian-Harga' . ($batch ? '-' . $batch->id : '') . '.pdf');
    }

    /** Import 3 file: surat permohonan + data perubahan Modul & Raport + data perubahan Seragam/Tas/KPK. */
    public function import(Request $request, PenyesuaianHargaPdfParser $parser)
    {
        $request->validate([
            'supplier_id'   => ['required', 'integer', 'exists:suppliers,id'],
            'surat'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'data'          => ['required', 'array', 'min:1', 'max:10'],
            'data.*'        => ['file', 'mimes:pdf,xls,xlsx,csv,txt', 'max:10240'],
            'lampiran'      => ['nullable', 'array', 'max:10'],
            'lampiran.*'    => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
            'judul'         => ['nullable', 'string', 'max:150'],
            'nomor_surat'   => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'berlaku_mulai' => ['required', 'date'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ], [
            'supplier_id.required'            => 'Pilih supplier untuk RA ini.',
            'supplier_id.exists'              => 'Supplier tidak ditemukan di Master Suplier.',
            'surat.required'                  => 'Surat permohonan wajib diupload.',
            'berlaku_mulai.required'          => 'Tanggal berlaku harga wajib diisi (mis. 1 Desember).',
            'lampiran.*.mimes'                => 'Lampiran harus berformat PDF, JPG, PNG, Word, atau Excel.',
            'lampiran.*.max'                  => 'Setiap lampiran maksimal 10 MB.',
            'surat.mimes'                     => 'Surat harus berformat PDF, JPG, PNG, Word, atau Excel.',
            'data.required'                   => 'File data perubahan harga wajib diupload.',
            'data.*.mimes'                    => 'Data perubahan harus berformat Excel (xls/xlsx), CSV, atau PDF.',
            '*.max'                           => 'Ukuran tiap file maksimal 10 MB.',
        ]);

        if (PenyesuaianHargaBatch::whereIn('status', [PenyesuaianHargaBatch::MENUNGGU, PenyesuaianHargaBatch::MENUNGGU_JUAL])->exists()) {
            return back()->withInput()->with('error', 'Masih ada import yang menunggu ACC (Beli atau Jual). Setujui atau tolak dulu sebelum import baru.');
        }

        // 1) Baca semua PDF dulu; kalau gagal tidak ada yang tersimpan
        // Satu file berisi semua perubahan harga; kelompok (Modul / Seragam) ditebak per baris, bawaannya Modul & Raport
        $sumber = [];
        foreach ((array) $request->file('data') as $i => $f) {
            $sumber['data' . $i] = [$f, PenyesuaianHargaData::GRUP_MODUL];
        }
        $baris = [];
        $peringatan = [];
        try {
            foreach ($sumber as $jenis => [$file, $grup]) {
                if (! $file) {
                    continue;
                }
                $ext = strtolower($file->getClientOriginalExtension());
                $hasil = in_array($ext, ['xls', 'xlsx', 'csv', 'txt'], true)
                    ? $parser->bacaExcel($file->getRealPath(), $ext)
                    : $parser->bacaFile($file->getRealPath());
                if (count($hasil['rows']) === 0) {
                    \Illuminate\Support\Facades\Log::warning('Import penyesuaian harga: tidak ada baris terbaca', [
                        'file' => $file->getClientOriginalName(), 'contoh' => $hasil['teks_contoh'] ?? '',
                    ]);
                    if (! isset($hasil['teks_panjang'])) {
                        $sebab = 'Pastikan ada kolom nama produk, Harga 2024, dan Harga 2026 (angka harga minimal 500).';
                    } elseif ($hasil['teks_panjang'] === 0) {
                        $sebab = 'PDF ini tidak berisi teks (kemungkinan hasil scan/gambar).';
                    } else {
                        $sebab = 'Teks yang terbaca (400 karakter pertama): ' . $hasil['teks_contoh'];
                    }

                    return back()->withInput()->with('error', "Tidak ada baris harga yang terbaca dari file \"" . $file->getClientOriginalName() . "\". {$sebab}");
                }
                foreach ($hasil['rows'] as $r) {
                    // Kelompok ditebak dari judul bagian, nama sheet, lalu nama produk (kaos/seragam/tas/KPK = Seragam); selain itu Modul & Raport
                    $g = $this->grupDariTeks($r['bagian'] ?? '') ?? $this->grupDariTeks($r['sheet'] ?? '')
                        ?? $this->grupDariTeks($r['nama']) ?? $grup;
                    $baris[] = [
                        'no'         => $r['no'],
                        'nama'       => $r['nama'],
                        'harga_2024' => $r['harga_2024'],
                        'harga_2026' => $r['harga_2026'],
                        'grup'       => $g,
                    ];
                }
                foreach ($hasil['peringatan'] as $w) {
                    $peringatan[] = "{$grup}: {$w}";
                }
            }

            // Nomor urut harus unik per kelompok; kalau ada yang kembar (mis. dua sheet), nomori ulang
            foreach (array_unique(array_column($baris, 'grup')) as $g) {
                $idx = array_keys(array_filter($baris, fn ($b) => $b['grup'] === $g));
                $nos = array_map(fn ($i) => $baris[$i]['no'], $idx);
                if (count($nos) !== count(array_unique($nos))) {
                    foreach ($idx as $urut => $i) {
                        $baris[$i]['no'] = $urut + 1;
                    }
                }
            }

            // Nomor/tanggal surat: isi otomatis dari PDF surat bila kolomnya dikosongkan
            $nomor   = $request->input('nomor_surat');
            $tanggal = $request->input('tanggal_surat');
            $surat   = $request->file('surat');
            if ((! $nomor || ! $tanggal) && strtolower($surat->getClientOriginalExtension()) === 'pdf') {
                try {
                    $info = $parser->infoSurat($parser->teksDariFile($surat->getRealPath()));
                    $nomor   = $nomor ?: $info['nomor_surat'];
                    $tanggal = $tanggal ?: $info['tanggal_surat'];
                } catch (\Throwable $e) {
                    // surat scan / tidak terbaca: isi manual saja
                }
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // 2) Simpan batch, berkas, dan baris data
        $batch = DB::transaction(function () use ($request, $sumber, $baris, $nomor, $tanggal) {
            $batch = PenyesuaianHargaBatch::create([
                'no_ra'         => PenyesuaianHargaBatch::buatNoRa(),
                'supplier_id'   => (int) $request->input('supplier_id'),
                'judul'         => $request->filled('judul') ? trim($request->input('judul')) : null,
                'nomor_surat'   => $nomor,
                'tanggal_surat' => $tanggal,
                'berlaku_mulai' => $request->input('berlaku_mulai'),
                'catatan'       => $request->input('catatan'),
                'status'        => PenyesuaianHargaBatch::MENUNGGU,
                'created_by'    => Auth::id(),
            ]);

            $berkas = ['surat' => $request->file('surat')]
                + array_map(fn ($s) => $s[0], $sumber);
            foreach ($berkas as $jenis => $f) {
                if (! $f) {
                    continue;
                }
                $batch->files()->create([
                    'jenis'  => str_starts_with($jenis, 'data') ? 'data' : $jenis,
                    'path'   => $f->store('penyesuaian-harga', 'local'),
                    'nama'   => $f->getClientOriginalName(),
                    'ukuran' => $f->getSize(),
                ]);
            }

            foreach ((array) $request->file('lampiran') as $f) {
                if (! $f) {
                    continue;
                }
                $batch->files()->create([
                    'jenis'  => 'lampiran',
                    'path'   => $f->store('penyesuaian-harga', 'local'),
                    'nama'   => $f->getClientOriginalName(),
                    'ukuran' => $f->getSize(),
                ]);
            }

            foreach ($baris as $r) {
                $batch->rows()->create([
                    'grup'       => $r['grup'],
                    'no'         => $r['no'],
                    'nama'       => $r['nama'],
                    'harga_2024' => $r['harga_2024'],
                    'harga_2026' => $r['harga_2026'],
                ]);
            }

            // Simpan pemetaan produk yang cocok otomatis
            $rows = $batch->rows()->get();
            foreach ($this->produkPerBaris($rows) as $rowId => $info) {
                if ($info['produk']) {
                    PenyesuaianHarga::whereKey($rowId)->update(['product_id' => $info['produk']->id]);
                }
            }

            return $batch;
        });

        $pesan = 'Import berhasil: <b>' . count($baris) . '</b> item terbaca. Periksa perbandingan di bawah, lalu ACC agar harga dipublikasikan.';
        if ($peringatan) {
            $pesan .= '<br>Perhatian: ' . e(implode(' ', $peringatan));
        }
        if (! $batch->nomor_surat) {
            $pesan .= '<br>Nomor surat tidak terbaca otomatis (surat berupa gambar/scan). Isi lewat tombol <b>Ubah info</b> di bawah.';
        }

        return redirect()->route('penyesuaian-harga.show', $batch->id)->with('success', $pesan);
    }

    /** ACC BELI: baris terpilih disetujui. Harga di Master Product BELUM berubah; menunggu ACC Jual. */
    public function setujui(Request $request, int $id)
    {
        $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
            'ids'     => ['required', 'array', 'min:1'],
            'ids.*'   => ['integer'],
        ], ['ids.required' => 'Centang minimal satu produk yang mau disetujui.', 'ids.min' => 'Centang minimal satu produk yang mau disetujui.']);

        $dipilih = array_map('intval', $request->input('ids'));

        try {
            $jumlah = 0;

            DB::transaction(function () use ($request, $id, $dipilih, &$jumlah) {
                $batch = PenyesuaianHargaBatch::lockForUpdate()->findOrFail($id);

                if (! $batch->isMenunggu()) {
                    throw new \RuntimeException('ACC Beli untuk import ini sudah diproses.');
                }
                if (! self::BOLEH_SETUJUI_SENDIRI && $batch->created_by === Auth::id()) {
                    throw new \RuntimeException('Import tidak boleh di-ACC oleh orang yang meng-importnya.');
                }

                $jumlah = $batch->rows()->whereIn('id', $dipilih)->whereNull('diterapkan_pada')->update(['disetujui_beli_pada' => now()]);

                if ($jumlah === 0) {
                    throw new \RuntimeException('Tidak ada produk terpilih yang bisa disetujui.');
                }

                $batch->update([
                    'status'       => PenyesuaianHargaBatch::MENUNGGU_JUAL,
                    'acc_beli_by'  => Auth::id(),
                    'acc_beli_at'  => now(),
                    'catatan_beli' => $request->input('catatan'),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "ACC Beli selesai untuk <b>{$jumlah}</b> produk. Harga di Master Product belum berubah &mdash; menunggu ACC Jual di menu Penyesuaian Harga &rarr; Jual.");
    }

    /** ACC JUAL: harga beli (= harga 2026) dan harga jual baru dipublikasikan ke Master Product. Hanya baris yang sudah di-ACC Beli. */
    public function setujuiJual(Request $request, int $id)
    {
        $request->validate([
            'catatan' => ['nullable', 'string', 'max:500'],
            'ids'     => ['required', 'array', 'min:1'],
            'ids.*'   => ['integer'],
        ], ['ids.required' => 'Centang minimal satu produk yang harganya mau diubah.', 'ids.min' => 'Centang minimal satu produk yang harganya mau diubah.']);

        $dipilih = array_map('intval', $request->input('ids'));

        try {
            $jumlah = 0;
            $dilewati = 0;
            $sudah = 0;
            $tidakDipilih = 0;

            DB::transaction(function () use ($request, $id, $dipilih, &$jumlah, &$dilewati, &$sudah, &$tidakDipilih) {
                $batch = PenyesuaianHargaBatch::lockForUpdate()->findOrFail($id);

                if (! $batch->isMenungguJual()) {
                    throw new \RuntimeException('Import ini belum di-ACC Beli atau sudah diproses.');
                }
                if (! self::BOLEH_SETUJUI_SENDIRI && $batch->created_by === Auth::id()) {
                    throw new \RuntimeException('Import tidak boleh di-ACC oleh orang yang meng-importnya.');
                }

                $rows = $batch->rows()->get();
                $peta = $this->produkPerBaris($rows);

                foreach ($rows as $r) {
                    $p = $peta[$r->id]['produk'] ?? null;
                    if (! $p) {
                        $dilewati++;
                        continue;
                    }
                    $p = Product::lockForUpdate()->find($p->id);
                    if (! $p) {
                        $dilewati++;
                        continue;
                    }

                    // Harga Beli sudah sama dengan 2026: tidak diubah, hanya diberi keterangan di Master Product
                    if ($this->sudahMengikuti2026($r, $p)) {
                        $r->update(['product_id' => $p->id]);
                        $p->forceFill(['harga_diubah_pada' => now(), 'harga_batch_id' => $batch->id])->save();
                        $sudah++;
                        continue;
                    }

                    // Bulk: hanya baris yang sudah di-ACC Beli dan dicentang yang diubah
                    if (! $r->disetujui_beli_pada || ! in_array($r->id, $dipilih, true)) {
                        $tidakDipilih++;
                        continue;
                    }

                    $jualBaru = Product::hitungHargaJual(
                        (float) $r->harga_2026,
                        $p->jenis,
                        (int) $p->isi,
                        $r->grup === PenyesuaianHargaData::GRUP_MODUL,
                        $batch->pengaliModul(),
                        $batch->pengaliLain()
                    );

                    $r->harga_beli_sebelum = $p->harga_beli;
                    $r->harga_jual_sebelum = $p->harga_jual;
                    $r->harga_jual_baru    = $jualBaru;
                    $r->product_id         = $p->id;
                    $r->diterapkan_pada    = now();
                    $r->save();

                    $p->harga_beli = $r->harga_2026;
                    $p->harga_jual = $jualBaru;
                    $p->forceFill(['harga_diubah_pada' => now(), 'harga_batch_id' => $batch->id])->save();
                    $jumlah++;
                }

                if ($jumlah === 0) {
                    throw new \RuntimeException('Tidak ada produk terpilih yang bisa diubah (belum cocok dengan Master Product atau harganya sudah mengikuti 2026).');
                }

                $batch->update([
                    // simpan pengali yang dipakai supaya angka "harga asli" di halaman Jual tetap bisa dilihat
                    'pengali_modul'  => $batch->pengaliModul(),
                    'pengali_lain'   => $batch->pengaliLain(),
                    'status'         => PenyesuaianHargaBatch::DIPUBLIKASI,
                    'reviewed_by'    => Auth::id(),
                    'reviewed_at'    => now(),
                    'published_at'   => now(),
                    'catatan_review' => $request->input('catatan'),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = "Disetujui. Harga beli dan harga jual <b>{$jumlah}</b> produk terpilih di Master Product sudah dipublikasikan.";
        if ($sudah) {
            $msg .= " {$sudah} produk harganya sudah mengikuti 2026 (tidak diubah, diberi keterangan).";
        }
        if ($tidakDipilih) {
            $msg .= " {$tidakDipilih} produk tidak dipilih sehingga harganya tetap.";
        }
        if ($dilewati) {
            $msg .= " {$dilewati} item dilewati karena produknya belum ketemu.";
        }

        return back()->with('success', $msg);
    }

    /** Tolak: tidak ada harga di Master Product yang berubah. */
    public function tolak(Request $request, int $id)
    {
        $request->validate(['catatan' => ['required', 'string', 'min:5', 'max:500']], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
            'catatan.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $batch = PenyesuaianHargaBatch::findOrFail($id);
        if (! $batch->isBelumFinal()) {
            return back()->with('error', 'Import ini sudah diproses.');
        }

        $batch->update([
            'status'         => PenyesuaianHargaBatch::DITOLAK,
            'reviewed_by'    => Auth::id(),
            'reviewed_at'    => now(),
            'catatan_review' => $request->input('catatan'),
        ]);

        return back()->with('success', 'Ditolak. Harga di Master Product tidak berubah.');
    }

    /** Ubah judul, nomor/tanggal surat, dan tanggal berlaku (info saja; tidak mengubah harga). */
    public function ubahInfo(Request $request, int $id)
    {
        $data = $request->validate([
            'supplier_id'   => ['nullable', 'integer', 'exists:suppliers,id'],
            'judul'         => ['nullable', 'string', 'max:150'],
            'nomor_surat'   => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'berlaku_mulai' => ['required', 'date'],
        ], ['berlaku_mulai.required' => 'Tanggal berlaku wajib diisi.']);

        $batch = PenyesuaianHargaBatch::findOrFail($id);
        $batch->update([
            'supplier_id'   => $data['supplier_id'] ?? $batch->supplier_id,
            'judul'         => filled($data['judul'] ?? null) ? trim($data['judul']) : null,
            'nomor_surat'   => filled($data['nomor_surat'] ?? null) ? trim($data['nomor_surat']) : null,
            'tanggal_surat' => $data['tanggal_surat'] ?? null,
            'berlaku_mulai' => $data['berlaku_mulai'],
        ]);

        return back()->with('success', 'Info perubahan harga diperbarui.');
    }

    /** Hapus import yang masih menunggu / ditolak (beserta berkasnya). Yang sudah dipublikasi tidak bisa dihapus. */
    public function hapus(int $id)
    {
        $batch = PenyesuaianHargaBatch::with('files')->findOrFail($id);
        if ($batch->status === PenyesuaianHargaBatch::DIPUBLIKASI) {
            return back()->with('error', 'Import yang sudah dipublikasikan tidak bisa dihapus.');
        }

        DB::transaction(function () use ($batch) {
            foreach ($batch->files as $f) {
                Storage::disk('local')->delete($f->path);
            }
            $batch->files()->delete();
            $batch->rows()->delete();
            $batch->delete();
        });

        return redirect()->route('penyesuaian-harga.index')->with('success', 'Import dihapus. Harga di Master Product tidak berubah.');
    }

    /** Tambah surat / lampiran / bukti ke import yang sudah ada (tidak mengubah harga, boleh kapan saja). */
    public function tambahLampiran(Request $request, int $id)
    {
        $batch = PenyesuaianHargaBatch::findOrFail($id);

        $request->validate([
            'lampiran'   => ['required', 'array', 'min:1', 'max:10'],
            'lampiran.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ], [
            'lampiran.required' => 'Pilih minimal satu file lampiran.',
            'lampiran.*.mimes'  => 'Lampiran harus berformat PDF, JPG, PNG, Word, atau Excel.',
            'lampiran.*.max'    => 'Setiap lampiran maksimal 10 MB.',
        ]);

        $jumlah = 0;
        foreach ((array) $request->file('lampiran') as $f) {
            $batch->files()->create([
                'jenis'  => 'lampiran',
                'path'   => $f->store('penyesuaian-harga', 'local'),
                'nama'   => $f->getClientOriginalName(),
                'ukuran' => $f->getSize(),
            ]);
            $jumlah++;
        }

        return redirect()->route('penyesuaian-harga.show', $batch->id)->with('success', $jumlah . ' lampiran ditambahkan.');
    }

    /** Hapus satu lampiran (hanya jenis lampiran dan hanya selama menunggu ACC). */
    public function hapusLampiran(int $id, int $fileId)
    {
        $batch = PenyesuaianHargaBatch::findOrFail($id);
        $f     = PenyesuaianHargaFile::where('batch_id', $id)->where('jenis', 'lampiran')->findOrFail($fileId);

        if (! $batch->isBelumFinal()) {
            return back()->with('error', 'Lampiran tidak bisa dihapus karena import ini sudah diproses.');
        }

        Storage::disk('local')->delete($f->path);
        $f->delete();

        return back()->with('success', 'Lampiran dihapus.');
    }

    /** Lihat berkas asli (surat / PDF data), file privat. */
    public function berkas(int $id, int $fileId)
    {
        $f = PenyesuaianHargaFile::where('batch_id', $id)->findOrFail($fileId);
        abort_unless(Storage::disk('local')->exists($f->path), 404, 'File tidak ditemukan.');

        return Storage::disk('local')->response($f->path, $f->nama, [], 'inline');
    }

    // ------------------------------------------------------------------

    /** Tebak kelompok dari teks judul bagian / nama sheet; null bila tidak jelas. */
    private function grupDariTeks(string $teks): ?string
    {
        if (preg_match('/seragam|\bkpk\b|\btas\b/i', $teks)) {
            return PenyesuaianHargaData::GRUP_SERAGAM;
        }
        if (preg_match('/modul|raport/i', $teks)) {
            return PenyesuaianHargaData::GRUP_MODUL;
        }

        return null;
    }

    /** Batch yang ditampilkan: pilihan ?batch=, kalau tidak ada pakai yang terbaru, kalau belum ada pakai data bawaan. */
    private function pilihBatch(Request $request): ?PenyesuaianHargaBatch
    {
        if ($request->filled('batch')) {
            return PenyesuaianHargaBatch::with(['files', 'creator', 'reviewer'])->findOrFail((int) $request->get('batch'));
        }

        return PenyesuaianHargaBatch::with(['files', 'creator', 'reviewer'])->orderByDesc('id')->first();
    }

    /** Baris + produk + status + harga jual otomatis untuk satu batch (atau data bawaan bila belum ada import). */
    private function dataTampilan(Request $request, ?PenyesuaianHargaBatch $batch): array
    {
        $q = PenyesuaianHarga::with('product')->orderBy('grup')->orderBy('no');

        // Belum ada import = tabel kosong (tidak ada data bawaan)
        $batch ? $q->where('batch_id', $batch->id) : $q->whereRaw('1 = 0');

        if ($request->filled('grup')) {
            $q->where('grup', $request->get('grup'));
        }
        if ($request->filled('cari')) {
            $q->where('nama', 'like', '%' . $request->get('cari') . '%');
        }

        $rows = $q->get();

        $peta   = $this->produkPerBaris($rows);
        $produk = [];
        $otomatis = [];
        $ganda  = [];
        foreach ($peta as $id => $info) {
            if ($info['produk']) {
                $produk[$id] = $info['produk'];
            }
            if ($info['otomatis']) {
                $otomatis[$id] = true;
            }
            if ($info['ganda']) {
                $ganda[$id] = $info['ganda'];
            }
        }

        $status = [];
        $jual   = [];
        $sudah26 = [];
        foreach ($rows as $r) {
            $p = $produk[$r->id] ?? null;
            $status[$r->id] = $this->statusVs2024($r, $p);
            $jual[$r->id]   = $this->infoHargaJual($r, $p, $batch);
            $sudah26[$r->id] = $this->sudahMengikuti2026($r, $p);
        }

        // Filter kelompok (halaman Jual): "modul" = hanya produk Modul, "lainnya" = selain Modul (Raport, Seragam, dll)
        if (in_array($request->get('kelompok'), ['modul', 'lainnya'], true)) {
            $mau = $request->get('kelompok') === 'modul';
            $rows = $rows->filter(fn ($r) => (bool) ($jual[$r->id]['modul'] ?? false) === $mau)->values();
        }

        if ($request->filled('status')) {
            $s = $request->get('status');
            $rows = $rows->filter(fn ($r) => $status[$r->id] === $s)->values();
        }
        if ($request->filled('kesesuaian')) {
            $sesuai = $request->get('kesesuaian') === 'sesuai';
            $rows = $rows->filter(fn ($r) => in_array($status[$r->id], ['sama_beli', 'sama_jual'], true) === $sesuai)->values();
        }

        $ringkas = [
            'total'       => $rows->count(),
            'sama'        => $rows->filter(fn ($r) => in_array($status[$r->id], ['sama_beli', 'sama_jual'], true))->count(),
            'beda'        => $rows->filter(fn ($r) => $status[$r->id] === 'beda')->count(),
            'belum_cocok' => $rows->filter(fn ($r) => $status[$r->id] === 'belum_cocok')->count(),
        ];

        $grupList = [PenyesuaianHargaData::GRUP_MODUL, PenyesuaianHargaData::GRUP_SERAGAM];

        return compact('rows', 'produk', 'otomatis', 'ganda', 'status', 'jual', 'sudah26', 'ringkas', 'grupList');
    }

    /**
     * Harga jual otomatis untuk satu baris: sebelum dipublikasi dihitung dari harga 2026,
     * sesudah dipublikasi memakai angka yang tersimpan saat ACC.
     */
    private function infoHargaJual(PenyesuaianHarga $r, ?Product $p, ?PenyesuaianHargaBatch $batch = null): array
    {
        if (! $p) {
            return ['sekarang' => null, 'baru' => null, 'pengali' => null, 'isi' => 1, 'beli_lama' => null, 'lama_satuan' => null, 'baru_satuan' => null, 'asli_satuan' => null];
        }

        $isi = max(1, (int) $p->isi);
        $modul = $r->grup === PenyesuaianHargaData::GRUP_MODUL;
        $pm = $batch?->pengaliModul();
        $pl = $batch?->pengaliLain();

        // Tampilan halaman Jual: per SATUAN (tanpa dikali isi). Harga paket dihitung saat ACC dan tersimpan di Master Product.
        $jualLamaPaket = $r->diterapkan_pada ? $r->harga_jual_sebelum : $p->harga_jual;
        $beliLama      = $r->diterapkan_pada ? $r->harga_beli_sebelum : $p->harga_beli;

        return [
            'beli_lama'   => $beliLama !== null ? (float) $beliLama : null,
            'lama_satuan' => $jualLamaPaket !== null ? round((float) $jualLamaPaket / $isi) : null,
            'baru_satuan' => Product::hitungHargaJual((float) $r->harga_2026, $p->jenis, 1, $modul, $pm, $pl),
            'asli_satuan' => Product::hargaJualAsli((float) $r->harga_2026, $p->jenis, $modul, $pm, $pl),
            // harga jual lama: sebelum diubah oleh publikasi (kalau sudah dipublikasi) atau yang berlaku sekarang
            'sekarang' => ($r->diterapkan_pada ? $r->harga_jual_sebelum : $p->harga_jual) !== null
                ? (float) ($r->diterapkan_pada ? $r->harga_jual_sebelum : $p->harga_jual)
                : null,
            'baru'     => $r->diterapkan_pada && $r->harga_jual_baru !== null
                ? (float) $r->harga_jual_baru
                : Product::hitungHargaJual((float) $r->harga_2026, $p->jenis, $isi, $modul, $pm, $pl),
            'pengali'  => Product::pengaliJual($p->jenis, $modul, $pm, $pl),
            'modul'    => Product::adalahModul($p->jenis, $modul),
            'isi'      => $isi,
        ];
    }

    /**
     * Produk efektif per baris: pemetaan tersimpan, kalau belum ada pakai pencocokan nama otomatis.
     * @return array<int, array{produk: ?Product, otomatis: bool, ganda: int}>
     */
    private function produkPerBaris($rows): array
    {
        $kandidat = $this->kandidatProduk();
        $hasil = [];

        foreach ($rows as $r) {
            $info = ['produk' => null, 'otomatis' => false, 'ganda' => 0];

            if ($r->product_id && ($p = $r->product ?? Product::find($r->product_id))) {
                $info['produk'] = $p;
            } else {
                $hit = $this->cariKandidat($r->nama, $kandidat);
                if ($hit->count() === 1) {
                    $info['produk'] = $hit->first();
                    $info['otomatis'] = true;
                } elseif ($hit->count() > 1) {
                    // Nama kembar: utamakan Jenis "Reguler" jika hanya satu yang cocok
                    $reg = $hit->filter(fn ($p) => str_contains(strtolower((string) $p->jenis), 'reguler'));
                    if ($reg->count() === 1) {
                        $info['produk'] = $reg->first();
                        $info['otomatis'] = true;
                    } else {
                        $info['ganda'] = $hit->count();
                    }
                }
            }

            $hasil[$r->id] = $info;
        }

        return $hasil;
    }

    /** Harga Beli produk di Master sudah sama dengan harga 2026 (belum diubah oleh ACC ini): tidak perlu masuk bulk. */
    private function sudahMengikuti2026(PenyesuaianHarga $r, ?Product $p): bool
    {
        return $p
            && ! $r->diterapkan_pada
            && $p->harga_beli !== null
            && round((float) $p->harga_beli) === round((float) $r->harga_2026);
    }

    /** Bandingkan harga aplikasi dengan harga PDF 2024 (harga beli dulu, lalu harga jual). */
    private function statusVs2024(PenyesuaianHarga $r, ?Product $p): string
    {
        if (! $p) {
            return 'belum_cocok';
        }
        $h24 = round((float) $r->harga_2024);

        // Setelah dipublikasi, bandingkan dengan harga aplikasi SEBELUM diubah (supaya hasil perbandingan tetap terbaca)
        $beli = $r->diterapkan_pada ? $r->harga_beli_sebelum : $p->harga_beli;
        $jual = $r->diterapkan_pada ? $r->harga_jual_sebelum : $p->harga_jual;

        if ($beli !== null && round((float) $beli) === $h24) {
            return 'sama_beli';
        }
        if ($jual !== null && round((float) $jual) === $h24) {
            return 'sama_jual';
        }

        return 'beda';
    }

    private function kandidatProduk()
    {
        return Product::all()->map(function ($p) {
            $p->norm = $this->normal($p->name);
            // tanpa kata "modul"
            $p->norm_tm = $this->normal(preg_replace('/\bmodul\b/i', ' ', (string) $p->name));
            // tanpa kata "modul" dan tanpa isi kurung: "Mewarnai 4 (Buah)" -> "mewarnai4"
            $p->norm_tk = $this->normal(preg_replace('/\bmodul\b/i', ' ', preg_replace('/\(.*?\)/', ' ', (string) $p->name)));
            return $p;
        });
    }

    /**
     * Cari produk yang namanya sama persis setelah dinormalkan (kata biMBA/AIUEO/Seragam diabaikan,
     * jadi "Kaos Seragam Anak S" = "Kaos Anak S"). Nama PDF dengan kurung, mis.
     * "KPK (Kotak Permainan Kata)", dicoba utuh, tanpa kurung, dan isi kurung.
     * Mengembalikan semua produk yang cocok pada varian pertama yang ada hasilnya.
     */
    private function cariKandidat(string $namaPdf, $kandidat)
    {
        // Alias lewat Label (SKU) untuk nama yang beda jauh antara PDF dan aplikasi
        $label = PenyesuaianHargaData::ALIAS_LABEL[$namaPdf] ?? null;
        if ($label) {
            $hit = $kandidat->filter(fn ($p) => strcasecmp((string) $p->label, $label) === 0)->values();
            if ($hit->isNotEmpty()) {
                return $hit;
            }
        }

        $varian = [$namaPdf, trim(preg_replace('/\(.*?\)/', '', $namaPdf))];
        if (preg_match('/\((.*?)\)/', $namaPdf, $m)) {
            $varian[] = $m[1];
        }

        foreach (array_unique($varian) as $v) {
            $norm = $this->normal($v);
            if ($norm === '') {
                continue;
            }
            $hit = $kandidat->where('norm', $norm)->values();
            if ($hit->isNotEmpty()) {
                return $hit;
            }
        }

        // Cadangan 1: abaikan kata "modul" ("Modul Lembar Mengarang" = "Lembar mengarang")
        // Cadangan 2: abaikan juga isi kurung di nama aplikasi ("Modul Mewarnai 4" = "Mewarnai 4 (Buah)")
        foreach (['norm_tm', 'norm_tk'] as $kolom) {
            foreach (array_unique($varian) as $v) {
                $norm = $this->normal(preg_replace('/\bmodul\b/i', ' ', $v));
                if ($norm === '') {
                    continue;
                }
                $hit = $kandidat->where($kolom, $norm)->values();
                if ($hit->isNotEmpty()) {
                    return $hit;
                }
            }
        }

        return collect();
    }

    private function normal(?string $s): string
    {
        $s = strtolower((string) $s);
        $s = preg_replace('/\b(bimba|aiueo|seragam)\b/', ' ', $s);

        return preg_replace('/[^a-z0-9]+/', '', $s);
    }
}
