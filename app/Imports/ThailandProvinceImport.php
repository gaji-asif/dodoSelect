<?php

namespace App\Imports;

use App\Models\ThailandProvince;
use Maatwebsite\Excel\Concerns\ToModel;

class ThailandProvinceImport implements ToModel
{
    /**
    * @param array $row
    */
    public function model(array $row)
    {
        if ($row[0] == 'province_code') {
            return null;
        }

        return new ThailandProvince([
            'code' => $row[0],
            'name_en' => $row[1],
            'name_th' => $row[2]
        ]);
    }
}
