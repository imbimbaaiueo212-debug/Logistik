<?php

namespace App\Modules\Kalender\Support;

use App\Modules\Master\Models\StokisMitra;
use App\Modules\Master\Models\UnitKemitraan;
use App\Modules\Master\Models\UserExportBimbaShop;
use Illuminate\Support\Facades\Log;

/**
 * Daftar pilihan "Nama Unit" untuk Create/Edit Manual Kalender Pasif.
 * Sumber: Unit + Mitra (unit_kemitraan), Stokis (stokis_mitra), dan akun biMBA Shop lainnya.
 * Nama yang sama hanya muncul sekali (urutan prioritas: Unit > Stokis > Lainnya).
 */
class UnitManualOptions
{
    public const EKSPEDISI = ['Diambil Sendiri', 'Lion Parcel', 'JNE'];

    public const SERVICE = [
        'Lion Parcel' => ['REGPACK', 'BOSPACK', 'JAGOPACK', 'BIGPACK'],
        'JNE'         => ['REG'],
    ];

    /** @return array<string, array<int, array{nama:string,label:string,status:string,no_cab:string,alamat:string,telepon:string}>> */
    public static function daftar(): array
    {
        $dipakai = [];
        $out = ['Unit / Mitra' => [], 'Stokis' => [], 'Lainnya (akun biMBA Shop)' => []];
        $kunci = fn ($v) => mb_strtoupper(trim((string) $v));

        try {
            foreach (UnitKemitraan::query()->orderBy('bimba_aiueo_unit')->get() as $u) {
                $nama = trim((string) $u->bimba_aiueo_unit);
                if ($nama === '' || isset($dipakai[$kunci($nama)])) {
                    continue;
                }
                $dipakai[$kunci($nama)] = true;
                $out['Unit / Mitra'][] = [
                    'nama'    => $nama,
                    'label'   => trim(($u->no_cab ? $u->no_cab . ' - ' : '') . $nama . ($u->nama_mitra ? ' (' . $u->nama_mitra . ')' : '')),
                    'status'  => mb_strtolower((string) $u->status_pengelolaan) === 'unit pasif' ? 'pasif' : 'aktif',
                    'no_cab'  => (string) $u->no_cab,
                    'alamat'  => (string) $u->alamat_unit,
                    'telepon' => (string) $u->no_telp_unit,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('UnitManualOptions unit: ' . $e->getMessage());
        }

        try {
            foreach (StokisMitra::query()->get() as $s) {
                $nama = trim((string) ($s->nama_stokis_db_kemitraan ?: $s->nama_stokis_db_bimbashop));
                if ($nama === '' || isset($dipakai[$kunci($nama)])) {
                    continue;
                }
                $dipakai[$kunci($nama)] = true;
                $out['Stokis'][] = [
                    'nama'    => $nama,
                    'label'   => trim(($s->no_cab ? $s->no_cab . ' - ' : '') . $nama . ($s->nama_mitra ? ' (' . $s->nama_mitra . ')' : '')),
                    'status'  => mb_strtolower((string) $s->status) === 'pasif' ? 'pasif' : 'aktif',
                    'no_cab'  => (string) $s->no_cab,
                    'alamat'  => '',
                    'telepon' => (string) $s->no_hp,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('UnitManualOptions stokis: ' . $e->getMessage());
        }

        try {
            foreach (UserExportBimbaShop::query()->orderBy('display_name')->get(['display_name', 'billing_company', 'billing_phone', 'billing_address_1']) as $c) {
                $nama = trim((string) ($c->billing_company ?: $c->display_name));
                if ($nama === '' || isset($dipakai[$kunci($nama)])) {
                    continue;
                }
                $dipakai[$kunci($nama)] = true;
                $out['Lainnya (akun biMBA Shop)'][] = [
                    'nama'    => $nama,
                    'label'   => $nama,
                    'status'  => '',          // tidak diketahui: dipilih manual
                    'no_cab'  => '',
                    'alamat'  => (string) $c->billing_address_1,
                    'telepon' => (string) $c->billing_phone,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('UnitManualOptions lainnya: ' . $e->getMessage());
        }

        return array_filter($out);
    }

    /** Status kirim mengikuti ekspedisi: Diambil Sendiri = Diambil, selain itu = Dikirim. */
    public static function statusKirim(?string $ekspedisi): ?string
    {
        if (blank($ekspedisi)) {
            return null;
        }

        return $ekspedisi === 'Diambil Sendiri' ? 'Diambil' : 'Dikirim';
    }
}
