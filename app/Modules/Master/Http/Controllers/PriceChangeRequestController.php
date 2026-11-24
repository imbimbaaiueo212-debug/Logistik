<?php

namespace App\Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Master\Models\PriceChangeFile;
use App\Modules\Master\Models\PriceChangeLog;
use App\Modules\Master\Models\PriceChangeRequest;
use App\Modules\Master\Models\PriceChangeRequestItem;
use App\Modules\Master\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Alur pengajuan perubahan harga Master Product:
 *   Diajukan (+ surat permohonan) -> Admin menyetujui / menolak -> bila disetujui harga produk otomatis berubah.
 * Setiap langkah dicatat di price_change_logs sebagai kronologi.
 */
class PriceChangeRequestController extends Controller
{
    /** true = pembuat pengajuan boleh menyetujui pengajuannya sendiri (matikan agar ada pemisahan tugas). */
    private const BOLEH_SETUJUI_SENDIRI = false;

    public function index(Request $request)
    {
        $q = PriceChangeRequest::with('creator')->withCount('items')->orderByDesc('id');

        if ($request->filled('status')) {
            $q->where('status', $request->get('status'));
        }
        if ($request->filled('cari')) {
            $cari = $request->get('cari');
            $q->where(function ($w) use ($cari) {
                $w->where('nomor', 'like', "%{$cari}%")->orWhere('nomor_surat', 'like', "%{$cari}%");
            });
        }

        $list = $q->paginate(20)->withQueryString();

        return view('master::pengajuan_harga.index', compact('list'));
    }

    public function create()
    {
        return view('master::pengajuan_harga.create');
    }

    /** Pencarian produk (ajax) untuk form pengajuan. */
    public function cariProduk(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $q = Product::query()->orderBy('name')->limit(20);
        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")->orWhere('label', 'like', "%{$term}%");
            });
        }

        $pending = PriceChangeRequestItem::whereHas('request', fn ($r) => $r->where('status', 'menunggu'))
            ->pluck('product_id')->all();

        return response()->json($q->get()->map(fn ($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'label'      => $p->label,
            'jenis'      => $p->jenis,
            'isi'        => (int) ($p->isi ?: 1),
            'harga_beli' => $p->harga_beli,
            'harga_jual' => $p->harga_jual,
            'pending'    => in_array($p->id, $pending, true),
        ])->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'alasan'                    => ['required', 'string', 'max:1000'],
            'nomor_surat'               => ['nullable', 'string', 'max:100'],
            'tanggal_surat'             => ['required', 'date'],
            'surat'                     => ['required', 'array', 'min:1', 'max:10'],
            'surat.*'                   => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'pendukung'                 => ['nullable', 'array', 'max:10'],
            'pendukung.*'               => ['file', 'mimes:pdf,jpg,jpeg,png,xls,xlsx,csv', 'max:5120'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_id'        => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.harga_beli_baru'   => ['nullable', 'numeric', 'min:0'],
            'items.*.harga_jual_baru'   => ['nullable', 'numeric', 'min:0'],
        ], [
            'surat.required'     => 'Surat permohonan penyesuaian harga wajib diupload.',
            'surat.min'          => 'Upload minimal satu surat permohonan.',
            'surat.*.mimes'      => 'Surat harus berformat PDF, JPG, atau PNG.',
            'surat.*.max'        => 'Ukuran tiap surat maksimal 5 MB.',
            'pendukung.*.mimes'  => 'Berkas pendukung harus PDF, JPG, PNG, XLS, XLSX, atau CSV.',
            'pendukung.*.max'    => 'Ukuran tiap berkas pendukung maksimal 5 MB.',
            'items.required'     => 'Tambahkan minimal satu produk.',
            'items.*.product_id.distinct' => 'Ada produk yang dipilih dua kali.',
        ]);

        // Susun rincian & pastikan memang ada yang berubah
        $rincian = [];
        $pending = PriceChangeRequestItem::whereHas('request', fn ($r) => $r->where('status', 'menunggu'))
            ->pluck('product_id')->all();

        foreach ($request->input('items') as $row) {
            $p = Product::find($row['product_id']);
            $beliBaru = isset($row['harga_beli_baru']) && $row['harga_beli_baru'] !== '' ? (float) $row['harga_beli_baru'] : null;
            $jualBaru = isset($row['harga_jual_baru']) && $row['harga_jual_baru'] !== '' ? (float) $row['harga_jual_baru'] : null;

            // Isian yang sama dengan harga sekarang dianggap tidak berubah
            if ($beliBaru !== null && round($beliBaru) === round((float) $p->harga_beli)) {
                $beliBaru = null;
            }
            if ($jualBaru !== null && round($jualBaru) === round((float) $p->harga_jual)) {
                $jualBaru = null;
            }

            if ($beliBaru === null && $jualBaru === null) {
                return back()->withInput()->withErrors(['items' => "Produk \"{$p->name}\" tidak ada perubahan harga. Ubah harganya atau hapus dari daftar."]);
            }
            if (in_array($p->id, $pending, true)) {
                return back()->withInput()->withErrors(['items' => "Produk \"{$p->name}\" masih punya pengajuan yang menunggu persetujuan."]);
            }

            $rincian[] = [
                'product_id'      => $p->id,
                'harga_beli_lama' => $p->harga_beli,
                'harga_beli_baru' => $beliBaru,
                'harga_jual_lama' => $p->harga_jual,
                'harga_jual_baru' => $jualBaru,
            ];
        }

        // Simpan semua berkas (surat + pendukung) dulu
        $berkas = [];
        foreach ((array) $request->file('surat') as $f) {
            $berkas[] = ['jenis' => 'surat', 'path' => $f->store('price-change', 'local'), 'nama' => $f->getClientOriginalName(), 'ukuran' => $f->getSize()];
        }
        foreach ((array) $request->file('pendukung') as $f) {
            $berkas[] = ['jenis' => 'pendukung', 'path' => $f->store('price-change', 'local'), 'nama' => $f->getClientOriginalName(), 'ukuran' => $f->getSize()];
        }
        $utama = $berkas[0]; // surat pertama tetap diisi ke kolom lama (kompatibel)

        $req = DB::transaction(function () use ($request, $rincian, $berkas, $utama) {
            $urut  = PriceChangeRequest::whereYear('created_at', now()->year)->lockForUpdate()->count() + 1;
            $nomor = 'PH-' . now()->format('Y') . '-' . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);

            $req = PriceChangeRequest::create([
                'nomor'         => $nomor,
                'alasan'        => $request->input('alasan'),
                'nomor_surat'   => $request->input('nomor_surat'),
                'tanggal_surat' => $request->input('tanggal_surat'),
                'surat_path'    => $utama['path'],
                'surat_nama'    => $utama['nama'],
                'status'        => 'menunggu',
                'created_by'    => Auth::id(),
            ]);

            foreach ($rincian as $r) {
                $req->items()->create($r);
            }

            foreach ($berkas as $b) {
                $req->files()->create($b);
            }

            $this->catat($req, 'diajukan', 'Mengajukan perubahan harga untuk ' . count($rincian) . ' produk dengan ' . count($berkas) . ' berkas. Alasan: ' . $request->input('alasan'));

            return $req;
        });

        return redirect()->route('pengajuan-harga.show', $req->id)
            ->with('success', "Pengajuan <b>{$req->nomor}</b> terkirim dan menunggu persetujuan admin.");
    }

    public function show(int $id)
    {
        $req = PriceChangeRequest::with(['items.product', 'files', 'logs.user', 'creator', 'reviewer'])->findOrFail($id);

        $bolehReview = $req->isMenunggu() && Auth::user()->isAdmin()
            && (self::BOLEH_SETUJUI_SENDIRI || $req->created_by !== Auth::id());
        $bolehBatal  = $req->isMenunggu() && ($req->created_by === Auth::id() || Auth::user()->isAdmin());
        $sendiri     = $req->isMenunggu() && Auth::user()->isAdmin() && ! $bolehReview;

        return view('master::pengajuan_harga.show', compact('req', 'bolehReview', 'bolehBatal', 'sendiri'));
    }

    /** Lihat / unduh satu berkas (file privat, hanya user login). */
    public function berkas(int $id, int $fileId)
    {
        $f = PriceChangeFile::where('request_id', $id)->findOrFail($fileId);
        abort_unless(Storage::disk('local')->exists($f->path), 404, 'File tidak ditemukan.');

        return Storage::disk('local')->response($f->path, $f->nama, [], 'inline');
    }

    /** PDF ringkasan pengajuan (daftar perubahan harga, berkas, status, kronologi). */
    public function pdf(int $id)
    {
        $req = PriceChangeRequest::with(['items.product', 'files', 'logs.user', 'creator', 'reviewer'])->findOrFail($id);

        return Pdf::loadView('master::pengajuan_harga.pdf', compact('req'))
            ->setPaper('a4', 'portrait')
            ->stream('Pengajuan-Harga-' . $req->nomor . '.pdf');
    }

    public function approve(Request $request, int $id)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $request->validate(['catatan' => ['nullable', 'string', 'max:500']]);

        try {
            DB::transaction(function () use ($request, $id) {
                $req = PriceChangeRequest::with('items')->lockForUpdate()->findOrFail($id);

                if (! $req->isMenunggu()) {
                    throw new \RuntimeException('Pengajuan ini sudah diproses.');
                }
                if (! self::BOLEH_SETUJUI_SENDIRI && $req->created_by === Auth::id()) {
                    throw new \RuntimeException('Pengajuan tidak boleh disetujui oleh pembuatnya sendiri.');
                }

                // 1) Pastikan harga di master belum berubah sejak diajukan
                $produk = [];
                foreach ($req->items as $it) {
                    $p = Product::lockForUpdate()->find($it->product_id);
                    if (! $p) {
                        throw new \RuntimeException('Ada produk yang sudah dihapus dari master.');
                    }
                    if (round((float) $p->harga_beli) !== round((float) $it->harga_beli_lama)
                        || round((float) $p->harga_jual) !== round((float) $it->harga_jual_lama)) {
                        throw new \RuntimeException("Harga \"{$p->name}\" di master sudah berubah sejak pengajuan dibuat. Tolak pengajuan ini lalu buat pengajuan baru.");
                    }
                    $produk[$it->id] = $p;
                }

                // 2) Terapkan harga baru
                foreach ($req->items as $it) {
                    $p = $produk[$it->id];
                    if ($it->harga_beli_baru !== null) {
                        $p->harga_beli = $it->harga_beli_baru;
                    }
                    if ($it->harga_jual_baru !== null) {
                        $p->harga_jual = $it->harga_jual_baru;
                    }
                    $p->forceFill(['harga_diubah_pada' => now(), 'harga_request_id' => $req->id])->save();
                }

                $req->update([
                    'status'         => 'diterapkan',
                    'reviewed_by'    => Auth::id(),
                    'reviewed_at'    => now(),
                    'catatan_review' => $request->input('catatan'),
                    'applied_at'     => now(),
                ]);

                $this->catat($req, 'disetujui', $request->input('catatan') ?: 'Pengajuan disetujui.');
                $this->catat($req, 'diterapkan', 'Harga ' . $req->items->count() . ' produk di Master Product sudah diperbarui.');
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan disetujui dan harga di Master Product sudah diperbarui.');
    }

    public function reject(Request $request, int $id)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        $request->validate(['catatan' => ['required', 'string', 'min:5', 'max:500']], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
            'catatan.min'      => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $req = PriceChangeRequest::findOrFail($id);
        if (! $req->isMenunggu()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        DB::transaction(function () use ($req, $request) {
            $req->update([
                'status'         => 'ditolak',
                'reviewed_by'    => Auth::id(),
                'reviewed_at'    => now(),
                'catatan_review' => $request->input('catatan'),
            ]);
            $this->catat($req, 'ditolak', $request->input('catatan'));
        });

        return back()->with('success', 'Pengajuan ditolak. Harga di Master Product tidak berubah.');
    }

    public function cancel(int $id)
    {
        $req = PriceChangeRequest::findOrFail($id);

        abort_unless($req->created_by === Auth::id() || Auth::user()->isAdmin(), 403);
        if (! $req->isMenunggu()) {
            return back()->with('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
        }

        DB::transaction(function () use ($req) {
            $req->update(['status' => 'dibatalkan']);
            $this->catat($req, 'dibatalkan', 'Pengajuan dibatalkan.');
        });

        return back()->with('success', 'Pengajuan dibatalkan.');
    }

    private function catat(PriceChangeRequest $req, string $aksi, ?string $catatan = null): void
    {
        PriceChangeLog::create([
            'request_id' => $req->id,
            'user_id'    => Auth::id(),
            'aksi'       => $aksi,
            'catatan'    => $catatan,
        ]);
    }
}
