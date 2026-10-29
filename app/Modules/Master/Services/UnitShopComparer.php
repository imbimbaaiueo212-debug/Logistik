<?php

namespace App\Modules\Master\Services;

use App\Modules\Master\Models\UnitKemitraan;

/**
 * Membandingkan data MITRA (tabel unit_kemitraan) dengan akun biMBA Shop
 * (tabel user_export_bimba_shop) untuk satu unit.
 *
 * Kunci pencocokan akun = No Cab (billing_last_name), nol di depan diabaikan.
 * Setelah akun ketemu, dibandingkan: nama, email, no HP, alamat.
 * Bila akun tidak ketemu, dicari petunjuk lewat email / no HP / nama mitra.
 *
 * Hasil compare():
 *   status     : 'sesuai' | 'beda' | 'tanpa_akun'
 *   akun       : model akun biMBA Shop yang dipakai (atau null)
 *   mitra/shop : nilai yang ditampilkan di tabel (nama, email, hp, alamat)
 *   cek        : per kolom ['s' => ok|mirip|beda|kosong1|kosong2, 'ket' => teks]
 *   masalah    : daftar alasan kenapa tidak sama
 *   petunjuk   : akun shop yang mungkin milik mitra ini (saat tanpa_akun)
 *   info       : catatan tambahan (mis. ada akun ganda)
 *   pertanyaan : teks siap salin untuk team admin
 */
class UnitShopComparer
{
    /** Kolom unit_kemitraan yang dipakai pengecekan (untuk select ringan). */
    public const KOLOM_UNIT = [
        'id_record', 'no_cab', 'bimba_aiueo_unit', 'no_induk_mitra', 'nama_mitra',
        'email', 'email_mitra', 'no_hp', 'no_hp_mitra', 'alamat_mitra',
        'no_rumah', 'rt_mitra', 'rw_mitra', 'kel_mitra', 'kec_mitra',
        'kota_mitra', 'provinsi_mitra', 'kode_pos_mitra',
    ];

    private const ALAMAT_STOP = [
        'jl', 'jalan', 'no', 'nomor', 'rt', 'rw', 'kel', 'kelurahan', 'desa', 'kec', 'kecamatan',
        'kota', 'kab', 'kabupaten', 'prov', 'provinsi', 'indonesia', 'blok', 'perum',
        'perumahan', 'komplek', 'kompleks', 'gg', 'gang', 'dan',
    ];

    private array $byCab = [];
    private array $byEmail = [];
    private array $byPhone = [];
    private array $byName = [];

    public function __construct(iterable $users)
    {
        foreach ($users as $u) {
            $cab = self::cabKey($u->billing_last_name);
            if ($cab !== '') {
                $this->byCab[$cab][] = $u;
            }
            foreach (self::emails($u->user_email, $u->billing_email) as $e) {
                $this->byEmail[$e][] = $u;
            }
            foreach (self::phones($u->billing_phone) as $p) {
                $this->byPhone[$p][] = $u;
            }
            foreach ($this->shopNameKeys($u) as $n) {
                if (strlen($n) >= 5) {
                    $this->byName[$n][] = $u;
                }
            }
        }
    }

    // =====================================================================
    //  Utama
    // =====================================================================

    public function compare(UnitKemitraan $unit): array
    {
        $noCab  = trim((string) $unit->no_cab);
        $cabKey = self::cabKey($noCab);

        $mitra = [
            'nama'   => trim((string) $unit->nama_mitra),
            'unit'   => trim((string) $unit->bimba_aiueo_unit),
            'nim'    => trim((string) $unit->no_induk_mitra),
            'email'  => trim((string) ($unit->email_mitra ?: $unit->email)),
            'hp'     => trim((string) ($unit->no_hp_mitra ?: $unit->no_hp)),
            'alamat' => $this->alamatMitra($unit),
        ];
        $mitraEmails = self::emails($unit->email_mitra, $unit->email);
        $mitraPhones = self::phones($unit->no_hp_mitra, $unit->no_hp);

        $cands = $cabKey !== '' ? ($this->byCab[$cabKey] ?? []) : [];
        $akun  = $cands ? $this->akunTerbaik($cands, $mitra, $mitraEmails, $mitraPhones) : null;

        $hasil = [
            'akun'       => $akun,
            'mitra'      => $mitra,
            'shop'       => ['nama' => '', 'email' => '', 'hp' => '', 'alamat' => ''],
            'status'     => 'sesuai',
            'cek'        => [],
            'masalah'    => [],
            'petunjuk'   => [],
            'info'       => [],
            'ringkas'    => '',
            'pertanyaan' => '',
        ];

        $label = $mitra['unit'] !== '' ? $mitra['unit'] : 'unit tanpa nama';
        $ident = 'Unit ' . $label . ' (No Cab ' . ($noCab !== '' ? $noCab : 'kosong')
               . ($mitra['nama'] !== '' ? ', Mitra ' . $mitra['nama'] : '') . ')';

        // ---------------- Akun tidak ditemukan ----------------
        if (! $akun) {
            $hasil['status'] = 'tanpa_akun';
            $this->isiTanpaAkun($hasil, $noCab, $ident, $mitra, $mitraEmails, $mitraPhones);
            return $hasil;
        }

        // ---------------- Akun ditemukan: bandingkan ----------------
        $hasil['shop'] = [
            'nama'   => trim((string) ($akun->first_name ?: $akun->display_name)),
            'email'  => trim((string) ($akun->user_email ?: $akun->billing_email)),
            'hp'     => trim((string) $akun->billing_phone),
            'alamat' => $this->alamatShop($akun),
        ];

        if (count($cands) > 1) {
            $hasil['info'][] = 'Ada ' . count($cands) . ' akun biMBA Shop dengan No Cab sama; yang ditampilkan paling cocok dengan data mitra.';
        }

        $cek = [
            'nama'   => $this->cekNama($mitra, $akun, $hasil['shop']['nama']),
            'email'  => $this->cekEmail($mitraEmails, $mitra['email'], $akun, $hasil['shop']['email']),
            'hp'     => $this->cekHp($mitraPhones, $mitra['hp'], $akun, $hasil['shop']['hp']),
            'alamat' => $this->cekAlamat($mitra['alamat'], $hasil['shop']['alamat']),
        ];
        $hasil['cek'] = $cek;

        $judul = ['nama' => 'Nama', 'email' => 'Email', 'hp' => 'No HP', 'alamat' => 'Alamat'];
        $cocok = [];
        foreach ($cek as $k => $c) {
            if (in_array($c['s'], ['beda', 'kosong1'], true)) {
                $hasil['masalah'][] = $judul[$k] . ': ' . $c['ket'];
            } elseif (in_array($c['s'], ['ok', 'mirip'], true)) {
                $cocok[] = $judul[$k];
            }
        }
        $hasil['ringkas'] = $cocok ? implode(', ', $cocok) . ' cocok' : '';

        if ($hasil['masalah']) {
            $hasil['status'] = 'beda';
            $baris = array_map(fn ($m) => '- ' . $m, $hasil['masalah']);
            $hasil['pertanyaan'] = "{$ident}: akun biMBA Shop ditemukan lewat No Cab, tetapi datanya tidak sama dengan data mitra.\n"
                . implode("\n", $baris)
                . "\nMohon dicek mana yang benar dan diperbarui.";
        }

        return $hasil;
    }

    // =====================================================================
    //  Akun tidak ditemukan
    // =====================================================================

    private function isiTanpaAkun(array &$h, string $noCab, string $ident, array $mitra, array $emails, array $phones): void
    {
        if ($noCab === '' || self::cabKey($noCab) === '') {
            $h['masalah'][] = 'No Cab kosong di data kemitraan'
                . ($mitra['nama'] !== '' ? ', padahal nama mitra ' . $mitra['nama'] . ' tercatat' : '');
            $h['pertanyaan'] = "{$ident} belum punya No Cab di data kemitraan"
                . ($mitra['nama'] !== '' ? ", padahal nama mitra {$mitra['nama']} tercatat" : '')
                . ". Mohon dicek No Cab yang benar dan apakah akun biMBA Shop-nya sudah ada.";
            return;
        }

        $h['masalah'][] = "No Cab {$noCab} tidak ada di biMBA Shop"
            . ($mitra['nama'] !== '' ? ', padahal nama mitra ' . $mitra['nama'] . ' tercatat' : '');

        // Cari akun shop yang mungkin milik mitra ini lewat email / HP / nama.
        $kandidat = [];
        $tambah = function ($u, string $alasan) use (&$kandidat) {
            $kandidat[$u->ID]['u'] = $u;
            $kandidat[$u->ID]['alasan'][$alasan] = $alasan;
        };
        foreach ($emails as $e) {
            foreach ($this->byEmail[$e] ?? [] as $u) {
                $tambah($u, 'email');
            }
        }
        foreach ($phones as $p) {
            foreach ($this->byPhone[$p] ?? [] as $u) {
                $tambah($u, 'no HP');
            }
        }
        $namaKey = self::nameKey($mitra['nama']);
        if (strlen($namaKey) >= 5) {
            foreach ($this->byName[$namaKey] ?? [] as $u) {
                $tambah($u, 'nama');
            }
        }

        uasort($kandidat, fn ($a, $b) => count($b['alasan']) <=> count($a['alasan']));
        $kandidat = array_slice($kandidat, 0, 3, true);

        $teksHint = [];
        foreach ($kandidat as $k) {
            $u    = $k['u'];
            $cab  = trim((string) $u->billing_last_name);
            $nama = trim((string) ($u->first_name ?: $u->display_name));
            $teksHint[] = 'Akun shop #' . $u->ID . ($nama !== '' ? " ({$nama})" : '')
                . ', No Cab di shop: ' . ($cab !== '' ? $cab : 'kosong')
                . ' - ' . implode(' & ', $k['alasan']) . ' sama dengan mitra';
        }
        $h['petunjuk'] = $teksHint;

        $kalimat = "{$ident}: No Cab {$noCab} tidak ditemukan di biMBA Shop"
            . ($mitra['nama'] !== '' ? ", padahal nama mitra {$mitra['nama']} tercatat di data kemitraan." : '.');
        if ($teksHint) {
            $kalimat .= "\nAda akun biMBA Shop yang memakai data sama dengan mitra, tetapi No Cab-nya tidak cocok:\n- "
                . implode("\n- ", $teksHint)
                . "\nMohon dipastikan No Cab mana yang benar (salah ketik di kemitraan atau di biMBA Shop).";
        } else {
            $kalimat .= "\nTidak ada akun biMBA Shop dengan email, no HP, atau nama yang sama."
                . "\nMohon dicek: apakah akun mitra belum didaftarkan di biMBA Shop, atau No Cab-nya terisi berbeda?";
        }
        $h['pertanyaan'] = $kalimat;
    }

    // =====================================================================
    //  Pengecekan per kolom
    // =====================================================================

    private function cekNama(array $mitra, $akun, string $namaShop): array
    {
        $shopKeys = array_filter($this->shopNameKeys($akun));
        $kMitra   = self::nameKey($mitra['nama']);
        $kUnit    = self::nameKey($mitra['unit']);

        if (! $shopKeys && ($kMitra === '' && $kUnit === '')) {
            return ['s' => 'kosong2', 'ket' => 'kosong di mitra dan di biMBA Shop'];
        }
        if (! $shopKeys) {
            return ['s' => 'kosong1', 'ket' => 'nama kosong di biMBA Shop (mitra: ' . $mitra['nama'] . ')'];
        }
        if ($kMitra === '' && $kUnit === '') {
            return ['s' => 'kosong1', 'ket' => 'nama mitra dan nama unit kosong di data kemitraan (shop: ' . $namaShop . ')'];
        }
        foreach ($shopKeys as $sk) {
            if (self::nameMatch($sk, $kMitra)) {
                return ['s' => 'ok', 'ket' => 'cocok dengan nama mitra'];
            }
        }
        foreach ($shopKeys as $sk) {
            if (self::nameMatch($sk, $kUnit)) {
                return ['s' => 'ok', 'ket' => 'cocok dengan nama unit'];
            }
        }

        return ['s' => 'beda', 'ket' => "di biMBA Shop \"{$namaShop}\", di kemitraan mitra \"{$mitra['nama']}\" / unit \"{$mitra['unit']}\""];
    }

    private function cekEmail(array $mitraEmails, string $tampilMitra, $akun, string $tampilShop): array
    {
        $shop = self::emails($akun->user_email, $akun->billing_email);

        if (! $mitraEmails && ! $shop) {
            return ['s' => 'kosong2', 'ket' => 'kosong di mitra dan di biMBA Shop'];
        }
        if (! $mitraEmails) {
            return ['s' => 'kosong1', 'ket' => "kosong di data mitra (shop: {$tampilShop})"];
        }
        if (! $shop) {
            return ['s' => 'kosong1', 'ket' => "kosong di biMBA Shop (mitra: {$tampilMitra})"];
        }
        if (array_intersect($mitraEmails, $shop)) {
            return ['s' => 'ok', 'ket' => 'sama'];
        }

        return ['s' => 'beda', 'ket' => "mitra {$tampilMitra}, biMBA Shop {$tampilShop}"];
    }

    private function cekHp(array $mitraPhones, string $tampilMitra, $akun, string $tampilShop): array
    {
        $shop = self::phones($akun->billing_phone);

        if (! $mitraPhones && ! $shop) {
            return ['s' => 'kosong2', 'ket' => 'kosong di mitra dan di biMBA Shop'];
        }
        if (! $mitraPhones) {
            return ['s' => 'kosong1', 'ket' => "kosong di data mitra (shop: {$tampilShop})"];
        }
        if (! $shop) {
            return ['s' => 'kosong1', 'ket' => "kosong di biMBA Shop (mitra: {$tampilMitra})"];
        }
        foreach ($mitraPhones as $a) {
            foreach ($shop as $b) {
                if ($a === $b || (min(strlen($a), strlen($b)) >= 9 && (str_ends_with($a, $b) || str_ends_with($b, $a)))) {
                    return ['s' => 'ok', 'ket' => 'sama'];
                }
            }
        }

        return ['s' => 'beda', 'ket' => "mitra {$tampilMitra}, biMBA Shop {$tampilShop}"];
    }

    private function cekAlamat(string $mitra, string $shop): array
    {
        $a = self::alamatTokens($mitra);
        $b = self::alamatTokens($shop);

        if (! $a && ! $b) {
            return ['s' => 'kosong2', 'ket' => 'kosong di mitra dan di biMBA Shop'];
        }
        if (! $a) {
            return ['s' => 'kosong1', 'ket' => 'kosong di data mitra'];
        }
        if (! $b) {
            return ['s' => 'kosong1', 'ket' => 'kosong di biMBA Shop'];
        }

        // Alamat ditulis bebas, jadi dibandingkan lewat kata yang sama (bukan harus persis).
        $sama  = count(array_intersect($a, $b));
        $skor  = $sama / min(count($a), count($b));

        if ($skor >= 0.5) {
            return ['s' => 'ok', 'ket' => 'cocok'];
        }
        if ($skor >= 0.25) {
            return ['s' => 'mirip', 'ket' => 'mirip, penulisan berbeda'];
        }

        return ['s' => 'beda', 'ket' => 'alamat di kemitraan dan di biMBA Shop jauh berbeda'];
    }

    // =====================================================================
    //  Pemilihan akun & alamat
    // =====================================================================

    private function akunTerbaik(array $cands, array $mitra, array $mitraEmails, array $mitraPhones)
    {
        if (count($cands) === 1) {
            return $cands[0];
        }

        $terbaik = $cands[0];
        $skorMax = -1;
        foreach ($cands as $u) {
            $skor = 0;
            if (array_intersect($mitraEmails, self::emails($u->user_email, $u->billing_email))) {
                $skor += 2;
            }
            if (array_intersect($mitraPhones, self::phones($u->billing_phone))) {
                $skor += 2;
            }
            $kMitra = self::nameKey($mitra['nama']);
            foreach ($this->shopNameKeys($u) as $sk) {
                if (self::nameMatch($sk, $kMitra) || self::nameMatch($sk, self::nameKey($mitra['unit']))) {
                    $skor += 1;
                    break;
                }
            }
            if ($skor > $skorMax) {
                $skorMax = $skor;
                $terbaik = $u;
            }
        }

        return $terbaik;
    }

    private function alamatMitra(UnitKemitraan $unit): string
    {
        $alamat = trim((string) $unit->alamat_mitra);
        if ($alamat !== '') {
            return $alamat;
        }

        return trim(collect([
            $unit->no_rumah,
            $unit->rt_mitra ? 'RT ' . $unit->rt_mitra : null,
            $unit->rw_mitra ? 'RW ' . $unit->rw_mitra : null,
            $unit->kel_mitra, $unit->kec_mitra, $unit->kota_mitra,
            $unit->provinsi_mitra, $unit->kode_pos_mitra,
        ])->filter(fn ($x) => trim((string) $x) !== '')->implode(', '));
    }

    private function alamatShop($u): string
    {
        return trim(collect([
            $u->billing_address_1, $u->billing_address_2,
            $u->billing_city, $u->billing_state, $u->billing_postcode,
        ])->filter(fn ($x) => trim((string) $x) !== '')->implode(', '));
    }

    // =====================================================================
    //  Normalisasi
    // =====================================================================

    /** No Cab tanpa nol di depan. */
    public static function cabKey($v): string
    {
        return ltrim(trim((string) $v), '0');
    }

    private static function emails(...$raws): array
    {
        $out = [];
        foreach ($raws as $raw) {
            foreach (preg_split('/[\s,;]+/', mb_strtolower(trim((string) $raw))) as $e) {
                if ($e !== '' && str_contains($e, '@')) {
                    $out[$e] = $e;
                }
            }
        }

        return array_values($out);
    }

    /** No HP dibuat seragam: hanya angka, tanpa 0 / 62 di depan. */
    private static function phones(...$raws): array
    {
        $out = [];
        foreach ($raws as $raw) {
            foreach (preg_split('/[\/,;|]|\bdan\b/i', (string) $raw) as $bagian) {
                $d = preg_replace('/\D+/', '', $bagian);
                if ($d === '') {
                    continue;
                }
                if (str_starts_with($d, '62')) {
                    $d = substr($d, 2);
                }
                $d = ltrim($d, '0');
                if (strlen($d) >= 6) {
                    $out[$d] = $d;
                }
            }
        }

        return array_values($out);
    }

    private static function nameKey($s): string
    {
        $s = mb_strtolower((string) $s);
        $s = preg_replace('/bimba[\s\-]*aiueo|bimba|aiueo/u', ' ', $s);

        return preg_replace('/[^a-z0-9]+/u', '', $s);
    }

    private function shopNameKeys($u): array
    {
        $first = trim((string) $u->first_name);
        $last  = trim((string) $u->last_name);

        $keys = [
            self::nameKey($u->first_name),
            self::nameKey($u->last_name),
            self::nameKey($u->display_name),
            self::nameKey($u->billing_first_name ?? ''),
            self::nameKey($first . ' ' . $last),
        ];

        return array_values(array_unique(array_filter($keys)));
    }

    private static function nameMatch(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }
        if ($a === $b) {
            return true;
        }
        if (min(strlen($a), strlen($b)) >= 5 && (str_contains($a, $b) || str_contains($b, $a))) {
            return true;
        }
        similar_text($a, $b, $persen);

        return $persen >= 85;
    }

    private static function alamatTokens(string $s): array
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/u', ' ', $s);
        $out = [];
        foreach (explode(' ', $s) as $t) {
            if (strlen($t) < 2 || in_array($t, self::ALAMAT_STOP, true)) {
                continue;
            }
            $out[$t] = $t;
        }

        return array_values($out);
    }
}
