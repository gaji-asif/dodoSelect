<?php

namespace App\Imports;

use App\Models\ThailandPostCode;
use Maatwebsite\Excel\Concerns\ToModel;

class ThailandPostCodeImport implements ToModel
{
    /**
    * @param array $row
    */
    public function model(array $row)
    {
        if ($row[0] == 'sub_district_code') {
            return null;
        }

        return new ThailandPostCode([
            'sub_district_code' => $row[0],
            'post_code' => $row[1]
        ]);
    }
}
