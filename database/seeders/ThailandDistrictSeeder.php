<?php

namespace Database\Seeders;

use App\Imports\ThailandDistrictImport;
use App\Models\ThailandDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class ThailandDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districtTable = (new ThailandDistrict())->getTable();
        DB::table($districtTable)->truncate();

        $districtData = storage_path('/framework/seeders/thai_district.csv');
        Excel::import(new ThailandDistrictImport, $districtData, null, MaatwebsiteExcel::CSV);
    }
}
