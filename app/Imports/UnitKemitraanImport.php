<?php

namespace App\Imports;

use App\Models\UnitKemitraan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class UnitKemitraanImport implements ToModel, WithStartRow, WithBatchInserts, WithChunkReading
{
    private function toDecimal($value)
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        if (strpos((string)$value, '#') !== false) {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }
    public function startRow(): int { return 3; }

    private function clean($value)
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        return in_array($value, ['', '-', '?']) ? null : $value;
    }

    private function cleanPhone($value)
    {
        if (empty($value)) return null;
        $value = trim((string) $value);
        if (strpos($value, ',') !== false || strpos($value, '/') !== false) {
            $parts = preg_split('/[,\/]/', $value);
            $value = trim($parts[0]);
        }
        $value = preg_replace('/[^0-9+\-]/', '', $value);
        return $value ?: null;
    }

    // FUNGSI PALING KUAT UNTUK NO_CAB (mengatasi rumus Excel)
    private function rawText($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        $str = trim($str);

        // Jika hasil rumus Excel (angka desimal panjang seperti serial date)
        if (is_numeric($str) && strpos($str, '.') !== false && strlen($str) > 6) {
            return $str;                    // Ambil mentah
        }

        // Jika angka biasa, tetap jadikan string
        if (is_numeric($str)) {
            return $str;
        }

        return $this->clean($str);
    }

    private function parseDate($value)
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            }
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d H:i:s');
            }
            try {
                return Carbon::createFromFormat('d/m/Y', trim($value))->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {}
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        if (empty($row[0])) return null;

        $extractNumber = fn($v) => empty($v) || $v === '-' ? null : preg_replace('/[^0-9.-]/', '', $v);

        return new UnitKemitraan([

            // IDENTITAS UNIT
            'id_record'        => $this->clean($row[0] ?? null),
            'no_cab'           => $this->rawText($row[1] ?? null),           // ← Diperbaiki
            'bimba_aiueo_unit' => $this->clean($row[2] ?? null),
            'status'           => $this->clean($row[3] ?? null),
            'ops'              => $this->clean($row[4] ?? null),
            'no_telp_unit'     => $this->clean($row[5] ?? null),
            'email_unit'       => $this->clean($row[6] ?? null),
            'alamat_unit'      => $this->clean($row[7] ?? null),
            'rt'               => $this->clean($row[8] ?? null),
            'rw'               => $this->clean($row[9] ?? null),
            'provinsi'         => $this->clean($row[10] ?? null),
            'kab_kota'         => $this->clean($row[11] ?? null),
            'kecamatan'        => $this->clean($row[12] ?? null),
            'kel_desa'         => $this->clean($row[13] ?? null),
            'kode_pos'         => $this->rawText($row[14] ?? null),         // ← Diperbaiki
            'titik_koordinat'  => $this->clean($row[15] ?? null),
            'koordinat_s'      => $extractNumber($row[16] ?? null),
            'koordinat_e'      => $extractNumber($row[17] ?? null),

            // MITRA
            'no_induk_mitra'   => $this->clean($row[18] ?? null),
            'nama_mitra'       => $this->clean($row[19] ?? null),
            'email'            => $this->clean($row[20] ?? null),
            'no_hp'            => $this->cleanPhone($row[21] ?? null),
            'foto'             => $this->clean($row[22] ?? null),

            // BANK
            'bank'             => $this->clean($row[23] ?? null),
            'no_rekening'      => $this->clean($row[24] ?? null),
            'atas_nama'        => $this->clean($row[25] ?? null),

            // LISENSI
            'no_akta'          => $this->clean($row[26] ?? null),
            'tgl_akta'         => $this->parseDate($row[27] ?? null),
            'nilai_lisensi'    => $extractNumber($row[28] ?? null),
            'persen_mitra'     => $extractNumber($row[29] ?? null),
            'persen_ypai'      => $extractNumber($row[30] ?? null),

            'awal'             => $this->parseDate($row[31] ?? null),
            'akhir'            => $this->parseDate($row[32] ?? null),
            'perpanjang'       => $this->parseDate($row[33] ?? null),
            'tutup'            => $this->parseDate($row[34] ?? null),

            // VA
            'jmp'              => $this->clean($row[35] ?? null),
            'lpm'              => $this->clean($row[36] ?? null),
            'pengembalian'     => $this->clean($row[37] ?? null),
            'tanggal'          => $this->parseDate($row[38] ?? null),
            'va_bca'           => $this->clean($row[39] ?? null),
            'va_mandiri_royalti' => $this->clean($row[40] ?? null),
            'va_mandiri_lisensi' => $this->clean($row[41] ?? null),
            'marketing'        => $this->clean($row[42] ?? null),
            'koorwil_kpk_sos'  => $this->clean($row[43] ?? null),

            // KETERANGAN
            'detail'           => $this->clean($row[44] ?? null),
            'note'             => $this->clean($row[45] ?? null),
            'updated_by'       => $this->clean($row[46] ?? null) ?? 'Import System',
            'last_updated'     => $this->parseDate($row[47] ?? null),
            'history'          => $this->clean($row[48] ?? null),
            'valid'            => $this->clean($row[49] ?? null),
            'level_user'       => $this->clean($row[50] ?? null),

            // SISA
            'sisa_3'           => $extractNumber($row[51] ?? null),
            'sisa_1'           => $extractNumber($row[52] ?? null),
            'sisa_2'           => $extractNumber($row[53] ?? null),
            'sisa_4'           => $extractNumber($row[54] ?? null),
            'sisa_f'           => $extractNumber($row[55] ?? null),
            'masa_kontrak'     => $this->clean($row[56] ?? null),
            'sisa'             => $this->clean($row[57] ?? null),
            'sisa_rr'          => $extractNumber($row[58] ?? null),

            // PERUBAHAN
            'no'               => $this->clean($row[59] ?? null),
            'lokasi_'          => $this->clean($row[60] ?? null),
            'kategori_perubahan' => $this->clean($row[61] ?? null),

            'awal_kontrak'     => $this->parseDate($row[62] ?? null),
            'akhir_kontrak'    => $this->parseDate($row[63] ?? null),

            'pdf'              => $this->clean($row[64] ?? null),
            'update_pdf'       => $this->clean($row[65] ?? null),

            'last_updated_'    => $this->parseDate($row[66] ?? null),

            'version'          => $this->clean($row[67] ?? null),
            'related_pengajuan_perubahans' => $this->clean($row[68] ?? null),

            'awal_'            => $this->parseDate($row[69] ?? null),
            'awal_kontrak_'    => $this->parseDate($row[70] ?? null),
            'awal_tanda'       => $this->parseDate($row[71] ?? null),
            'akhir_tanda'      => $this->parseDate($row[72] ?? null),
            'perpanjangan_tanda' => $this->parseDate($row[73] ?? null),
            'tutup_tanda'      => $this->parseDate($row[74] ?? null),

            'vendor_stokis_1'  => $this->clean($row[75] ?? null),
            'vendor_stokis_2'  => $this->clean($row[76] ?? null),

            'sisa_summary'     => $this->clean($row[77] ?? null),
            'notifikasi_sisa_kontrak_lisensi' => $this->clean($row[78] ?? null),

            'alamat_saat_ini'  => $this->clean($row[79] ?? null),
            'related_pengajuan_perubahans_by_no_cab' => $this->clean($row[80] ?? null),
            'alamat_mitra'     => $this->clean($row[81] ?? null),
            'nama_mitra_tanda' => $this->clean($row[82] ?? null),
            'no_hp_mitra'      => $this->cleanPhone($row[83] ?? null),
            'email_mitra'      => $this->clean($row[84] ?? null),

            'len_perubahan_unit' => $this->clean($row[85] ?? null),
            'no_cab_bimba_unit'  => $this->rawText($row[86] ?? null),

            'lampiran_jarak_stokis_1' => $this->toDecimal($row[87] ?? null),
            'lampiran_jarak_stokis_2' => $this->toDecimal($row[88] ?? null),

            'keterangan_stokis_1' => $this->clean($row[89] ?? null),
            'keterangan_stokis_2' => $this->clean($row[90] ?? null),
            'kirim_email_lisensi' => $this->clean($row[91] ?? null),

            'pdf_'            => $this->clean($row[92] ?? null),
            'update_pdf_'     => $this->clean($row[93] ?? null),

            'last_updated__'  => $this->parseDate($row[94] ?? null),

            'version_'        => $this->clean($row[95] ?? null),

            'awal_kontrak__'  => $this->parseDate($row[96] ?? null),
            'akhir_kontrak__' => $this->parseDate($row[97] ?? null),

            'jakarta'         => $this->clean($row[98] ?? null),
            'tanggal_update'  => $this->parseDate($row[99] ?? null),
            'tanggal_update__'=> $this->parseDate($row[100] ?? null),

            'masa_kontrak_____' => $this->clean($row[101] ?? null),
            'jika_maka'         => $this->clean($row[102] ?? null),
            'related_perpanjang_kontraks' => $this->clean($row[103] ?? null),

            'cabang_unit_bimba'     => $this->clean($row[104] ?? null),
            'status_ops_unit_bimba' => $this->clean($row[105] ?? null),
            'status_ops_vendor_1'   => $this->clean($row[106] ?? null),
            'status_ops_vendor_2'   => $this->clean($row[107] ?? null),

            'dokumen_tambahan_1' => $this->clean($row[108] ?? null),
            'dokumen_tambahan_2' => $this->clean($row[109] ?? null),
            'dokumen_tambahan_3' => $this->clean($row[110] ?? null),

            'akun_facebook' => $this->clean($row[111] ?? null),
            'akun_instagram' => $this->clean($row[112] ?? null),
            'akun_media_sosial_unit_bimba_aiueo' => $this->clean($row[113] ?? null),
        ]);
    }
}