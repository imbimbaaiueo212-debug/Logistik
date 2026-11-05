<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        foreach ([
            'kalender_dlc_periodes',
            'kalender_pasif_periodes',
            'kalender_bacaan_periodes',
            'kalender_spare_pasifs',
            'kalender_pasif_manual_periodes',
        ] as $tabel) {
            DB::table($tabel)
                ->whereRaw("TRIM(edisi) REGEXP '^20[0-9]{2}$'")
                ->update(['edisi' => DB::raw("CONCAT('KLD', SUBSTRING(TRIM(edisi), 3, 2))")]);
        }
    }

    public function down()
    {
    }
};
