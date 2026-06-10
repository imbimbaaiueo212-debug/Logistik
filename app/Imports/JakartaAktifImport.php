<?php

namespace App\Imports;

use App\Models\JakartaAktif;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class JakartaAktifImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    public function model(array $row)
    {
        return new JakartaAktif([
            'tgl_input'           => $row['tgl_input'] ?? null,
            'tgl_pesan'           => $row['tgl_pesan'] ?? null,
            'kirim'               => $row['kirim'],
            'no_telpon'           => $row['no_telpon'],
            'alamat_kirim'        => $row['alamat_kirim'],
            'kab_kota_provinsi'   => $row['kab/kota-provinsi'] ?? $row['kab_kota_provinsi'],
            'ekspedisi'           => $row['ekspedisi'],
            'ongkir'              => $row['ongkir'] ?? 0,
            'service_pengiriman'  => $row['service_pengiriman'],
            'tracking_number'     => $row['tracking_number'] ?? null,
            'validasi'            => $row['validasi'],
            'jenis_bank'          => $row['jenis_bank'],
            'status_pembayaran'   => $row['status_pembayaran'],
            'id_pesan'            => $row['id_pesan'],
            'cabang'              => $row['cabang'],
            'nama_unit'           => $row['nama_unit'],
            'vendor'              => $row['vendor'],
            'pesanan'             => $row['pesanan'],
            'status_pesan'        => $row['status_pesan'],
            'berat'               => $row['berat'] ?? 0,
            'harga'               => $row['harga'] ?? 0,
            'diskon'              => $row['diskon'] ?? 0,
            'fee_payment'         => $row['fee_payment'] ?? 0,
            'total'               => $row['total'] ?? 0,
            'status'              => 'aktif',
            'sales'               => $row['sales'] ?? auth()->user()->name ?? 'System',
            'catatan'             => $row['catatan'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}