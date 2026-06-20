<?php

namespace App\Imports;

use App\Models\UnitKemitraan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnitKemitraanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new UnitKemitraan([
            'id_record'                     => $row['id record'] ?? null,
            'no_cab'                        => $row['no cab'] ?? null,
            'bimba_aiueo_unit'              => $row['bimba aiueo unit'] ?? null,
            'status'                        => $row['status'] ?? null,
            'ops'                           => $row['ops'] ?? null,
            'no_telp_unit'                  => $row['no telp unit'] ?? null,
            'email_unit'                    => $row['email unit'] ?? null,
            'alamat_unit'                   => $row['alamat unit'] ?? null,
            'rt'                            => $row['rt'] ?? null,
            'rw'                            => $row['rw'] ?? null,
            'provinsi'                      => $row['provinsi'] ?? null,
            'kab_kota'                      => $row['kab/kota'] ?? null,
            'kecamatan'                     => $row['kecamatan'] ?? null,
            'kel_desa'                      => $row['kel/desa'] ?? null,
            'kode_pos'                      => $row['kode pos'] ?? null,
            'titik_koordinat'               => $row['titik koordinat'] ?? null,
            'koordinat_s'                   => $row['koordinat s'] ?? null,
            'koordinat_e'                   => $row['koordinat e'] ?? null,

            'no_induk_mitra'                => $row['no induk mitra'] ?? null,
            'nama_mitra'                    => $row['nama mitra'] ?? null,
            'email'                         => $row['email'] ?? $row['email mitra'] ?? null,
            'no_hp'                         => $row['no hp'] ?? $row['no hp mitra'] ?? null,
            'foto'                          => $row['foto'] ?? null,

            'bank'                          => $row['bank'] ?? null,
            'no_rekening'                   => $row['no rekening'] ?? null,
            'atas_nama'                     => $row['atas nama'] ?? null,

            'no_akta'                       => $row['no akta'] ?? null,
            'tgl_akta'                      => $row['tgl akta'] ?? null,
            'nilai_lisensi'                 => $row['nilai lisensi'] ?? null,
            'persen_mitra'                  => $row['% mitra'] ?? null,
            'persen_ypai'                   => $row['% ypai'] ?? null,

            'awal'                          => $row['awal'] ?? null,
            'akhir'                         => $row['akhir'] ?? null,
            'perpanjang'                    => $row['perpanjang'] ?? null,
            'tutup'                         => $row['tutup'] ?? null,

            'jmp'                           => $row['jmp'] ?? null,
            'lpm'                           => $row['lpm'] ?? null,
            'pengembalian'                  => $row['pengembalian'] ?? null,
            'tanggal'                       => $row['tanggal'] ?? null,
            'va_bca'                        => $row['va bca'] ?? null,
            'va_mandiri_royalti'            => $row['va mandiri royalti'] ?? null,
            'va_mandiri_lisensi'            => $row['va mandiri lisensi'] ?? null,
            'marketing'                     => $row['marketing'] ?? null,
            'koorwil_kpk_sos'               => $row['koorwil/kpk/sos'] ?? null,

            'detail'                        => $row['detail'] ?? null,
            'note'                          => $row['note'] ?? null,
            'updated_by'                    => $row['updated by'] ?? 'Import System',
            'history'                       => $row['history'] ?? null,
            'valid'                         => $row['valid'] ?? null,
            'level_user'                    => $row['level user'] ?? null,

            'sisa_3'                        => $row['sisa_3'] ?? null,
            'sisa_1'                        => $row['sisa_1'] ?? null,
            'sisa_2'                        => $row['sisa_2'] ?? null,
            'sisa_4'                        => $row['sisa_4'] ?? null,
            'sisa_f'                        => $row['sisa_f'] ?? null,
            'masa_kontrak'                  => $row['masa kontrak'] ?? null,
            'sisa'                          => $row['sisa'] ?? null,
            'sisa_rr'                       => $row['sisa_rr'] ?? null,

            'no_lokasi'                     => $row['no'] ?? $row['no lokasi'] ?? null,
            'kategori_perubahan'            => $row['kategori perubahan'] ?? null,

            'pdf'                           => $row['pdf'] ?? null,
            'update_pdf'                    => $row['update pdf'] ?? null,
            'last_updated_'                 => $row['last updated_'] ?? null,
            'version'                       => $row['version'] ?? null,

            'vendor_stokis_1'               => $row['vendor stokis 1'] ?? null,
            'vendor_stokis_2'               => $row['vendor stokis 2'] ?? null,
            'sisa_summary'                  => $row['sisa summary'] ?? null,
            'notifikasi_sisa_kontrak_lisensi' => $row['notifikasi sisa kontrak (lisensi)'] ?? null,

            'alamat_saat_ini'               => $row['alamat saat ini'] ?? null,
            'alamat_mitra'                  => $row['alamat mitra'] ?? null,
            'no_cab_bimba_unit'             => $row['no cab - bimba unit'] ?? null,
            'len_perubahan_unit'            => $row['len perubahan unit'] ?? null,
            'kirim_email_lisensi'           => $row['kirim email lisensi'] ?? null,

            'jakarta'                       => $row['jakarta'] ?? null,
            'tanggal_update'                => $row['tanggal update'] ?? null,
            'akun_facebook'                 => $row['akun facebook'] ?? null,
            'akun_instagram'                => $row['akun instagram'] ?? null,
            'akun_media_sosial_unit_bimba_aiueo' => $row['akun media sosial unit bimba aiueo'] ?? null,

            // Kolom dengan titik dan variasi lain
            'awal_tanda'                    => $row['awal.'] ?? null,
            'akhir_tanda'                   => $row['akhir.'] ?? null,
            'perpanjang_tanda'              => $row['perpanjang.'] ?? null,
            'tutup_tanda'                   => $row['tutup.'] ?? null,
        ]);
    }
}