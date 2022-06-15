<?php

namespace Database\Seeders;

use App\Imports\ThailandSubDistrictImport;
use App\Models\ThailandSubDistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class ThailandSubDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subDistrictTable = (new ThailandSubDistrict())->getTable();
        DB::table($subDistrictTable)->truncate();

        $districtData = storage_path('/framework/seeders/thai_sub_district.csv');
        Excel::import(new ThailandSubDistrictImport, $districtData, null, MaatwebsiteExcel::CSV);
    }
}
