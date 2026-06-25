<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Picking extends Model
{
    use HasFactory;

    protected $table = 'pickings';

    protected $fillable = [
        'no_pl',
        'tgl_order',
        'tgl_picking',
        'jam_picking',
        'nama_unit',
        'billing_last_name',
        'billing_company',
        'status_kirim',
        'total_item',
        'total_qty',
        'dipicking_oleh',
        'status',
        'printed_at',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tgl_order'     => 'date',
        'tgl_picking'   => 'date',
        'printed_at'    => 'datetime',
        'jam_picking'   => 'string',
        'total_item'    => 'integer',
        'total_qty'     => 'integer',
    ];

    // Relasi
    public function items()
    {
        return $this->hasMany(PickingItem::class);
    }

    // Relasi ke Jakarta Aktif (melalui picking_id)
    public function jakartaAktif()
    {
        return $this->hasOne(JakartaAktif::class, 'picking_id', 'id');
    }

    // Event otomatis saat dihapus
    protected static function booted()
    {
        static::deleting(function ($picking) {
            // Reset flag di Jakarta Aktif
            JakartaAktif::where('picking_id', $picking->id)
                        ->update([
                            'picking_generated' => false,
                            'picking_id'        => null,
                        ]);
        });
    }

    // Accessor
    public function getNoPlAttribute($value)
    {
        return $value ?? 'PL-' . date('Ymd') . '-' . str_pad($this->id ?? rand(100,999), 4, '0', STR_PAD_LEFT);
    }

    // Scope
    public function scopeBelumDipicking($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSudahDipicking($query)
    {
        return $query->where('status', 'completed');
    }
}