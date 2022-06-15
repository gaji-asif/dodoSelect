<?php

namespace App\Imports;

use App\Models\ThailandSubDistrict;
use Maatwebsite\Excel\Concerns\ToModel;

class ThailandSubDistrictImport implements ToModel
{
    /**
    * @param array $collection
    */
    public function model(array $row)
    {
        if ($row[0] == 'district_code') {
            return null;
        }

        return new ThailandSubDistrict([
            'district_code' => $row[0],
            'code' => $row[1],
            'name_en' => $row[2],
            'name_th' => $row[3]
        ]);
    }
}
