<?php

namespace App\Modules\Order\Http\Controllers\Concerns;

use App\Modules\Master\Models\StokisMitra;
use App\Modules\Master\Models\UnitKemitraan;

/**
 * Aturan memisahkan order Logistik (SKU LG) milik PASIF dari AKTIF.
 *
 * Order dianggap PASIF jika salah satu terpenuhi:
 *  1. Payment Method = bacs (pasti pasif)
 *  2. Billing Name / Company memuat "Jakarta Pasif" atau "Inventaris"
 *     (Stokis Jakarta Pasif, JAKARTA PASIF, Stokis Inventaris biMBA AIUEO)
 *  3. Billing Company berisi angka murni (no cab) dan di Database Unit Kemitraan unitnya PASIF
 *
 * Khusus untuk order pasif, angka di Billing Company dicek ke Unit Kemitraan:
 *  - unit AKTIF  -> ditolak, diganti ke Stokis Pasif Inventaris
 *  - unit PASIF  -> pakai nama & no cab unit tersebut
 *  - tidak ketemu -> pakai hasil pencocokan Stokis Mitra seperti biasa
 *
 * Perlu trait OrderHelpers (unitKemitraanByNoCab) pada class pemakai.
 */
trait LogistikPasifRules
{
    /**
     * @return array{pasif: bool, override: ?array{no_cab: string, nama_unit: string}, alasan: string}
     */
    private function cekLogistikPasif($src): array
    {
        $teks = strtolower(trim(implode(' ', [
            (string) $src->billing_first_name,
            (string) $src->billing_last_name,
            (string) $src->billing_company,
        ])));

        $bacs      = strtolower(trim((string) $src->payment_method)) === 'bacs';
        $namaPasif = str_contains($teks, 'jakarta pasif') || str_contains($teks, 'inventaris');

        // Billing Company berisi angka MURNI (mis. "372", "0065") -> cek Unit Kemitraan.
        // Kode berawalan huruf seperti "M0065" / "W0023" adalah kode mitra, BUKAN no cab,
        // jadi tidak dicocokkan ke Unit Kemitraan (kalau dipaksa, "M0065" salah cocok ke no cab 65).
        $unit = null;
        $company = trim((string) $src->billing_company);

        if ($company !== '' && preg_match('/^\d+$/', $company) && preg_match_all('/\d+/', $company, $m)) {
            foreach ($m[0] as $angka) {
                foreach (array_unique([$angka, ltrim($angka, '0') ?: '0']) as $cand) {
                    $unit = $this->unitKemitraanByNoCab($cand);
                    if ($unit) {
                        break 2;
                    }
                }
            }
        }

        $unitAktif = $unit && $this->unitKemitraanAktif($unit);
        $unitPasif = $unit && ! $unitAktif;

        if (! ($bacs || $namaPasif || $unitPasif)) {
            return ['pasif' => false, 'override' => null, 'alasan' => ''];
        }

        if ($unitAktif) {
            return [
                'pasif'    => true,
                'override' => $this->stokisInventaris(),
                'alasan'   => 'unit aktif ditolak, diganti Stokis Inventaris',
            ];
        }

        if ($unitPasif) {
            return [
                'pasif'    => true,
                'override' => [
                    'no_cab'    => trim((string) $unit->no_cab),
                    'nama_unit' => trim((string) $unit->bimba_aiueo_unit),
                ],
                'alasan'   => 'unit pasif (Unit Kemitraan)',
            ];
        }

        return ['pasif' => true, 'override' => null, 'alasan' => $bacs ? 'bacs' : 'nama pasif'];
    }

    private function unitKemitraanAktif(UnitKemitraan $unit): bool
    {
        $pengelolaan = strtolower(trim((string) $unit->status_pengelolaan));

        if ($pengelolaan !== '') {
            return str_contains($pengelolaan, 'aktif') && ! str_contains($pengelolaan, 'pasif');
        }

        $status = strtoupper(trim((string) $unit->status));

        return in_array($status, [
            'MM', 'MM 1', 'AKTIF 1', 'MK', 'MK 1', 'MK RINDA', 'MKU', 'MKU 1', 'UNIT AKTIF', 'E-BIMBA AKTIF',
        ], true);
    }

    /** Stokis Pasif "Inventaris" (Stokis Mitra status PASIF). */
    private function stokisInventaris(): array
    {
        $inv = StokisMitra::where('status', 'PASIF')
            ->where(function ($q) {
                $q->where('nama_stokis_db_bimbashop', 'like', '%inventaris%')
                  ->orWhere('nama_stokis_db_kemitraan', 'like', '%inventaris%');
            })
            ->first();

        $nama = $inv
            ? trim((string) ($inv->nama_stokis_db_bimbashop ?: $inv->nama_stokis_db_kemitraan))
            : '';

        return [
            'no_cab'    => $inv ? (trim((string) $inv->no_cab) ?: '-') : '-',
            'nama_unit' => $nama !== '' ? $nama : 'Stokis Inventaris biMBA AIUEO',
        ];
    }
}
