<?php

namespace Database\Seeders;

use App\Models\ShopeeSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopeeSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = (new ShopeeSetting())->getTable();
        DB::table($settings)->truncate();

        ShopeeSetting::insert([
            'host' => 'https://partner.shopeemobile.com',
            'path' => '/api/v2/shop/auth_partner',
            'redirect_url' => route('shopee.settings'),
            'parent_id' => '2001732',
            'parent_key' => '378913d60838f9e5178afd1959e0eb5719548116d73c571720238fbda9716410'
        ]);
    }
}
