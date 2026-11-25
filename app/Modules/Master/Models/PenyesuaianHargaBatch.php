<?php

namespace App\Modules\Master\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Satu kali import: surat permohonan + data perubahan harga dari percetakan, menunggu ACC. */
class PenyesuaianHargaBatch extends Model
{
    public const MENUNGGU     = 'menunggu';
    public const MENUNGGU_JUAL = 'menunggu_jual';
    public const DIPUBLIKASI  = 'dipublikasi';
    public const DITOLAK      = 'ditolak';

    protected $table = 'penyesuaian_harga_batches';

    protected $fillable = [
        'no_ra', 'supplier_id', 'judul', 'nomor_surat', 'tanggal_surat', 'berlaku_mulai', 'catatan', 'status', 'pengali_modul', 'pengali_lain',
        'created_by', 'acc_beli_by', 'acc_beli_at', 'catatan_beli', 'reviewed_by', 'reviewed_at', 'catatan_review', 'published_at',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'berlaku_mulai' => 'date',
        'acc_beli_at'   => 'datetime',
        'reviewed_at'   => 'datetime',
        'published_at'  => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /** Nomor RA otomatis: RA<yymmdd>-<urut harian>, mis. RA261125-01. Panggil di dalam transaksi. */
    public static function buatNoRa(): string
    {
        $awalan = 'RA' . now()->format('ymd') . '-';
        $terakhir = static::where('no_ra', 'like', $awalan . '%')->lockForUpdate()->orderByDesc('no_ra')->value('no_ra');
        $urut = $terakhir ? ((int) substr($terakhir, strlen($awalan))) + 1 : 1;

        return $awalan . str_pad((string) $urut, 2, '0', STR_PAD_LEFT);
    }

    /** Pengali harga jual untuk Modul (diisi manual di halaman Jual; kosong = bawaan). */
    public function pengaliModul(): float
    {
        return $this->pengali_modul !== null ? (float) $this->pengali_modul : Product::PENGALI_MODUL;
    }

    /** Pengali harga jual untuk selain Modul. */
    public function pengaliLain(): float
    {
        return $this->pengali_lain !== null ? (float) $this->pengali_lain : Product::PENGALI_LAINNYA;
    }

    public function rows()
    {
        return $this->hasMany(PenyesuaianHarga::class, 'batch_id');
    }

    public function files()
    {
        return $this->hasMany(PenyesuaianHargaFile::class, 'batch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Judul yang tampil: isian judul, kalau kosong otomatis dari tanggal berlaku. */
    public function namaTampil(): string
    {
        if (filled($this->judul)) {
            return $this->judul;
        }

        return 'Perubahan Harga ' . ($this->berlaku_mulai ?? $this->created_at)->translatedFormat('F Y');
    }

    public function isMenunggu(): bool
    {
        return $this->status === self::MENUNGGU;
    }

    /** Beli sudah di-ACC, menunggu ACC Jual sebelum harga pindah ke Master Product. */
    public function isMenungguJual(): bool
    {
        return $this->status === self::MENUNGGU_JUAL;
    }

    /** Belum final: masih boleh diubah pengalinya, ditolak, dll. */
    public function isBelumFinal(): bool
    {
        return in_array($this->status, [self::MENUNGGU, self::MENUNGGU_JUAL], true);
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            self::MENUNGGU_JUAL => 'Menunggu ACC Jual',
            self::DIPUBLIKASI => 'Disetujui & dipublikasikan',
            self::DITOLAK     => 'Ditolak (tidak dipublikasikan)',
            default           => 'Menunggu ACC Beli',
        };
    }
}
