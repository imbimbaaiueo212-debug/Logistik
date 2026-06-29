<?php

namespace App\Imports;

use App\Models\UserExportBimbaShop;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserExportBimbaShopImport implements ToModel, WithStartRow, WithChunkReading
{
    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        if (empty(array_filter($row))) {
            return null;
        }

        return UserExportBimbaShop::updateOrCreate(
            [
                'ID' => (int) ($row[0] ?? 0),
            ],
            [
                'customer_id'           => $row[1] ?? null,
                'user_login'            => $row[2] ?? null,
                'user_pass'             => $row[3] ?? null,
                'user_nicename'         => $row[4] ?? null,
                'user_email'            => $row[5] ?? null,
                'user_url'              => $row[6] ?? null,
                'user_registered'       => $row[7] ?? null,
                'display_name'          => $row[8] ?? null,

                'first_name'            => $row[9] ?? null,
                'last_name'             => $row[10] ?? null,
                'user_status'           => $row[11] ?? 0,
                'roles'                 => $row[12] ?? null,
                'nickname'              => $row[13] ?? null,
                'description'           => $row[14] ?? null,

                'rich_editing'          => $row[15] ?? 'true',
                'syntax_highlighting'   => $row[16] ?? 'true',
                'admin_color'           => $row[17] ?? 'fresh',
                'use_ssl'               => $row[18] ?? 0,
                'show_admin_bar_front'  => $row[19] ?? 1,
                'locale'                => $row[20] ?? 'id_ID',

                'wp_user_level'         => $row[21] ?? 0,
                'dismissed_wp_pointers' => $row[22] ?? null,
                'show_welcome_panel'    => $row[23] ?? 1,
                'session_tokens'        => $row[24] ?? null,
                'last_update'           => $row[25] ?? null,

                'orders'                => $row[26] ?? 0,
                'total_spent'           => $row[27] ?? 0,
                'aov'                   => $row[28] ?? 0,

                'billing_first_name'    => $row[29] ?? null,
                'billing_last_name'     => $row[30] ?? null,
                'billing_company'       => $row[31] ?? null,
                'billing_email'         => $row[32] ?? null,
                'billing_phone'         => $row[33] ?? null,
                'billing_address_1'     => $row[34] ?? null,
                'billing_address_2'     => $row[35] ?? null,
                'billing_postcode'      => $row[36] ?? null,
                'billing_city'          => $row[37] ?? null,
                'billing_state'         => $row[38] ?? null,
                'billing_country'       => $row[39] ?? null,

                'shipping_first_name'   => $row[40] ?? null,
                'shipping_last_name'    => $row[41] ?? null,
                'shipping_company'      => $row[42] ?? null,
                'shipping_phone'        => $row[43] ?? null,
                'shipping_address_1'    => $row[44] ?? null,
                'shipping_address_2'    => $row[45] ?? null,
                'shipping_postcode'     => $row[46] ?? null,
                'shipping_city'         => $row[47] ?? null,
                'shipping_state'        => $row[48] ?? null,
                'shipping_country'      => $row[49] ?? null,
            ]
        );
    }
}