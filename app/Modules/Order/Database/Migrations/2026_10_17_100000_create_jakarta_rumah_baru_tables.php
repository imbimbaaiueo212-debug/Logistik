<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database sendiri untuk rumah DLC, EBT, INV, BSS (sebelumnya semua numpang di jakarta_pasif).
 *
 * Per rumah dibuat 3 tabel, strukturnya sama dengan versi pasif:
 *   jakarta_{rumah}, jakarta_{rumah}_items, realisasi_{rumah}
 * Bedanya kolom penghubung bernama jakarta_id (bukan jakarta_pasif_id).
 *
 * Data lama: order DLC/EBT/INV/BSS di jakarta_pasif yang BELUM diproses dipindah ke tabel barunya.
 * Order yang sudah diproses (sudah punya Rekap Aktual / Picking) sengaja TIDAK dipindah:
 * picking_pasif punya foreign key ke realisasi_pasif, jadi memindahkannya bisa merusak data.
 */
return new class extends Migration
{
    private array $rumah = ['dlc', 'ebt', 'inv', 'bss'];

    public function up(): void
    {
        foreach ($this->rumah as $r) {
            $this->buatTabel($r);
            $this->pindahDataLama($r);
        }
    }

    public function down(): void
    {
        foreach ($this->rumah as $r) {
            Schema::dropIfExists("realisasi_{$r}");
            Schema::dropIfExists("jakarta_{$r}_items");
            Schema::dropIfExists("jakarta_{$r}");
        }
    }

    private function buatTabel(string $r): void
    {
        if (! Schema::hasTable("jakarta_{$r}")) {
            Schema::create("jakarta_{$r}", function (Blueprint $table) use ($r) {
                $table->id();
                $table->date('tgl_input')->nullable();
                $table->dateTime('tgl_pesan')->nullable();
                $table->string('kirim')->nullable();
                $table->string('no_telpon')->nullable();
                $table->text('alamat_kirim')->nullable();
                $table->string('kab_kota_provinsi')->nullable();
                $table->integer('ongkir')->default(0);
                $table->string('nama_unit')->nullable();
                $table->text('pesanan')->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('berat', 10, 2)->default(0);
                $table->integer('item_qty')->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->string('jenis_bank')->nullable();
                $table->string('status_pembayaran')->nullable();
                $table->string('status_pesan')->nullable();
                $table->string('id_pesan')->nullable()->index();
                $table->string('status')->default('aktif');
                $table->dateTime('payment_date')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('billing_last_name')->nullable();
                $table->string('billing_company')->nullable();
                $table->string('status_kirim')->nullable();
                $table->string('ekspedisi')->nullable();
                $table->string('service_pengiriman')->nullable();
                $table->dateTime('estimasi_print_pl')->nullable();
                $table->dateTime('estimasi_persiapan')->nullable();
                $table->boolean('is_processed')->default(false);
                $table->dateTime('processed_at')->nullable();
                $table->string('validasi')->nullable();
                $table->text('catatan')->nullable();
                $table->string('grup')->nullable();
                $table->string('jenis', 10)->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("jakarta_{$r}_items")) {
            Schema::create("jakarta_{$r}_items", function (Blueprint $table) use ($r) {
                $table->id();
                $table->foreignId('jakarta_id')->constrained("jakarta_{$r}")->onDelete('cascade');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('sku')->nullable();
                $table->string('label')->nullable();
                $table->string('nama_produk')->nullable();
                $table->integer('qty')->default(1);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("realisasi_{$r}")) {
            Schema::create("realisasi_{$r}", function (Blueprint $table) use ($r) {
                $table->id();
                $table->unsignedBigInteger('jakarta_id')->nullable()->index();
                $table->string('jenis', 10)->nullable()->index();
                $table->string('no_pl')->nullable()->index();
                $table->dateTime('tgl_turun_pl')->nullable();
                $table->string('nama_unit')->nullable();
                $table->string('pengiriman')->nullable();
                $table->string('service_pengiriman')->nullable();
                $table->text('nama_barang')->nullable();
                $table->string('kategori_order')->nullable()->index(); // Modul / Majalah / Sertifikat
                $table->unsignedBigInteger('product_id')->nullable();
                $table->json('product_ids')->nullable();
                $table->dateTime('tgl_bayar')->nullable();
                $table->decimal('jumlah_bayar', 15, 2)->default(0);
                $table->string('nama_stokis')->nullable();
                $table->dateTime('tgl_estimasi')->nullable();
                $table->integer('estimasi_hari')->nullable();
                $table->string('penyebut')->nullable();
                $table->string('pengambil')->nullable();
                $table->text('ket')->nullable();
                $table->decimal('order_weight', 12, 2)->default(0);
                $table->string('billing_last_name')->nullable();
                $table->string('billing_company')->nullable();
                $table->string('rekap_number')->nullable()->index();
                $table->timestamp('printed_at')->nullable();
                $table->timestamp('picking_printed_at')->nullable();
                $table->timestamps();

                $table->foreign('jakarta_id')
                    ->references('id')
                    ->on("jakarta_{$r}")
                    ->onDelete('cascade');
            });
        }
    }

    /** Pindahkan order lama yang belum diproses dari jakarta_pasif ke tabel rumahnya. */
    private function pindahDataLama(string $r): void
    {
        if (! Schema::hasTable('jakarta_pasif') || ! Schema::hasColumn('jakarta_pasif', 'jenis')) {
            return;
        }

        $kode = strtoupper($r);

        $kolom = array_values(array_diff(
            Schema::getColumnListing("jakarta_{$r}"),
            ['id']
        ));
        $kolomAsal = Schema::getColumnListing('jakarta_pasif');
        $kolom = array_values(array_intersect($kolom, $kolomAsal));

        $sudahAdaPicking = Schema::hasTable('picking_pasif')
            ? DB::table('picking_pasif')->whereNotNull('jakarta_pasif_id')->pluck('jakarta_pasif_id')->all()
            : [];

        DB::transaction(function () use ($r, $kode, $kolom, $sudahAdaPicking) {
            $baris = DB::table('jakarta_pasif')
                ->where('jenis', $kode)
                ->where(function ($q) {
                    $q->where('is_processed', 0)->orWhereNull('is_processed');
                })
                ->when(Schema::hasTable('realisasi_pasif'), function ($q) {
                    $q->whereNotExists(function ($s) {
                        $s->select(DB::raw(1))
                            ->from('realisasi_pasif')
                            ->whereColumn('realisasi_pasif.jakarta_pasif_id', 'jakarta_pasif.id');
                    });
                })
                ->when($sudahAdaPicking, fn ($q) => $q->whereNotIn('id', $sudahAdaPicking))
                ->orderBy('id')
                ->get();

            foreach ($baris as $row) {
                $data = [];
                foreach ($kolom as $k) {
                    $data[$k] = $row->{$k};
                }

                $idBaru = DB::table("jakarta_{$r}")->insertGetId($data);

                $items = DB::table('jakarta_pasif_items')->where('jakarta_pasif_id', $row->id)->get();
                foreach ($items as $item) {
                    DB::table("jakarta_{$r}_items")->insert([
                        'jakarta_id'  => $idBaru,
                        'product_id'  => $item->product_id,
                        'sku'         => $item->sku,
                        'label'       => $item->label,
                        'nama_produk' => $item->nama_produk,
                        'qty'         => $item->qty,
                        'harga'       => $item->harga,
                        'subtotal'    => $item->subtotal,
                        'created_at'  => $item->created_at,
                        'updated_at'  => $item->updated_at,
                    ]);
                }

                DB::table('jakarta_pasif_items')->where('jakarta_pasif_id', $row->id)->delete();
                DB::table('jakarta_pasif')->where('id', $row->id)->delete();
            }
        });
    }
};
