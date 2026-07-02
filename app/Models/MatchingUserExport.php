<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingUserExport extends Model
{
    protected $table = 'matching_user_export';

    protected $fillable = [
        'unit_kemitraan_id',
        'user_export_id',
        'no_cab',
        'billing_last_name',
        'status',
    ];

    public function unitKemitraan()
    {
        return $this->belongsTo(UnitKemitraan::class, 'unit_kemitraan_id', 'id_record');
    }

    public function userExport()
    {
        return $this->belongsTo(UserExportBimbaShop::class, 'user_export_id', 'ID');
    }
}