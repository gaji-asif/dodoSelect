<?php

namespace App\Imports;

use App\Models\ThailandDistrict;
use Maatwebsite\Excel\Concerns\ToModel;

class ThailandDistrictImport implements ToModel
{
    /**
    * @param array $row
    */
    public function model(array $row)
    {
        if ($row[0] == 'province_code') {
            return null;
        }

        return new ThailandDistrict([
            'province_code' => $row[0],
            'code' => $row[1],
            'name_en' => $row[2],
            'name_th' => $row[3]
        ]);
    }
}
