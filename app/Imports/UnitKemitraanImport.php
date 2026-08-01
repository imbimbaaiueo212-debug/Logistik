<?php

namespace App\Imports;

use App\Models\UnitKemitraan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class UnitKemitraanImport implements
    ToModel,
    WithStartRow,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnError,
    WithCustomCsvSettings,
    WithEvents
{
    use SkipsErrors;

    public int $successCount = 0;
    public int $failedCount  = 0;
    public int $skippedCount = 0;
    public array $failedRows = [];

    public function batchSize(): int
    {
        return 300;
    }

    public function chunkSize(): int
    {
        return 300;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter'        => ';',
            'enclosure'        => '"',
            'escape_character' => '\\',
            'contiguous'       => false,
            'input_encoding'   => 'UTF-8',
        ];
    }

    // =========================================================
    // HELPER
    // =========================================================

    private function clean($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return in_array($value, ['', '-', '?'], true) ? null : $value;
    }

    private function cleanIdRecord($value)
{
    // Biarkan null jika benar-benar kosong
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    // Kalau setelah di-trim kosong atau hanya tanda strip, biarkan null
    if ($value === '' || $value === '-' || $value === '?') {
        return null;
    }

    // Kembalikan nilai asli dari Excel (termasuk "0" jika memang 0 di Excel)
    return $value;
}

    private function cleanPhone($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if (str_contains($value, ',') || str_contains($value, '/')) {
            $parts = preg_split('/[,\/]/', $value);
            $value = trim($parts[0]);
        }

        return preg_replace('/[^0-9+\-]/', '', $value) ?: null;
    }

    private function extractNumber($value)
{
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    // Langsung null jika kosong atau tanda strip
    if ($value === '' || $value === '-' || $value === '?' || strtolower($value) === 'null') {
        return null;
    }

    // Hilangkan karakter yang tidak perlu
    $value = str_replace(['%', ' '], '', $value);
    $value = str_replace('.', '', $value);   // hapus titik ribuan
    $value = str_replace(',', '.', $value);  // koma jadi titik

    // Hanya izinkan angka, titik, dan minus
    $value = preg_replace('/[^0-9.\-]/', '', $value);

    // Kalau setelah dibersihkan masih kosong atau bukan angka
    if ($value === '' || $value === '-' || $value === '.' || !is_numeric($value)) {
        return null;
    }

    $number = (float) $value;

    // Proteksi angka ekstrem (mencegah out of range)
    if (abs($number) > 9999999999) {
        return null;
    }

    return $number;
}

    /**
     * Versi aman untuk kolom jarak (mencegah Out of range)
     */
    private function toSafeDecimal($value)
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        // Kalau mengandung huruf / simbol aneh → null
        if (preg_match('/[a-zA-Z°\'\"kmKM]/', (string)$value)) {
            return null;
        }

        $number = $this->extractNumber($value);

        // Proteksi angka ekstrem
        if ($number === null || abs($number) > 999999 || abs($number) < 0.0001) {
            return null;
        }

        return round($number, 4); // maksimal 4 digit di belakang koma
    }

    private function parseDate($value)
    {
        if ($value === null || $value === '' || $value === '-' || $value == 0) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                if ($value < 30000) {
                    return null;
                }
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            }

            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d H:i:s');
            }

            $value = trim($value);

            $formats = ['d/m/Y', 'd-M-y', 'd-M-Y', 'd-m-Y', 'Y-m-d'];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('Y-m-d H:i:s');
                } catch (Throwable $e) {
                }
            }

            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return null;
        }
    }

    private function getStatusPengelolaan(?string $status): ?string
    {
        if (empty($status)) {
            return null;
        }

        $status = strtoupper(trim($status));

        $statusAktif = [
            'MM', 'MM 1', 'AKTIF 1', 'MK', 'MK 1',
            'MK RINDA', 'MKU', 'MKU 1', 'UNIT AKTIF',
            'E-BIMBA AKTIF',
        ];

        if (in_array($status, $statusAktif, true)) {
            return 'Unit Aktif';
        }

        if (str_contains($status, 'AKTIF KAB') || str_contains($status, 'AKTIF KEC')) {
            return null;
        }

        if (str_contains($status, 'PASIF KAB') || str_contains($status, 'PASIF KEC')) {
            return null;
        }

        if (str_contains($status, 'STOCKIST')) {
            return null;
        }

        if (str_contains($status, 'E-BIMBA PASIF')) {
            return 'Unit Pasif';
        }

        if (str_contains($status, 'PASIF') || str_contains($status, 'UNIT PASIF')) {
            return 'Unit Pasif';
        }

        return null;
    }

    private function getMitraPengelolaan(?string $status): ?string
    {
        if (empty($status)) {
            return null;
        }

        $statusUpper = strtoupper(trim($status));

        if (str_contains($statusUpper, 'OPS1')) {
            return 'OPS1';
        }

        if (preg_match('/1/', $statusUpper)) {
            return 'PUW1 | OPS1';
        }

        return 'YPAI';
    }

    // =========================================================
    // MODEL
    // =========================================================

    public function model(array $row)
    {
        try {
            $row = array_pad($row, 120, null);

            $idRecordRaw = $row[0] ?? null;
            $noCabRaw    = $row[1] ?? null;

            $isIdEmpty = ($idRecordRaw === null || trim((string)$idRecordRaw) === '' || trim((string)$idRecordRaw) === '-');
            $isNoCabEmpty = ($noCabRaw === null || trim((string)$noCabRaw) === '' || trim((string)$noCabRaw) === '-');

            if ($isIdEmpty && $isNoCabEmpty) {
                $this->skippedCount++;
                return null;
            }

            $noCab = $this->clean($noCabRaw);

            if (empty($noCab)) {
                $this->skippedCount++;
                return null;
            }

            $status = $this->clean($row[3] ?? null);

            $data = [
                // IDENTITAS UNIT
                'id_record'        => $this->cleanIdRecord($idRecordRaw),
                'no_cab'           => $noCab,
                'bimba_aiueo_unit' => $this->clean($row[2] ?? null),
                'status'           => $status,
                'ops'              => $this->clean($row[4] ?? null),
                'no_telp_unit'     => $this->cleanPhone($row[5] ?? null),
                'email_unit'       => $this->clean($row[6] ?? null),
                'alamat_unit'      => $this->clean($row[7] ?? null),
                'rt'               => $this->clean($row[8] ?? null),
                'rw'               => $this->clean($row[9] ?? null),
                'provinsi'         => $this->clean($row[10] ?? null),
                'kab_kota'         => $this->clean($row[11] ?? null),
                'kecamatan'        => $this->clean($row[12] ?? null),
                'kel_desa'         => $this->clean($row[13] ?? null),
                'kode_pos'         => $this->clean($row[14] ?? null),
                'titik_koordinat'  => $this->clean($row[15] ?? null),
                'koordinat_s'      => $this->clean($row[16] ?? null),
                'koordinat_e'      => $this->clean($row[17] ?? null),

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
                'nilai_lisensi'    => $this->extractNumber($row[28] ?? null),
                'persen_mitra'     => $this->extractNumber($row[29] ?? null),
                'persen_ypai'      => $this->extractNumber($row[30] ?? null),

                'awal'             => $this->parseDate($row[31] ?? null),
                'akhir'            => $this->parseDate($row[32] ?? null),
                'perpanjang'       => $this->parseDate($row[33] ?? null),
                'tutup'            => $this->parseDate($row[34] ?? null),

                // VA
                'jmp'                  => $this->clean($row[35] ?? null),
                'lpm'                  => $this->clean($row[36] ?? null),
                'pengembalian'         => $this->clean($row[37] ?? null),
                'tanggal'              => $this->parseDate($row[38] ?? null),
                'va_bca'               => $this->clean($row[39] ?? null),
                'va_mandiri_royalti'   => $this->clean($row[40] ?? null),
                'va_mandiri_lisensi'   => $this->clean($row[41] ?? null),
                'marketing'            => $this->clean($row[42] ?? null),
                'koorwil_kpk_sos'      => $this->clean($row[43] ?? null),

                // KETERANGAN
                'detail'           => $this->clean($row[44] ?? null),
                'note'             => $this->clean($row[45] ?? null),
                'updated_by'       => $this->clean($row[46] ?? null) ?? 'Import System',
                'last_updated'     => $this->parseDate($row[47] ?? null),
                'history'          => $this->clean($row[48] ?? null),
                'valid'            => $this->clean($row[49] ?? null),
                'level_user'       => $this->clean($row[50] ?? null),

                // SISA
                'sisa_3'           => $this->extractNumber($row[51] ?? null),
                'sisa_1'           => $this->extractNumber($row[52] ?? null),
                'sisa_2'           => $this->extractNumber($row[53] ?? null),
                'sisa_4'           => $this->extractNumber($row[54] ?? null),
                'sisa_f'           => $this->extractNumber($row[55] ?? null),
                'masa_kontrak'     => $this->clean($row[56] ?? null),
                'sisa'             => $this->clean($row[57] ?? null),
                'sisa_rr'          => $this->extractNumber($row[58] ?? null),

                // PERUBAHAN
                'no'                       => $this->clean($row[59] ?? null),
                'lokasi_'                  => $this->clean($row[60] ?? null),
                'kategori_perubahan'       => $this->clean($row[61] ?? null),
                'awal_kontrak'             => $this->parseDate($row[62] ?? null),
                'akhir_kontrak'            => $this->parseDate($row[63] ?? null),
                'pdf'                      => $this->clean($row[64] ?? null),
                'update_pdf'               => $this->clean($row[65] ?? null),
                'last_updated_'            => $this->parseDate($row[66] ?? null),
                'version'                  => $this->clean($row[67] ?? null),
                'related_pengajuan_perubahans' => $this->clean($row[68] ?? null),

                'awal_'                    => $this->parseDate($row[69] ?? null),
                'awal_kontrak_'            => $this->parseDate($row[70] ?? null),
                'awal_tanda'               => $this->parseDate($row[71] ?? null),
                'akhir_tanda'              => $this->parseDate($row[72] ?? null),
                'perpanjangan_tanda'       => $this->parseDate($row[73] ?? null),
                'tutup_tanda'              => $this->parseDate($row[74] ?? null),

                'vendor_stokis_1'          => $this->clean($row[75] ?? null),
                'vendor_stokis_2'          => $this->clean($row[76] ?? null),
                'sisa_summary'             => $this->clean($row[77] ?? null),
                'notifikasi_sisa_kontrak_lisensi' => $this->clean($row[78] ?? null),
                'alamat_saat_ini'          => $this->clean($row[79] ?? null),
                'related_pengajuan_perubahans_by_no_cab' => $this->clean($row[80] ?? null),
                'alamat_mitra'             => $this->clean($row[81] ?? null),
                'nama_mitra_tanda'         => $this->clean($row[82] ?? null),
                'no_hp_mitra'              => $this->cleanPhone($row[83] ?? null),
                'email_mitra'              => $this->clean($row[84] ?? null),

                'len_perubahan_unit'       => $this->clean($row[85] ?? null),
                'no_cab_bimba_unit'        => $this->clean($row[86] ?? null),

                // ===== YANG DIPERBAIKI =====
                'lampiran_jarak_stokis_1'  => $this->toSafeDecimal($row[87] ?? null),
                'lampiran_jarak_stokis_2'  => $this->toSafeDecimal($row[88] ?? null),
                // ============================

                'keterangan_stokis_1'      => $this->clean($row[89] ?? null),
                'keterangan_stokis_2'      => $this->clean($row[90] ?? null),
                'kirim_email_lisensi'      => $this->clean($row[91] ?? null),

                'pdf_'                     => $this->clean($row[92] ?? null),
                'update_pdf_'              => $this->clean($row[93] ?? null),
                'last_updated__'           => $this->parseDate($row[94] ?? null),
                'version_'                 => $this->clean($row[95] ?? null),
                'awal_kontrak__'           => $this->parseDate($row[96] ?? null),
                'akhir_kontrak__'          => $this->parseDate($row[97] ?? null),

                'jakarta'                  => $this->clean($row[98] ?? null),
                'tanggal_update'           => $this->parseDate($row[99] ?? null),
                'tanggal_update__'         => $this->parseDate($row[100] ?? null),
                'masa_kontrak_____'        => $this->clean($row[101] ?? null),
                'jika_maka'                => $this->clean($row[102] ?? null),
                'related_perpanjang_kontraks' => $this->clean($row[103] ?? null),

                'cabang_unit_bimba'        => $this->clean($row[104] ?? null),
                'status_ops_unit_bimba'    => $this->clean($row[105] ?? null),
                'status_ops_vendor_1'      => $this->clean($row[106] ?? null),
                'status_ops_vendor_2'      => $this->clean($row[107] ?? null),

                'dokumen_tambahan_1'       => $this->clean($row[108] ?? null),
                'dokumen_tambahan_2'       => $this->clean($row[109] ?? null),
                'dokumen_tambahan_3'       => $this->clean($row[110] ?? null),

                'akun_facebook'            => $this->clean($row[111] ?? null),
                'akun_instagram'           => $this->clean($row[112] ?? null),
                'akun_media_sosial_unit_bimba_aiueo' => $this->clean($row[113] ?? null),

                // Otomatis
                'status_pengelolaan'       => $this->getStatusPengelolaan($status),
                'mitra_pengelolaan'        => $this->getMitraPengelolaan($status),
            ];

            UnitKemitraan::updateOrCreate(
                ['no_cab' => $noCab],
                $data
            );

            $this->successCount++;

        } catch (Throwable $e) {
            $this->failedCount++;

            $this->failedRows[] = [
                'no_cab'    => $row[1] ?? '-',
                'id_record' => $row[0] ?? '-',
                'reason'    => $e->getMessage(),
            ];

            Log::error('UnitKemitraan Import Error', [
                'no_cab'    => $row[1] ?? null,
                'id_record' => $row[0] ?? null,
                'message'   => $e->getMessage(),
                'line'      => $e->getLine(),
            ]);
        }

        return null;
    }

    public function onError(Throwable $e)
    {
        $this->failedCount++;
        Log::error('UnitKemitraan Import onError', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                Log::info('========== UnitKemitraan Import SELESAI ==========', [
                    'success'     => $this->successCount,
                    'failed'      => $this->failedCount,
                    'skipped'     => $this->skippedCount,
                    'failed_rows' => array_slice($this->failedRows, 0, 20), // batasi agar log tidak terlalu besar
                ]);
            },
        ];
    }

    public function getSummary(): array
    {
        return [
            'success'     => $this->successCount,
            'failed'      => $this->failedCount,
            'skipped'     => $this->skippedCount,
            'failed_rows' => $this->failedRows,
            'errors'      => method_exists($this, 'failures') ? $this->failures() : [],
        ];
    }
}