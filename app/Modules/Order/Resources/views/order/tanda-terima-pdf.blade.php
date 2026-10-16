<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Produk - {{ $no_pl }}</title>

    <style>
        /* Ukuran kertas diatur dari controller: ->setPaper('a6', 'portrait') */
        @page { margin: 8pt 12pt 6pt 12pt; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 6.5pt;
            line-height: 1.15;
            color: #1f2937;
            margin: 0;
        }

        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: middle; }

        .judul { font-size: 7.5pt; font-weight: bold; margin-bottom: 3pt; }

        /* Pita judul biru */
        .pita {
            background-color: #dbe8f5;
            border: 0.5pt solid #9db7d5;
            text-align: center;
            font-weight: bold;
            font-size: 7pt;
            padding: 2pt 0;
        }

        /* Info id pesan / unit / tanggal */
        .info td { padding: 1pt 0; }
        .info .lbl   { width: 42pt; }
        .info .titik { width: 8pt; }
        .info .isi   { font-weight: bold; }
        .garis-bawah { border-bottom: 0.5pt solid #9db7d5; }

        /* Kotak centang */
        .bar-hijau  { background-color: #e2f0d9; padding: 3pt 5pt; }
        .bar-kuning { background-color: #fff9c4; padding: 3pt 5pt; }
        .kotak {
            width: 12pt;
            height: 12pt;
            border: 0.75pt solid #6b7280;
        }

        /* Tabel kekurangan */
        .tabel-kurang th {
            background-color: #dbe8f5;
            border: 0.5pt solid #9db7d5;
            padding: 1.5pt 2pt;
            font-size: 6.5pt;
        }
        .tabel-kurang td {
            border: 0.5pt solid #b8cbe0;
            padding: 1pt 3pt;
            height: 7pt;
            font-size: 6.5pt;
        }

        /* Tanda tangan */
        .ttd td { font-size: 6.5pt; }
        .ttd .ruang-ttd { height: 16pt; }
        .ttd .garis {
            height: 32pt;   /* ruang tanda tangan */
            border-bottom: 0.75pt solid #111;
        }
        .ket { font-size: 5.8pt; line-height: 1.35; }
    </style>
</head>
<body>

    <div class="judul">TANDA TERIMA PRODUK</div>

    {{-- ================= PITA JUDUL ================= --}}
    <div class="pita">Tanda Terima Produk</div>

    {{-- ================= INFO ================= --}}
    <table class="info" style="margin-top:3pt;">
        <tr>
            <td class="lbl">Id Pesan</td>
            <td class="titik">:</td>
            <td class="isi">{{ $no_pl ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Nama Unit</td>
            <td class="titik">:</td>
            <td class="isi">{{ $nama_unit ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Tgl Terima</td>
            <td class="titik">:</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="lbl garis-bawah" style="vertical-align: top;">Note</td>
            <td class="titik garis-bawah" style="vertical-align: top;">:</td>
            <td class="garis-bawah" style="line-height:1.25; padding-bottom:2pt;">
                Mohon Konfirmasi Ke Pihak Pembeli yang Tercantum di Packing Slip
                Apabila Barang Sudah Diterima Dengan Jangka Waktu 3 Hari Setelah
                Barang Diterima.
            </td>
        </tr>
    </table>

    {{-- ================= LENGKAP ================= --}}
    <table style="margin-top:4pt;">
        <tr>
            <td class="bar-hijau" style="width:76%;">
                Modul yang kami terima lengkap tanpa kekurangan
            </td>
            <td style="width:6%; text-align:center;">:</td>
            <td style="width:18%;"><div class="kotak">&nbsp;</div></td>
        </tr>
    </table>

    {{-- ================= TIDAK LENGKAP ================= --}}
    <table style="margin-top:3pt;">
        <tr>
            <td class="bar-kuning" style="width:76%;">
                Modul yang kami terima tidak lengkap<br>
                Adapun kekurangan tersebut, yaitu:
            </td>
            <td style="width:6%; text-align:center;">:</td>
            <td style="width:18%;"><div class="kotak">&nbsp;</div></td>
        </tr>
    </table>

    {{-- ================= TABEL KEKURANGAN ================= --}}
    <table class="tabel-kurang" style="margin-top:4pt;">
        <thead>
            <tr>
                <th style="width:9%;">No</th>
                <th style="width:31%;">Label</th>
                <th style="width:20%;">Jumlah</th>
                <th style="width:40%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= 10; $i++)
                <tr>
                    <td style="text-align:left;">{{ $i }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

{{-- ================= TANDA TANGAN ================= --}}
<table class="ttd" style="margin-top:5pt;">
    <tr>
        <td style="width:8%;">&nbsp;</td>
        <td style="width:20%; text-align:center;">Penerima</td>
        <td style="width:24%;">&nbsp;</td>
        <td style="width:20%; text-align:center;">Mengetahui</td>
        <td style="width:8%;">&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="garis">&nbsp;</td>
        <td>&nbsp;</td>
        <td class="garis">&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>

    {{-- ================= KETERANGAN ================= --}}
    <div class="ket" style="margin-top:5pt;">
        Keterangan :<br>
        * &nbsp;Pengecekan modul dilakukan bersama.<br>
        * &nbsp;Semua data harus diisi dengan lengkap dan jelas.<br>
        * &nbsp;Wajib melampirkan tanda tangan KU dan stempel unit.
    </div>

</body>
</html>