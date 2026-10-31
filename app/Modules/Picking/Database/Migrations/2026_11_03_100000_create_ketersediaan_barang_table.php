<?php

use App\Modules\Picking\Services\KetersediaanBarangService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ketersediaan_barang', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20);                       // Majalah | Kalender
            $table->string('kode', 20);                        // M159 | 2027
            $table->string('status', 20)->default('belum_datang'); // belum_datang | datang
            $table->date('tgl_datang')->nullable();
            $table->unsignedBigInteger('diubah_oleh')->nullable();
            $table->timestamps();

            $table->unique(['jenis', 'kode']);
        });

        $this->isiDariPickingLama();
    }

    /**
     * Edisi / tahun yang SUDAH ada di data Picking/Persiapan lama dianggap sudah datang,
     * supaya pesanan lama tidak mendadak tertahan. Edisi baru setelah migration = belum datang.
     */
    private function isiDariPickingLama(): void
    {
        // [tabel induk, tabel item, kolom fk item, kolom teks induk]
        $sumber = [
            ['pickings',                  'picking_items',        'picking_id',        ['pesanan']],
            ['picking_pasif',             'picking_pasif_item',   'picking_pasif_id',  ['pesanan']],
            ['manual_pickings',           'manual_picking_items', 'manual_picking_id', ['pesanan']],
            ['realisasi_logistik_aktif',  null,                   null,                ['nama_barang']],
            ['realisasi_logistik_pasif',  null,                   null,                ['nama_barang']],
        ];

        $ketemu = []; // "Jenis|kode" => true

        foreach ($sumber as [$induk, $itemTbl, $fk, $kolom]) {
            if (! Schema::hasTable($induk) || ! Schema::hasColumn($induk, 'kategori_order')) {
                continue;
            }
            $adaItem = $itemTbl && Schema::hasTable($itemTbl);

            DB::table($induk)->orderBy('id')->chunk(500, function ($rows) use (&$ketemu, $itemTbl, $fk, $kolom, $adaItem) {
                $items = $adaItem
                    ? DB::table($itemTbl)->whereIn($fk, $rows->pluck('id'))->get([$fk, 'item_sku', 'item_name'])->groupBy($fk)
                    : collect();

                foreach ($rows as $r) {
                    $kelompok = [];
                    foreach ($items->get($r->id, collect()) as $it) {
                        $kelompok[] = [(string) $it->item_sku, (string) $it->item_name];
                    }
                    foreach ($kolom as $c) {
                        if (isset($r->{$c}) && $r->{$c} !== '') {
                            $kelompok[] = ['', (string) $r->{$c}];
                        }
                    }
                    foreach (KetersediaanBarangService::kunciDariTeks($r->kategori_order, $kelompok) as $k) {
                        $ketemu[$k['jenis'] . '|' . $k['kode']] = true;
                    }
                }
            });
        }

        $now = now();
        foreach (array_keys($ketemu) as $k) {
            [$jenis, $kode] = explode('|', $k, 2);
            DB::table('ketersediaan_barang')->insertOrIgnore([
                'jenis'      => $jenis,
                'kode'       => $kode,
                'status'     => 'datang',
                'tgl_datang' => null, // tanggal tidak diketahui (data lama)
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ketersediaan_barang');
    }
};
