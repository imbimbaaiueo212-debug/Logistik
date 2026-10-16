<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Majalah\Models\PesananMajalah;
use App\Modules\Majalah\Models\PesananMajalahPuw1;
use App\Modules\Import\Models\DlcPeriode;
use App\Modules\Import\Models\PasifPeriode;
use App\Modules\Import\Models\BacaanPeriode;
use App\Modules\Import\Models\SparePasif;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Import\Http\Controllers\Concerns\ManualOrderSyncHelpers;

class ManualSyncController extends Controller
{
    use ManualOrderSyncHelpers;


 public function syncPesananMajalahToJakartaAktif()
{
    $created     = 0;
    $updated     = 0;
    $skipped     = 0;
    $errors      = [];
    $skippedList = [];

    $periodes = PesananMajalah::with([
        'kabupaten.units',
        'kotamadya.units',
    ])->get();

    $periodesPuw1 = PesananMajalahPuw1::with('units')->get();

    $periodesDlc = DlcPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    $periodesPasif = PasifPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    $periodesPasifManual = \App\Modules\Import\Models\PasifManualPeriode::with('transaksis')
        ->where('status', 'aktif')
        ->get();

    $periodesBacaan = BacaanPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    DB::beginTransaction();

    try {
        // =====================================================
        // A + B. KABUPATEN & KOTAMADYA → GROUP B
        // =====================================================
        foreach ($periodes as $periode) {
            $rawName = ($periode->judul ?? '') . ' ' . ($periode->bulan ?? '');
            $edisi   = $this->extractEdisiMajalah($rawName);

            foreach ($periode->kabupaten ?? [] as $kabupaten) {
                foreach ($kabupaten->units ?? [] as $unit) {
                    $wilayah = !empty($kabupaten->nama_kabupaten)
                        ? 'KABUPATEN ' . strtoupper(trim($kabupaten->nama_kabupaten))
                        : null;

                    $result = $this->createManualOrderFromUnit(
                        $unit,
                        $edisi,
                        $wilayah,
                        $kabupaten->contact_person ?? null,
                        'B',
                        $periode->no_ps ?? null
                    );
                    $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
                }
            }

            foreach ($periode->kotamadya ?? [] as $kotamadya) {
                foreach ($kotamadya->units ?? [] as $unit) {
                    $wilayah = !empty($kotamadya->nama_kotamadya)
                        ? 'KOTAMADYA ' . strtoupper(trim($kotamadya->nama_kotamadya))
                        : null;

                    $result = $this->createManualOrderFromUnit(
                        $unit,
                        $edisi,
                        $wilayah,
                        $kotamadya->contact_person ?? null,
                        'B',
                        $periode->no_ps ?? null
                    );
                    $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
                }
            }
        }

        // =====================================================
        // C. PUW1 (Jabodetabek) → GROUP B
        // =====================================================
        foreach ($periodesPuw1 as $periode) {
            $rawName = ($periode->judul ?? '') . ' ' . ($periode->bulan ?? '');
            $edisi   = $this->extractEdisiMajalah($rawName);

            foreach ($periode->units ?? [] as $unit) {
                $wilayah = !empty($unit->kabupaten_kota)
                    ? 'JABODETABEK ' . strtoupper(trim($unit->kabupaten_kota))
                    : 'JABODETABEK';

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    $wilayah,
                    $periode->contact_person ?? null,
                    'B',
                    $periode->no_ps ?? null
                );
                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // D. DLC → GROUP A (tipe majalah)
        // =====================================================
        foreach ($periodesDlc as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if (($item->qty ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (DLC)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => $item->qty,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                    // 'kabupaten_kota' tidak perlu lagi
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    null,               // ← diubah dari 'DLC' menjadi null
                    null,
                    'A',
                    $periode->no_ps ?? null,
                    'DLC'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // E. PASIF BIASA → GROUP F
        // =====================================================
        foreach ($periodesPasif as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                $qtyItem = (int) ($item->qty ?? $item->jumlah ?? 0);

                if ($qtyItem <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (Pasif)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => $qtyItem,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    null,               // Kab/Kota dikosongkan
                    null,
                    'F',
                    $periode->no_ps ?? null,
                    'majalah'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // E2. PASIF MANUAL → GROUP F
        // =====================================================
        foreach ($periodesPasifManual as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->transaksis as $item) {
                if (($item->jumlah ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (Pasif Manual)';
                    continue;
                }

                $idPesananExisting = trim((string) ($item->id_pesan ?? ''));

                $unit = (object) [
                    'id'                => $item->id,
                    'id_pesan'          => $item->id,
                    'custom_order_id'   => $idPesananExisting !== '' ? $idPesananExisting : null,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => $item->jumlah,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    null,               // Kab/Kota dikosongkan
                    null,
                    'F',
                    $periode->no_ps ?? null,
                    'majalah'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // F. BACAAN UNIT → GROUP A
        // =====================================================
        foreach ($periodesBacaan as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if ((int) ($item->bacaan_unit ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (Bacaan)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => 1,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    null,               // Kab/Kota dikosongkan
                    null,
                    'A',
                    $periode->no_ps ?? null,
                    'bacaan'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // G. SPARE PASIF 3% → GROUP A
        // =====================================================
        $this->syncSparePasifData();

        $spareItems = SparePasif::where('status', 'aktif')
            ->where('spare', '>', 0)
            ->get();

        foreach ($spareItems as $item) {
            $edisi = strtoupper(trim($item->edisi));
            $qty   = (int) $item->spare;

            if ($qty <= 0) {
                $skipped++;
                continue;
            }

            $unit = (object) [
                'id'                => 'spare-' . $item->id,
                'nama_unit'         => 'SPARE PASIF 3% ' . $edisi,
                'no_cabang'         => null,
                'jumlah_pesanan'    => $qty,
                'alamat_unit'       => 'Spare Pasif',
                'telepon'           => null,
                'mitra_pengelolaan' => null,
                // 'kabupaten_kota' tidak perlu lagi
            ];

            $result = $this->createManualOrderFromUnit(
                $unit,
                $edisi,
                null,               // ← diubah dari 'SPARE PASIF' menjadi null
                null,
                'A',
                $item->no_ps ?? null,
                'spare'
            );

            $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
        }

        DB::commit();

        $message = "✅ Sync Pesanan Majalah + DLC + Pasif + Bacaan + Spare Pasif 3% ke Manual selesai.<br>"
                 . "Berhasil masuk: <strong>{$created}</strong><br>"
                 . "Berhasil di-update (no_ps / qty): <strong>{$updated}</strong><br>"
                 . "Dilewati (sudah ada / qty 0): <strong>{$skipped}</strong>";

        if (count($skippedList) > 0) {
                $uniqueNames = array_values(array_unique($skippedList));
                $message .= "<br><br><strong>Dilewati (" . count($uniqueNames) . "):</strong><br>"
                        . "• " . implode('<br>• ', $uniqueNames);
            }

        if (count($errors) > 0) {
            $message .= "<br><br>❌ Error (" . count($errors) . "):<br>"
                     . "• " . implode('<br>• ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= "<br>• ... dan " . (count($errors) - 5) . " error lainnya";
            }
        }

        return redirect()
            ->route('import.manual')
            ->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Sync Pesanan Majalah + DLC + Pasif + Bacaan gagal: ' . $e->getMessage());

        return redirect()
            ->route('import.manual')
            ->with('error', 'Gagal sync: ' . $e->getMessage());
    }
}
}
