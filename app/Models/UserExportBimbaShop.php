<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExportBimbaShop extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'user_export_bimba_shop';

    // Primary key
    protected $primaryKey = 'ID';

    // Jika primary key bukan auto-increment integer biasa
    public $incrementing = false;

    // Tipe primary key
    protected $keyType = 'int';

    // Kolom yang boleh diisi massal
    protected $fillable = [
        'ID',
        'customer_id',
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_registered',
        'display_name',
        'first_name',
        'last_name',
        'user_status',
        'roles',
        'nickname',
        'description',
        'rich_editing',
        'syntax_highlighting',
        'admin_color',
        'use_ssl',
        'show_admin_bar_front',
        'locale',
        'wp_user_level',
        'dismissed_wp_pointers',
        'show_welcome_panel',
        'session_tokens',
        'last_update',
        'orders',
        'total_spent',
        'aov',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_email',
        'billing_phone',
        'billing_address_1',
        'billing_address_2',
        'billing_postcode',
        'billing_city',
        'billing_state',
        'billing_country',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_phone',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_postcode',
        'shipping_city',
        'shipping_state',
        'shipping_country',
    ];

    // Kolom yang harus di-cast ke tipe tertentu
    protected $casts = [
        'user_registered'       => 'datetime',
        'last_update'           => 'datetime',
        'total_spent'           => 'decimal:2',
        'aov'                   => 'decimal:2',
        'use_ssl'               => 'boolean',
        'show_admin_bar_front'  => 'boolean',
        'show_welcome_panel'    => 'boolean',
        'wp_user_level'         => 'integer',
        'user_status'           => 'integer',
        'orders'                => 'integer',
    ];

    // Jika ingin menonaktifkan timestamps (karena sudah ada di migration)
    // public $timestamps = true;
}