<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JakartaAktif extends Model
{
    protected $table = 'jakarta_aktif';

    protected $fillable = [
        'tgl_input', 
        'tgl_pesan', 
        'kirim', 
        'no_telpon', 
        'alamat_kirim',
        'kab_kota_provinsi', 
        'ekspedisi', 
        'ongkir', 
        
        // === SERVICE KURIR ===
        'service_pengiriman',      // Contoh: J&T REG, J&T YES, JNE REG, SICEPAT, dll
        'tracking_number',
        'status_kirim',
        'estimasi_print_pl',
        'estimasi_persiapan',
                 // No Resi
        'billing_last_name',
        // === MANUAL DISTRIBUSI ===
        'distribusi_manual',       // Yes / No
        'nama_distributor',        // Nama orang / vendor yang mendistribusikan manual
        'tgl_distribusi',          // Tanggal distribusi manual
        'status_distribusi',       // Sudah Didistribusikan / Belum / Gagal
        
        'validasi', 
        'jenis_bank', 
        'status_pembayaran',
        'id_pesan', 
        'cabang', 
        'nama_unit', 
        'vendor', 
        'pesanan',
        'status_pesan', 
        'berat', 
        'harga', 
        'diskon', 
        'fee_payment',
        'total', 
        'status', 
        'sales', 
        'catatan'
    ];

    protected $casts = [
        'tgl_input'      => 'date',
        'tgl_pesan'      => 'datetime',
        'tgl_distribusi' => 'date',        // Tambahan cast
        'berat'          => 'decimal:2',
        'harga'          => 'decimal:2',
        'diskon'         => 'decimal:2',
        'fee_payment'    => 'decimal:2',
        'total'          => 'decimal:2',
        'ongkir'         => 'decimal:2',
    ];

    public function casdana()
{
    return $this->hasOne(CasdanaTransaction::class, 'invoice_number', 'id_pesan');
}

public function getPaymentDateAttribute()
{
    // Coba ambil dari relasi dulu
    if ($this->relationLoaded('casdana') && $this->casdana?->payment_date) {
        return $this->casdana->payment_date;
    }

    // Fallback query langsung (paling reliable)
    $cas = CasdanaTransaction::where('invoice_number', $this->id_pesan)
            ->orWhere('invoice_number', 'IDB' . $this->id_pesan)
            ->orWhere('invoice_number', 'like', "%{$this->id_pesan}%")
            ->first();

    return $cas?->payment_date;
}


// In JakartaAktif model
public static function appendBulkNote($ids, $note)
{
    $newNote = "\n\nDi proses bulk pada " . now()->format('d/m/Y H:i') . ": " . $note;

    return static::whereIn('id', $ids)
        ->update([
            'catatan' => DB::raw("CONCAT(COALESCE(`catatan`, ''), ?)")
        ], [$newNote]);
}

/**
 * Bulk Action untuk Jakarta Aktif
 */
public function bulkActionJakartaAktif(Request $request)
{
    $selectedIds = $request->input('selected', []);
    $action      = $request->input('action');
    $statusKirim = $request->input('status_kirim');
    $catatan     = $request->input('catatan');

    if (empty($selectedIds)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    if ($action === 'processed') {
        // Paksa timezone WIB dengan Carbon
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        if ($catatan) {
            $newNote = "\n\nDi proses bulk pada " . $now->format('d/m/Y H:i') . ": " . trim($catatan);

            $updated = DB::update("
                UPDATE jakarta_aktif 
                SET is_processed = 1,
                    processed_at = ?,
                    updated_at = ?,
                    status_kirim = ?,
                    catatan = CONCAT(COALESCE(catatan, ''), ?)
                WHERE id IN (" . str_repeat('?,', count($selectedIds) - 1) . "?)
            ", array_merge(
                [$now, $now, $statusKirim, $newNote],
                $selectedIds
            ));
        } else {
            $updated = DB::update("
                UPDATE jakarta_aktif 
                SET is_processed = 1,
                    processed_at = ?,
                    updated_at = ?,
                    status_kirim = ?
                WHERE id IN (" . str_repeat('?,', count($selectedIds) - 1) . "?)
            ", array_merge(
                [$now, $now, $statusKirim],
                $selectedIds
            ));
        }

        return redirect()->route('order.jakarta-aktif')
                         ->with('success', "$updated data berhasil diproses dan dikunci.");
    }

    return redirect()->back()->with('error', 'Aksi tidak dikenali.');
}

public function realisasi()
{
    return $this->hasOne(RealisasiAktif::class, 'jakarta_aktif_id');
}

}