<?php

namespace App\Imports;

use App\Models\UnitKemitraan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
        return 2; // baris 1 = header
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
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '' || $value === '-' || $value === '?' || strtolower($value) === 'null' || $value === '0') {
            return null;
        }

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

        if ($value === '' || $value === '-' || $value === '?' || strtolower($value) === 'null') {
            return null;
        }

        $value = str_replace(['%', ' '], '', $value);
        $value = str_replace('.', '', $value);   // 420.000 → 420000
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === '.' || !is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if (abs($number) > 9999999999) {
            return null;
        }

        return $number;
    }

    private function toSafeDecimal($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (preg_match('/[a-zA-Z°\'\"kmKM]/', (string) $value)) {
            return null;
        }

        $number = $this->extractNumber($value);

        if ($number === null || abs($number) > 999999 || abs($number) < 0.0001) {
            return null;
        }

        return round($number, 4);
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

            $value = trim((string) $value);
            $formats = ['d/m/Y', 'd-M-y', 'd-M-Y', 'd-m-Y', 'Y-m-d', 'd-M-y'];

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

    /**
     * Jika baris masih 1 kolom (delimiter tidak terbaca), pecah manual
     */
    private function normalizeRow(array $row): array
{
    // kalau csv masih menjadi satu kolom
    if (count($row) === 1 && is_string($row[0]) && str_contains($row[0], ';')) {
        $row = str_getcsv($row[0], ';', '"', '\\');
    }

    // reindex
    $row = array_values($row);

    // hilangkan karakter BOM
    foreach ($row as $k => $v) {

        if (is_string($v)) {

            $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);

            // ubah nbsp menjadi spasi biasa
            $v = str_replace("\xc2\xa0", ' ', $v);

            // hapus karakter control
            $v = preg_replace('/[\x00-\x1F\x7F]/u', '', $v);

            $row[$k] = trim($v);
        }
    }

    // pastikan jumlah kolom selalu cukup
    return array_pad($row,150,null);
}

    // =========================================================
    // MODEL
    // =========================================================

    public function model(array $row)
    {
        try {
            $row = $this->normalizeRow($row);
            if (count($row) < 131) {

            Log::warning('Jumlah kolom tidak sesuai',[
                'jumlah' => count($row),
                'row' => $row,
            ]);

            return null;
        }

            $idRecordRaw = $row[0] ?? null;
            $noCabRaw    = $row[1] ?? null;

            $idRecord = $this->cleanIdRecord($idRecordRaw);

            // Normalisasi no_cab
            $noCab = $this->clean($noCabRaw !== null ? (string) $noCabRaw : null);
            if ($noCab !== null && preg_match('/[0-9]/', $noCab)) {
                $noCab = (string) (int) preg_replace('/[^0-9]/', '', $noCab);
            }

            if (empty($noCab) && empty($idRecord)) {
                $this->skippedCount++;
                return null;
            }

            if (empty($noCab)) {
                $this->skippedCount++;
                return null;
            }

            if (empty($idRecord)) {
                $idRecord = 'AUTO-' . $noCab;
            }

            $status = $this->clean($row[3] ?? null);

            $data = [
                'id_record'        => $idRecord,
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

                'no_induk_mitra'   => $this->clean($row[18] ?? null),
                'nama_mitra'       => $this->clean($row[19] ?? null),
                'email'            => $this->clean($row[20] ?? null),
                'no_hp'            => $this->cleanPhone($row[21] ?? null),
                'foto'             => $this->clean($row[22] ?? null),

                'bank'             => $this->clean($row[23] ?? null),
                'no_rekening'      => $this->clean($row[24] ?? null),
                'atas_nama'        => $this->clean($row[25] ?? null),

                'no_akta'          => $this->clean($row[26] ?? null),
                'tgl_akta'         => $this->parseDate($row[27] ?? null),
                'nilai_lisensi'    => $this->extractNumber($row[28] ?? null),
                'persen_mitra'     => $this->extractNumber($row[29] ?? null),
                'persen_ypai'      => $this->extractNumber($row[30] ?? null),

                'awal'             => $this->parseDate($row[31] ?? null),
                'akhir'            => $this->parseDate($row[32] ?? null),
                'perpanjang'       => $this->parseDate($row[33] ?? null),
                'tutup'            => $this->parseDate($row[34] ?? null),

                'jmp'                  => $this->clean($row[35] ?? null),
                'lpm'                  => $this->clean($row[36] ?? null),
                'pengembalian'         => $this->clean($row[37] ?? null),
                'tanggal'              => $this->parseDate($row[38] ?? null),
                'va_bca'               => $this->clean($row[39] ?? null),
                'va_mandiri_royalti'   => $this->clean($row[40] ?? null),
                'va_mandiri_lisensi'   => $this->clean($row[41] ?? null),
                'marketing'            => $this->clean($row[42] ?? null),
                'koorwil_kpk_sos'      => $this->clean($row[43] ?? null),

                'detail'           => $this->clean($row[44] ?? null),
                'note'             => $this->clean($row[45] ?? null),
                'updated_by'       => $this->clean($row[46] ?? null) ?? 'Import System',
                'last_updated'     => $this->parseDate($row[47] ?? null),
                'history'          => $this->clean($row[48] ?? null),
                'valid'            => $this->clean($row[49] ?? null),
                'level_user'       => $this->clean($row[50] ?? null),

                'sisa_3'           => $this->extractNumber($row[51] ?? null),
                'sisa_1'           => $this->extractNumber($row[52] ?? null),
                'sisa_2'           => $this->extractNumber($row[53] ?? null),
                'sisa_4'           => $this->extractNumber($row[54] ?? null),
                'sisa_f'           => $this->extractNumber($row[55] ?? null),
                'masa_kontrak'     => $this->clean($row[56] ?? null),
                'sisa'             => $this->clean($row[57] ?? null),
                'sisa_rr'          => $this->extractNumber($row[58] ?? null),

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
                'lampiran_jarak_stokis_1'  => $this->toSafeDecimal($row[87] ?? null),
                'lampiran_jarak_stokis_2'  => $this->toSafeDecimal($row[88] ?? null),
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

                // MEMO
                'status_unit'          => $this->clean($row[114] ?? null),
                'pdf_memo'             => $this->clean($row[115] ?? null),
                'update_pdf_memo'      => $this->clean($row[116] ?? null),
                'last_updated_memo'    => $this->parseDate($row[117] ?? null),
                'version_memo'         => $this->clean($row[118] ?? null),
                'kirim_email_memo'     => $this->clean($row[119] ?? null),
                'tgl_kirim_email_memo' => $this->parseDate($row[120] ?? null),

                // MARKETING & ALAMAT MITRA
                'nama_marketing_'      => $this->clean($row[121] ?? null),
                'no_rumah'             => $this->clean($row[122] ?? null),
                'rt_mitra'             => $this->clean($row[123] ?? null),
                'rw_mitra'             => $this->clean($row[124] ?? null),
                'kel_mitra'            => $this->clean($row[125] ?? null),
                'kec_mitra'            => $this->clean($row[126] ?? null),
                'kota_mitra'           => $this->clean($row[127] ?? null),
                'provinsi_mitra'       => $this->clean($row[128] ?? null),
                'kode_pos_mitra'       => $this->clean($row[129] ?? null),
                'email_marketing_'     => $this->clean($row[130] ?? null),

                'status_pengelolaan'   => $this->getStatusPengelolaan($status),
                'mitra_pengelolaan'    => $this->getMitraPengelolaan($status),
            ];

            // INSERT / UPDATE
            $existing = UnitKemitraan::withTrashed()
                ->where('no_cab', $noCab)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->fill($data);
                $existing->save();
            } else {
                UnitKemitraan::create($data);
            }

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
                    'failed_rows' => array_slice($this->failedRows, 0, 20),
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
        ];
    }
}