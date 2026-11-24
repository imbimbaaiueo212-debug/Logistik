<?php

namespace App\Modules\Master\Services;

/**
 * Membaca tabel "Perbandingan Harga ... 2024-2026" dari PDF percetakan.
 * Tiap baris tabel: No | Nama Produk | Harga 2024 | Harga 2026 | (Kenaikan %, diabaikan).
 *
 * Pembacaan tidak bergantung pada tata letak kolom: teks PDF diratakan jadi satu baris,
 * lalu baris dicari berurutan berdasarkan nomor (1, 2, 3, ...). Harga pertama di tiap baris
 * dianggap Harga 2024 dan harga terakhir dianggap Harga 2026.
 *
 * Butuh paket smalot/pdfparser:  composer require smalot/pdfparser
 */
class PenyesuaianHargaPdfParser
{
    /** Angka harga: 2.158 | 2,158 | 2158 | 2.158,00 (persen seperti 23,96% tidak dianggap harga). */
    private const HARGA = '\d{1,3}(?:[.,]\d{3})+(?:,\d{1,2})?|\d{4,9}';

    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    /** true jika teks terakhir yang dibaca berasal dari OCR (PDF tanpa lapisan teks). */
    public bool $dipakaiOcr = false;

    /** Ambil seluruh teks dari file PDF; kalau PDF tidak punya teks (huruf dijadikan kurva/gambar) dipakai OCR. */
    public function teksDariFile(string $path): string
    {
        $this->dipakaiOcr = false;
        $teks = '';

        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $teks = (string) (new \Smalot\PdfParser\Parser())->parseFile($path)->getText();
            } catch (\Throwable $e) {
                $teks = '';
            }
        }

        if (mb_strlen(trim($teks)) >= 30) {
            return $teks;
        }

        // Tidak ada teks: PDF dari CorelDRAW dengan "text as curves", atau hasil scan -> OCR
        $teks = $this->ocr($path);
        $this->dipakaiOcr = true;

        return $teks;
    }

    /**
     * OCR: pdftoppm (Poppler) mengubah halaman jadi gambar, Tesseract membacanya.
     * Lokasi program bisa diatur di .env: TESSERACT_PATH, PDFTOPPM_PATH, OCR_LANG (default eng).
     */
    private function ocr(string $path): string
    {
        $tesseract = env('TESSERACT_PATH', 'tesseract');
        $pdftoppm  = env('PDFTOPPM_PATH', 'pdftoppm');
        $bahasa    = env('OCR_LANG', 'eng');

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ph_ocr_' . uniqid();
        if (! @mkdir($dir, 0700, true) && ! is_dir($dir)) {
            throw new \RuntimeException('Folder sementara untuk OCR tidak bisa dibuat.');
        }

        try {
            $this->jalankan([$pdftoppm, '-r', '250', '-gray', '-png', $path, $dir . DIRECTORY_SEPARATOR . 'hal'], 180,
                'Program pdftoppm (Poppler) tidak ditemukan atau gagal. Pasang Poppler lalu isi PDFTOPPM_PATH di .env.');

            $gambar = glob($dir . DIRECTORY_SEPARATOR . 'hal*.png') ?: [];
            natsort($gambar);
            if (! $gambar) {
                throw new \RuntimeException('PDF tidak menghasilkan gambar halaman untuk OCR.');
            }

            $teks = '';
            foreach ($gambar as $g) {
                $teks .= $this->jalankan([$tesseract, $g, 'stdout', '-l', $bahasa, '--psm', '6'], 120,
                    'Program Tesseract tidak ditemukan atau gagal. Pasang Tesseract OCR lalu isi TESSERACT_PATH di .env.') . "\n";
            }

            return $teks;
        } finally {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($dir);
        }
    }

    private function jalankan(array $perintah, int $batasDetik, string $pesanGagal): string
    {
        try {
            $proses = new \Symfony\Component\Process\Process($perintah);
            $proses->setTimeout($batasDetik);
            $proses->run();
        } catch (\Throwable $e) {
            throw new \RuntimeException($pesanGagal);
        }
        if (! $proses->isSuccessful()) {
            throw new \RuntimeException($pesanGagal);
        }

        return $proses->getOutput();
    }

    /** Baca tabel harga dari PDF. @return array{rows: array, peringatan: array} */
    public function bacaFile(string $path): array
    {
        $teks = $this->teksDariFile($path);
        $hasil = $this->bacaTeks($teks);
        if ($this->dipakaiOcr) {
            $hasil['peringatan'][] = 'PDF tidak berisi teks sehingga dibaca dengan OCR; cocokkan angkanya dengan PDF sebelum ACC.';
        }
        $hasil['teks_panjang'] = strlen(trim($teks));
        $hasil['teks_contoh']  = mb_substr(trim((string) preg_replace('/\s+/u', ' ', $teks)), 0, 400);

        return $hasil;
    }

    /**
     * Baca tabel harga dari teks hasil ekstrak PDF.
     *
     * @return array{rows: array<int, array{no:int,nama:string,harga_2024:int,harga_2026:int}>, peringatan: string[]}
     */
    public function bacaTeks(string $teks): array
    {
        // Buang footer halaman ("MITRA ... | Perbandingan Harga ... Halaman 2") dan rapikan persen hasil OCR ("23, 22%")
        $teks = preg_replace('/^[^\n|]*\|[^\n|]*Halaman\s*\d+\s*$/miu', ' ', $teks);
        $teks = preg_replace('/(\d)\s*,\s+(\d{1,2}\s*%)/u', '$1,$2', (string) $teks);
        $teks = preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', (string) $teks));
        $teks = trim((string) $teks);

        // Nomor 1 bisa muncul di judul/kop; coba tiap kemunculan dan pakai yang menghasilkan baris terbanyak.
        $terbaik = ['rows' => [], 'peringatan' => []];
        preg_match_all('/(?<![\d.,])1\s+(?=[A-Za-z])/u', $teks, $m, PREG_OFFSET_CAPTURE);

        foreach ($m[0] as [$cocok, $pos]) {
            $hasil = $this->bacaBerurutan($teks, $pos);
            $n = count($hasil['rows']);
            $nTerbaik = count($terbaik['rows']);
            // Lebih banyak baris menang; jika sama, pilih yang nama barisnya paling pendek (bukan kop surat yang ikut tertelan).
            if ($n > $nTerbaik
                || ($n > 0 && $n === $nTerbaik && strlen($hasil['rows'][0]['nama']) < strlen($terbaik['rows'][0]['nama']))) {
                $terbaik = $hasil;
            }
        }

        // Cara 2: PDF tanpa kolom nomor (atau nomor menempel ke nama) -> baca berdasarkan pasangan harga
        // Dibandingkan, bukan hanya cadangan: cara 1 bisa kebetulan menemukan beberapa "baris" palsu.
        $tanpaNomor = $this->bacaTanpaNomor($teks);
        if (count($tanpaNomor['rows']) > count($terbaik['rows'])) {
            $terbaik = $tanpaNomor;
        }

        return $terbaik;
    }

    /**
     * Cara 2: tiap baris = nama produk + dua harga atau lebih berurutan. Nama diambil dari teks di antara
     * baris sebelumnya dan pasangan harga ini; nomor diberikan otomatis 1, 2, 3, ...
     */
    private function bacaTanpaNomor(string $teks): array
    {
        $rows = [];
        $peringatan = [];
        $pola = '/(?:(?:Rp\.?\s*)?(?:' . self::HARGA . ')(?![\d.,%])\s*){2,}/u';

        if (! preg_match_all($pola, $teks, $m, PREG_OFFSET_CAPTURE)) {
            return ['rows' => [], 'peringatan' => []];
        }

        $akhirSebelum = 0;
        foreach ($m[0] as $i => [$run, $mulai]) {
            $segmen = substr($teks, $akhirSebelum, $mulai - $akhirSebelum);
            $akhirSebelum = $mulai + strlen($run);

            if ($i === 0) {
                // Baris pertama didahului judul/header: ambil teks setelah kata header terakhir
                if (preg_match_all('/(?:Nama\s+(?:Produk|Barang|Item)|Produk|Kenaikan|Selisih|Persentase|Keterangan|2026|%)/iu', $segmen, $h, PREG_OFFSET_CAPTURE)) {
                    $last = end($h[0]);
                    $segmen = substr($segmen, $last[1] + strlen($last[0]));
                }
            }

            $nama = $this->bersihkanNama($segmen);
            if ($nama === null) {
                $peringatan[] = 'Satu baris harga (' . trim($run) . ') tidak punya nama yang terbaca.';
                continue;
            }

            preg_match_all('/(?:' . self::HARGA . ')/u', $run, $h2);
            $angka = array_map([$this, 'keAngka'], $h2[0]);
            if (count($angka) < 2 || $angka[0] <= 0 || end($angka) <= 0) {
                continue;
            }

            $rows[] = [
                'no'         => count($rows) + 1,
                'nama'       => $nama,
                'harga_2024' => $angka[0],
                'harga_2026' => end($angka),
            ];
        }

        if ($rows) {
            $peringatan[] = 'PDF tidak punya kolom nomor yang terbaca; urutan nomor dibuat otomatis.';
        }

        return ['rows' => $rows, 'peringatan' => $peringatan];
    }

    /** Bersihkan potongan teks di depan pasangan harga: buang persen, nomor urut, "Rp"; kembalikan nama atau null. */
    private function bersihkanNama(string $segmen): ?string
    {
        $segmen = trim($segmen);
        do {
            $sebelum = $segmen;
            $segmen = preg_replace('/^[+\-]?\d{1,3}(?:[.,]\d{1,2})?\s*%\s*/u', '', $segmen);   // 23,96%
            $segmen = preg_replace('/^[+\-]?\d{1,3}[.,]\d{1,2}\s+(?=[A-Za-z])/u', '', $segmen); // 23,96 (tanpa %)
            $segmen = preg_replace('/^\d{1,3}[.)]?\s*(?=[A-Za-z])/u', '', $segmen);                // nomor urut
            $segmen = trim($segmen);
        } while ($segmen !== $sebelum);

        $segmen = trim((string) preg_replace('/\s*Rp\.?$/i', '', $segmen));

        if ($segmen === '' || ! preg_match('/^[A-Za-z]/', $segmen) || strlen($segmen) > 150) {
            return null;
        }
        if (preg_match('/\b(19|20)\d{2}\b/', $segmen)) {
            return null;
        }

        return $segmen;
    }

    /** Baca baris nomor 1, 2, 3, ... berturut-turut mulai dari posisi $mulai. */
    private function bacaBerurutan(string $teks, int $mulai): array
    {
        $rows = [];
        $peringatan = [];
        $pos = $mulai;
        $harapan = 1;
        $len = strlen($teks);

        while ($pos < $len) {
            $baris = null;

            // Cari nomor yang diharapkan; jika barisnya rusak, boleh melompat maksimal 2 nomor.
            for ($lompat = 0; $lompat <= 2 && $baris === null; $lompat++) {
                $no = $harapan + $lompat;
                $batas = $rows === [] ? 0 : 300; // nomor berikut harus dekat (boleh ada footer/header halaman di antaranya)
                $baris = $this->cariBaris($teks, $pos, $no, $batas);
                if ($baris !== null && $lompat > 0) {
                    $peringatan[] = 'Baris no ' . implode(', ', range($harapan, $no - 1)) . ' tidak terbaca.';
                }
            }

            if ($baris === null) {
                break;
            }

            $r = $baris['row'];
            if ($r['persen'] !== null && $r['harga_2024'] > 0) {
                $hitung = ($r['harga_2026'] - $r['harga_2024']) / $r['harga_2024'] * 100;
                if (abs($hitung - $r['persen']) > 0.06) {
                    $peringatan[] = sprintf('No %d (%s): persen di PDF %s%% tidak cocok dengan hitungan %s%%, periksa angkanya.',
                        $r['no'], $r['nama'], number_format($r['persen'], 2, ',', '.'), number_format($hitung, 2, ',', '.'));
                }
            }
            $rows[] = $r;
            $pos = $baris['akhir'];
            $harapan = $baris['row']['no'] + 1;
        }

        return ['rows' => $rows, 'peringatan' => $peringatan];
    }

    /** Cari satu baris dengan nomor $no mulai dari $pos; $batas = jarak maksimal (karakter) dari $pos, 0 = bebas. */
    private function cariBaris(string $teks, int $pos, int $no, int $batas): ?array
    {
        $pola = '/(?<![\d.,])' . $no . '\s+(?<nama>[A-Za-z][^%]{0,120}?)\s+(?<harga>(?:(?:Rp\.?\s*)?(?:' . self::HARGA . ')(?![\d.,%])\s*){2,})/u';

        if (! preg_match($pola, $teks, $c, PREG_OFFSET_CAPTURE, $pos)) {
            return null;
        }
        if ($batas > 0 && $c[0][1] - $pos > $batas) {
            return null;
        }

        preg_match_all('/(?:' . self::HARGA . ')/u', $c['harga'][0], $h);
        $angka = array_map([$this, 'keAngka'], $h[0]);
        if (count($angka) < 2 || $angka[0] <= 0 || end($angka) <= 0) {
            return null;
        }

        // Persentase kenaikan yang tercetak setelah harga (dipakai untuk mengecek angka hasil OCR)
        $persen = null;
        $akhirRun = $c[0][1] + strlen($c[0][0]);
        if (preg_match('/^\s*([+\-]?\d{1,3})\s*[.,]\s*(\d{1,2})\s*%/u', substr($teks, $akhirRun, 30), $pm)) {
            $persen = (float) ($pm[1] . '.' . $pm[2]);
        }

        $nama = trim(preg_replace('/\s*Rp\.?$/i', '', $c['nama'][0]));
        // Nama produk tidak mengandung tahun; kalau ada, itu kop/judul yang ikut tertelan.
        if (preg_match('/\b(19|20)\d{2}\b/', $nama)) {
            return null;
        }

        return [
            'row' => [
                'no'         => $no,
                'nama'       => $nama,
                'harga_2024' => $angka[0],
                'harga_2026' => end($angka),
                'persen'     => $persen,
            ],
            'akhir' => $akhirRun,
        ];
    }

    private function keAngka(string $s): int
    {
        $s = preg_replace('/,\d{1,2}$/', '', $s);   // buang desimal ",00"

        return (int) preg_replace('/[^\d]/', '', $s);
    }

    // ------------------------------------------------------------------
    // Excel / CSV
    // ------------------------------------------------------------------

    /**
     * Baca file Excel (xls, xlsx) atau CSV. Setiap sheet dibaca; sheet/judul bagian dipakai untuk menebak kelompok.
     * Butuh PhpSpreadsheet (sudah ikut paket maatwebsite/excel).
     *
     * @return array{rows: array, peringatan: string[]}
     */
    public function bacaExcel(string $path, string $ekstensi = ''): array
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException('Pembaca Excel belum terpasang (paket maatwebsite/excel / phpoffice/phpspreadsheet).');
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            if ($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Csv) {
                $reader->setDelimiter($this->tebakPemisahCsv($path));
            }
            $reader->setReadDataOnly(true);
            $buku = $reader->load($path);

            $sheets = [];
            foreach ($buku->getWorksheetIterator() as $ws) {
                $sheets[$ws->getTitle()] = $ws->toArray(null, true, false, false);
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('File Excel/CSV tidak bisa dibaca (' . $e->getMessage() . ').');
        }

        return $this->bacaTabel($sheets);
    }

    private function tebakPemisahCsv(string $path): string
    {
        $contoh = (string) file_get_contents($path, false, null, 0, 4000);
        $hitung = [';' => substr_count($contoh, ';'), ',' => substr_count($contoh, ','), "\t" => substr_count($contoh, "\t")];
        arsort($hitung);

        return (string) array_key_first($hitung);
    }

    /**
     * Baca tabel dari sel-sel mentah.
     * Kolom dikenali dari baris judul ("Harga 2024", "Harga 2026"); jika tidak ada, dua angka harga pertama
     * di tiap baris dianggap Harga 2024 dan Harga 2026. Persen (mis. 23,96% atau 0,2396) diabaikan.
     *
     * @param array<string, array<int, array<int, mixed>>> $sheets  nama sheet => baris => sel
     * @return array{rows: array<int, array{no:int,nama:string,harga_2024:int,harga_2026:int,sheet:string,bagian:string}>, peringatan: string[]}
     */
    public function bacaTabel(array $sheets): array
    {
        $rows = [];
        $peringatan = [];

        foreach ($sheets as $namaSheet => $baris) {
            $idx24 = $idx26 = null;
            $bagian = '';
            $nomorOtomatis = 0;

            foreach ($baris as $cells) {
                $cells = array_values((array) $cells);

                // Baris judul kolom: ada sel berisi 2024 dan sel berisi 2026
                if ($idx24 === null) {
                    $a = $b = null;
                    foreach ($cells as $i => $c) {
                        $t = strtolower(trim((string) $c));
                        if ($a === null && preg_match('/2024/', $t) && ! str_contains($t, 'aplikasi')) {
                            $a = $i;
                        } elseif ($b === null && preg_match('/2026/', $t)) {
                            $b = $i;
                        }
                    }
                    if ($a !== null && $b !== null && ! $this->adaHargaAngka($cells)) {
                        $idx24 = $a;
                        $idx26 = $b;
                        continue;
                    }
                }

                // Nama = sel teks pertama yang bukan angka/persen/judul kolom
                $iNama = null;
                foreach ($cells as $i => $c) {
                    if (is_string($c) && $this->bisaJadiNama($c)) {
                        $iNama = $i;
                        break;
                    }
                }

                // Harga
                $h24 = $h26 = null;
                if ($iNama !== null && $idx24 !== null && $idx26 !== null) {
                    $h24 = $this->selKeHarga($cells[$idx24] ?? null);
                    $h26 = $this->selKeHarga($cells[$idx26] ?? null);
                }
                if ($iNama !== null && ($h24 === null || $h26 === null)) {
                    $harga = [];
                    foreach ($cells as $i => $c) {
                        if ($i > $iNama && ($v = $this->selKeHarga($c)) !== null) {
                            $harga[] = $v;
                        }
                    }
                    if (count($harga) >= 2) {
                        [$h24, $h26] = [$harga[0], $harga[1]];
                    }
                }

                if ($iNama === null || $h24 === null || $h26 === null) {
                    // Baris judul bagian (mis. "SERAGAM, TAS & KPK"): catat untuk menebak kelompok
                    $teks = trim(implode(' ', array_filter(array_map(fn ($c) => is_string($c) ? trim($c) : '', $cells))));
                    if ($teks !== '' && preg_match('/modul|raport|seragam|\bkpk\b|\btas\b/i', $teks)) {
                        $bagian = $teks;
                    }
                    continue;
                }

                // Nomor: angka bulat kecil sebelum nama, kalau tidak ada nomor otomatis
                $no = null;
                for ($i = 0; $i < $iNama; $i++) {
                    $c = $cells[$i] ?? null;
                    if ((is_int($c) || is_float($c) || (is_string($c) && ctype_digit(trim($c)))) && (int) $c > 0 && (int) $c <= 999) {
                        $no = (int) $c;
                        break;
                    }
                }
                $nomorOtomatis++;

                $rows[] = [
                    'no'         => $no ?? $nomorOtomatis,
                    'nama'       => trim((string) $cells[$iNama]),
                    'harga_2024' => $h24,
                    'harga_2026' => $h26,
                    'sheet'      => (string) $namaSheet,
                    'bagian'     => $bagian,
                ];
            }
        }

        if (! $rows) {
            return ['rows' => [], 'peringatan' => []];
        }

        return ['rows' => $rows, 'peringatan' => $peringatan];
    }

    private function adaHargaAngka(array $cells): bool
    {
        foreach ($cells as $c) {
            if ($this->selKeHarga($c) !== null && ! in_array((int) $c, [2024, 2026], true)) {
                return true;
            }
        }

        return false;
    }

    /** Sel -> harga rupiah bulat (>= 500), atau null bila bukan harga (kosong, teks, persen). */
    private function selKeHarga($c): ?int
    {
        if (is_int($c) || is_float($c)) {
            // 0,2396 / 23,96 dsb. adalah persen, bukan harga
            return $c >= 500 ? (int) round($c) : null;
        }
        if (! is_string($c)) {
            return null;
        }
        $t = trim($c);
        if ($t === '' || str_contains($t, '%')) {
            return null;
        }
        if (! preg_match('/^(?:Rp\.?\s*)?(' . self::HARGA . ')$/iu', $t, $m)) {
            return null;
        }
        $v = $this->keAngka($m[1]);

        return $v >= 500 ? $v : null;
    }

    private function bisaJadiNama(string $s): bool
    {
        $s = trim($s);
        if (strlen($s) < 2 || ! preg_match('/[A-Za-z]/', $s)) {
            return false;
        }
        if (preg_match('/^(?:Rp\.?\s*)?[\d.,\s%+\-]+$/iu', $s)) {
            return false;
        }
        // judul kolom
        if (preg_match('/^(no\.?|nama(\s+produk)?(\s*\/\s*modul)?|produk|harga.*|kenaikan.*|selisih|persen.*|keterangan|satuan)$/i', $s)) {
            return false;
        }

        return true;
    }

    /** Nomor & tanggal surat dari teks surat permohonan (hasil terbaik, boleh kosong). */
    public function infoSurat(string $teks): array
    {
        $teks = preg_replace('/\s+/u', ' ', $teks);
        $nomor = null;
        $tanggal = null;

        if (preg_match('/\b(?:Nomor|No\.?)\s*[:.]?\s*([A-Za-z0-9][A-Za-z0-9.\-]*(?:\/[A-Za-z0-9.\-]+)+)/i', $teks, $m)) {
            $nomor = $m[1];
            // Bulan romawi hasil OCR sering salah ("X1" untuk "XI")
            $bagian = explode('/', $nomor);
            if (count($bagian) >= 3 && preg_match('/^[XIV1l|]+$/', $bagian[count($bagian) - 2])) {
                $bagian[count($bagian) - 2] = strtr($bagian[count($bagian) - 2], ['1' => 'I', 'l' => 'I', '|' => 'I']);
                $nomor = implode('/', $bagian);
            }
        }

        $bulan = implode('|', array_keys(self::BULAN));
        if (preg_match('/\b(\d{1,2})\s+(' . $bulan . ')\s+(\d{4})\b/i', $teks, $m)) {
            $tanggal = sprintf('%04d-%02d-%02d', $m[3], self::BULAN[strtolower($m[2])], $m[1]);
        }

        return ['nomor_surat' => $nomor, 'tanggal_surat' => $tanggal];
    }
}
