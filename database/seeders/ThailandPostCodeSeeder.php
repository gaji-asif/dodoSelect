<?php

namespace Database\Seeders;

use App\Imports\ThailandPostCodeImport;
use App\Models\ThailandPostCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class ThailandPostCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $postCodeTable = (new ThailandPostCode())->getTable();
        DB::table($postCodeTable)->truncate();

        $postCodeData = storage_path('/framework/seeders/thai_post_code.csv');
        Excel::import(new ThailandPostCodeImport, $postCodeData, null, MaatwebsiteExcel::CSV);
    }
}
