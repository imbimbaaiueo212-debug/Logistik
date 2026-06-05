<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::with('category')
            ->get()
            ->map(function ($p) {
                return [
                    'kode'           => $p->kode,
                    'kategori'       => $p->category?->nama, // 🔥 ambil dari relasi
                    'name'           => $p->name,
                    'sku'            => $p->sku,
                    'jenis'          => $p->jenis,
                    'satuan'         => $p->satuan,
                    'berat_satuan'   => $p->berat_satuan,
                    'isi'            => $p->isi,
                    'harga_beli'     => $p->harga_beli,
                    'harga_jual'     => $p->harga_jual,
                    'status'         => $p->status,
                    'role'           => $p->role,
                    'tanggal_rilis'  => $p->tanggal_rilis,
                    'hal'            => $p->hal,
                    'lembar'         => $p->lembar,
                    'kertas'         => $p->kertas,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'kode',
            'kategori',
            'name',
            'sku',
            'jenis',
            'satuan',
            'berat_satuan',
            'isi',
            'harga_beli',
            'harga_jual',
            'status',
            'role',
            'tanggal_rilis',
            'hal',
            'lembar',
            'kertas',
        ];
    }
}