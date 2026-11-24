<?php

namespace App\Modules\Master\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PriceChangeRequest extends Model
{
    protected $table = 'price_change_requests';

    protected $fillable = [
        'nomor', 'alasan', 'nomor_surat', 'tanggal_surat', 'surat_path', 'surat_nama',
        'status', 'created_by', 'reviewed_by', 'reviewed_at', 'catatan_review', 'applied_at',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'reviewed_at'   => 'datetime',
        'applied_at'    => 'datetime',
    ];

    public const STATUS = [
        'menunggu'    => ['Menunggu persetujuan', 'bg-amber-100 text-amber-800'],
        'diterapkan'  => ['Disetujui & diterapkan', 'bg-green-100 text-green-800'],
        'ditolak'     => ['Ditolak', 'bg-red-100 text-red-800'],
        'dibatalkan'  => ['Dibatalkan', 'bg-gray-100 text-gray-600'],
    ];

    public function items()    { return $this->hasMany(PriceChangeRequestItem::class, 'request_id'); }
    public function files()   { return $this->hasMany(PriceChangeFile::class, 'request_id')->orderBy('id'); }
    public function logs()     { return $this->hasMany(PriceChangeLog::class, 'request_id')->orderBy('id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isMenunggu(): bool { return $this->status === 'menunggu'; }

    public function suratIsImage(): bool
    {
        return in_array(strtolower(pathinfo($this->surat_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }
}
