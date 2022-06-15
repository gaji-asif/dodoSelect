<?php

namespace Database\Seeders;

use App\Models\LazadaSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LazadaSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = (new LazadaSetting())->getTable();
        DB::table($settings)->truncate();

        LazadaSetting::insert([
            'host' => 'https://auth.lazada.com/oauth/authorize',
            'regional_host' => 'https://api.lazada.co.th/rest',
            'redirect_url' => route('lazada.settings'),
            'app_id' => '101898',
            'app_secret' => 'Syyl2Y7hBXY3b9eVPnss0ypUkhkTVvOv'
        ]);
    }
}
