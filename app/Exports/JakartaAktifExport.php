<?php

namespace App\Exports;

use App\Models\JakartaAktif;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JakartaAktifExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = JakartaAktif::query();

        if ($this->request->filled('id_pesan')) {
            $query->where('id_pesan', 'like', '%' . $this->request->id_pesan . '%');
        }
        if ($this->request->filled('kirim')) {
            $query->where('kirim', 'like', '%' . $this->request->kirim . '%');
        }
        if ($this->request->filled('nama_unit')) {
            $query->where('nama_unit', 'like', '%' . $this->request->nama_unit . '%');
        }
        if ($this->request->filled('start_date')) {
            $query->whereDate('tgl_pesan', '>=', $this->request->start_date);
        }
        if ($this->request->filled('end_date')) {
            $query->whereDate('tgl_pesan', '<=', $this->request->end_date);
        }

        return $query->latest('tgl_pesan');
    }

    public function headings(): array
    {
        return [
            'ID Pesan',
            'Nama Unit',
            'Cabang',
            'Alamat Kirim',
            'Kab/Kota',
            'Pesanan',
            'Tgl Pesan',
            'Payment Date',
            'Estimasi Print PL',
            'Estimasi Persiapan',
            'Jasa Kurir',
            'Service Kurir',
            'Status Kirim',
            'Ship Total',
            'Berat',
            'Item Price',
            'Total',
            'Payment Channel',
            'Status Bayar',
            'Status biMBAShop',
            'Tanggal Proses',           // ← BARU
        ];
    }

    public function map($item): array
    {
        return [
            $item->id_pesan,
            $item->nama_unit,
            $item->billing_last_name,
            $item->kirim,
            $item->kab_kota_provinsi,
            $item->pesanan,
            $item->tgl_pesan ? \Carbon\Carbon::parse($item->tgl_pesan)->format('d/m/Y H:i') : '',
            $item->payment_date ? \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i') : '',
            $item->estimasi_print_pl ? \Carbon\Carbon::parse($item->estimasi_print_pl)->format('d/m/Y H:i') : '',
            $item->estimasi_persiapan ? \Carbon\Carbon::parse($item->estimasi_persiapan)->format('d/m/Y H:i') : '',
            $item->ekspedisi,
            $item->service_pengiriman,
            $item->status_kirim,
            $item->ongkir,
            $item->berat,
            $item->harga,
            $item->total,
            $item->jenis_bank,
            $item->status_pembayaran,
            $item->status_pesan,
            $item->processed_at ? \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i') : 'Belum Diproses',  // ← BARU
        ];
    }
}