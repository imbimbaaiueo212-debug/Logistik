<?php

namespace App\Modules\Master\Support;

/**
 * Daftar harga dari dokumen Mitra Bimba Abadi (Jakarta, 23 November 2026, berlaku 1 Desember 2026):
 *  - Perbandingan Harga Modul & Raport biMBA AIUEO 2024-2026 (95 item)
 *  - Perbandingan Harga Seragam, Tas & KPK biMBA AIUEO 2024-2026 (9 item)
 * Harga dalam rupiah per unit. Jika ada revisi dokumen, ubah angka di sini lalu klik "Muat Ulang Daftar".
 */
class PenyesuaianHargaData
{
    public const BERLAKU_MULAI = '2026-12-01';

    public const GRUP_MODUL   = 'Modul & Raport';
    public const GRUP_SERAGAM = 'Seragam, Tas & KPK';

    /**
     * Nama di PDF yang beda jauh dengan nama di aplikasi: dipetakan lewat Label (SKU) produk.
     * Format: 'nama di PDF' => 'label di Master Product'.
     */
    public const ALIAS_LABEL = [
        'Raport + Sampul A4'      => 'RBAS',     // aplikasi: Rapor Murid + Sampul
        'Kaos Seragam Anak 3XLS'  => 'KAXXXLS',  // aplikasi: Kaos Anak XXXLS
    ];

    public static function items(): array
    {
        return [
        ['grup' => 'Modul & Raport', 'no' => 1, 'nama' => 'Modul Baca 1A', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 2, 'nama' => 'Modul Baca 1B', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 3, 'nama' => 'Modul Baca 1C', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 4, 'nama' => 'Modul Baca 1D', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 5, 'nama' => 'Modul Baca 1E', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 6, 'nama' => 'Modul Baca 1F', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 7, 'nama' => 'Modul Baca 1G', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 8, 'nama' => 'Modul Baca 1H', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 9, 'nama' => 'Modul Baca 2', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 10, 'nama' => 'Modul Baca 3', 'harga_2024' => 3596, 'harga_2026' => 4410],
        ['grup' => 'Modul & Raport', 'no' => 11, 'nama' => 'Modul Baca 4', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 12, 'nama' => 'Modul Baca 5', 'harga_2024' => 3596, 'harga_2026' => 4410],
        ['grup' => 'Modul & Raport', 'no' => 13, 'nama' => 'Modul Baca 6', 'harga_2024' => 2697, 'harga_2026' => 3330],
        ['grup' => 'Modul & Raport', 'no' => 14, 'nama' => 'Modul Baca 7', 'harga_2024' => 1439, 'harga_2026' => 1784],
        ['grup' => 'Modul & Raport', 'no' => 15, 'nama' => 'Modul Baca 8', 'harga_2024' => 2517, 'harga_2026' => 3110],
        ['grup' => 'Modul & Raport', 'no' => 16, 'nama' => 'Modul Baca 9', 'harga_2024' => 2517, 'harga_2026' => 3110],
        ['grup' => 'Modul & Raport', 'no' => 17, 'nama' => 'Modul Baca 10', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 18, 'nama' => 'Modul Baca 11', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 19, 'nama' => 'Modul Baca 12A', 'harga_2024' => 4675, 'harga_2026' => 5790],
        ['grup' => 'Modul & Raport', 'no' => 20, 'nama' => 'Modul Tulis 1A', 'harga_2024' => 3956, 'harga_2026' => 4850],
        ['grup' => 'Modul & Raport', 'no' => 21, 'nama' => 'Modul Tulis 1B', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 22, 'nama' => 'Modul Tulis 2', 'harga_2024' => 2338, 'harga_2026' => 2899],
        ['grup' => 'Modul & Raport', 'no' => 23, 'nama' => 'Modul Tulis 3', 'harga_2024' => 2338, 'harga_2026' => 2899],
        ['grup' => 'Modul & Raport', 'no' => 24, 'nama' => 'Modul Tulis 4', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 25, 'nama' => 'Modul Tulis 5', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 26, 'nama' => 'Modul Tulis 6', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 27, 'nama' => 'Modul Tulis 7', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 28, 'nama' => 'Modul Tulis 8', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 29, 'nama' => 'Modul Tulis 9', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 30, 'nama' => 'Modul Tulis 10', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 31, 'nama' => 'Modul Tulis 11', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 32, 'nama' => 'Modul Tulis 12A', 'harga_2024' => 5754, 'harga_2026' => 7090],
        ['grup' => 'Modul & Raport', 'no' => 33, 'nama' => 'Modul Tulis 12B', 'harga_2024' => 5754, 'harga_2026' => 7090],
        ['grup' => 'Modul & Raport', 'no' => 34, 'nama' => 'Modul Matematika 1A', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 35, 'nama' => 'Modul Matematika 1B', 'harga_2024' => 4496, 'harga_2026' => 5500],
        ['grup' => 'Modul & Raport', 'no' => 36, 'nama' => 'Modul Matematika 2A', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 37, 'nama' => 'Modul Matematika 2B', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 38, 'nama' => 'Modul Matematika 3A', 'harga_2024' => 4316, 'harga_2026' => 5280],
        ['grup' => 'Modul & Raport', 'no' => 39, 'nama' => 'Modul Matematika 3B', 'harga_2024' => 7912, 'harga_2026' => 9690],
        ['grup' => 'Modul & Raport', 'no' => 40, 'nama' => 'Modul Matematika 4A', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 41, 'nama' => 'Modul Matematika 4B', 'harga_2024' => 9351, 'harga_2026' => 11430],
        ['grup' => 'Modul & Raport', 'no' => 42, 'nama' => 'Modul Matematika 5A', 'harga_2024' => 5754, 'harga_2026' => 7090],
        ['grup' => 'Modul & Raport', 'no' => 43, 'nama' => 'Modul Matematika 5B', 'harga_2024' => 5754, 'harga_2026' => 7090],
        ['grup' => 'Modul & Raport', 'no' => 44, 'nama' => 'Modul Matematika 6A', 'harga_2024' => 6474, 'harga_2026' => 7960],
        ['grup' => 'Modul & Raport', 'no' => 45, 'nama' => 'Modul Matematika 6B', 'harga_2024' => 6474, 'harga_2026' => 7960],
        ['grup' => 'Modul & Raport', 'no' => 46, 'nama' => 'Modul Matematika 7A', 'harga_2024' => 7193, 'harga_2026' => 8820],
        ['grup' => 'Modul & Raport', 'no' => 47, 'nama' => 'Modul Matematika 7B', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 48, 'nama' => 'Modul Huruf Sambung 1', 'harga_2024' => 4675, 'harga_2026' => 5790],
        ['grup' => 'Modul & Raport', 'no' => 49, 'nama' => 'Modul Huruf Sambung 2', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 50, 'nama' => 'Modul Huruf Sambung 3', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 51, 'nama' => 'Modul Huruf Sambung 4', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 52, 'nama' => 'Modul Dikte 1', 'harga_2024' => 10789, 'harga_2026' => 13030],
        ['grup' => 'Modul & Raport', 'no' => 53, 'nama' => 'Modul Dikte 2', 'harga_2024' => 10789, 'harga_2026' => 13030],
        ['grup' => 'Modul & Raport', 'no' => 54, 'nama' => 'Modul Dikte 3', 'harga_2024' => 2877, 'harga_2026' => 3567],
        ['grup' => 'Modul & Raport', 'no' => 55, 'nama' => 'Modul Dikte 4', 'harga_2024' => 7193, 'harga_2026' => 8820],
        ['grup' => 'Modul & Raport', 'no' => 56, 'nama' => 'Modul Dikte 5', 'harga_2024' => 4316, 'harga_2026' => 5350],
        ['grup' => 'Modul & Raport', 'no' => 57, 'nama' => 'Modul Dikte 6', 'harga_2024' => 7193, 'harga_2026' => 8820],
        ['grup' => 'Modul & Raport', 'no' => 58, 'nama' => 'Modul Evaluasi Sumatif 1', 'harga_2024' => 3776, 'harga_2026' => 4682],
        ['grup' => 'Modul & Raport', 'no' => 59, 'nama' => 'Modul Evaluasi Sumatif 2', 'harga_2024' => 2158, 'harga_2026' => 2675],
        ['grup' => 'Modul & Raport', 'no' => 60, 'nama' => 'Modul Evaluasi Sumatif 3', 'harga_2024' => 1978, 'harga_2026' => 2452],
        ['grup' => 'Modul & Raport', 'no' => 61, 'nama' => 'Hore Aku Bisa Baca Cerita 1', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 62, 'nama' => 'Hore Aku Bisa Baca Cerita 2', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 63, 'nama' => 'Hore Aku Bisa Baca Cerita 3', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 64, 'nama' => 'Hore Aku Bisa Baca Cerita 4', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 65, 'nama' => 'Hore Aku Bisa Baca Cerita 5', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 66, 'nama' => 'Hore Aku Bisa Baca Cerita 6', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 67, 'nama' => 'Hore Aku Bisa Baca Cerita 7', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 68, 'nama' => 'Hore Aku Bisa Baca Cerita 8', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 69, 'nama' => 'Hore Aku Bisa Baca Cerita 9', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 70, 'nama' => 'Hore Aku Bisa Baca Cerita 10', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 71, 'nama' => 'Hore Aku Bisa Baca Kalimat 1', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 72, 'nama' => 'Hore Aku Bisa Baca Kalimat 2', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 73, 'nama' => 'Hore Aku Bisa Baca Kalimat 3', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 74, 'nama' => 'Hore Aku Bisa Baca Kalimat 4', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 75, 'nama' => 'Hore Aku Bisa Baca Kalimat 5', 'harga_2024' => 1798, 'harga_2026' => 2229],
        ['grup' => 'Modul & Raport', 'no' => 76, 'nama' => 'Pengurangan Tanpa Meminjam', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 77, 'nama' => 'Pengurangan Dengan Meminjam', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 78, 'nama' => 'Penjumlahan Tanpa Menyimpan', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 79, 'nama' => 'Penjumlahan Dengan Menyimpan', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 80, 'nama' => 'Penjumlahan Bersusun Dengan Menyimpan', 'harga_2024' => 7193, 'harga_2026' => 8820],
        ['grup' => 'Modul & Raport', 'no' => 81, 'nama' => 'Lambang Bilangan', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 82, 'nama' => 'Pilihan Ganda', 'harga_2024' => 5754, 'harga_2026' => 7090],
        ['grup' => 'Modul & Raport', 'no' => 83, 'nama' => 'Nama Bilangan', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 84, 'nama' => 'Soal Cerita', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 85, 'nama' => 'Soal Bacaan', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 86, 'nama' => 'Nilai Tempat', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 87, 'nama' => 'Menuliskan Bilangan 2 Angka dalam Bentuk Panjang', 'harga_2024' => 3596, 'harga_2026' => 4459],
        ['grup' => 'Modul & Raport', 'no' => 88, 'nama' => 'Raport + Sampul A4', 'harga_2024' => 9738, 'harga_2026' => 12000],
        ['grup' => 'Modul & Raport', 'no' => 89, 'nama' => 'Modul Mewarnai 1', 'harga_2024' => 6779, 'harga_2026' => 8400],
        ['grup' => 'Modul & Raport', 'no' => 90, 'nama' => 'Modul Mewarnai 2', 'harga_2024' => 6779, 'harga_2026' => 8400],
        ['grup' => 'Modul & Raport', 'no' => 91, 'nama' => 'Modul Mewarnai 3', 'harga_2024' => 6779, 'harga_2026' => 8400],
        ['grup' => 'Modul & Raport', 'no' => 92, 'nama' => 'Modul Mewarnai 4', 'harga_2024' => 6779, 'harga_2026' => 8400],
        ['grup' => 'Modul & Raport', 'no' => 93, 'nama' => 'Modul Mewarnai 5', 'harga_2024' => 6779, 'harga_2026' => 8400],
        ['grup' => 'Modul & Raport', 'no' => 94, 'nama' => 'Modul Pelangi', 'harga_2024' => 5423, 'harga_2026' => 6700],
        ['grup' => 'Modul & Raport', 'no' => 95, 'nama' => 'Modul Lembar Mengarang', 'harga_2024' => 4926, 'harga_2026' => 6100],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 1, 'nama' => 'Kaos Seragam Anak S', 'harga_2024' => 45820, 'harga_2026' => 56800],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 2, 'nama' => 'Kaos Seragam Anak M', 'harga_2024' => 49980, 'harga_2026' => 61900],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 3, 'nama' => 'Kaos Seragam Anak L', 'harga_2024' => 49980, 'harga_2026' => 61900],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 4, 'nama' => 'Kaos Seragam Anak XL', 'harga_2024' => 55930, 'harga_2026' => 69300],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 5, 'nama' => 'Kaos Seragam Anak XXL', 'harga_2024' => 55930, 'harga_2026' => 69300],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 6, 'nama' => 'Kaos Seragam Anak XXXL', 'harga_2024' => 58900, 'harga_2026' => 73000],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 7, 'nama' => 'Kaos Seragam Anak 3XLS', 'harga_2024' => 69620, 'harga_2026' => 86300],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 8, 'nama' => 'Tas biMBA', 'harga_2024' => 58310, 'harga_2026' => 65300],
        ['grup' => 'Seragam, Tas & KPK', 'no' => 9, 'nama' => 'KPK (Kotak Permainan Kata)', 'harga_2024' => 5950, 'harga_2026' => 8000],
        ];
    }
}
