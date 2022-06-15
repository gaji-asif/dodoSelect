<?php

namespace Database\Seeders;

use App\Imports\ThailandProvinceImport;
use App\Models\ThailandProvince;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class ThailandProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinceTable = (new ThailandProvince())->getTable();
        DB::table($provinceTable)->truncate();

        $provinceData = storage_path('/framework/seeders/thai_province.csv');
        Excel::import(new ThailandProvinceImport, $provinceData, null, MaatwebsiteExcel::CSV);
    }
}
