<?php

namespace App\Models;

use App\Traits\Models\ThailandAddressQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThailandPostCode extends Model
{
    use HasFactory, ThailandAddressQuery;

    /**
     * Filter by sub district
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|int|null  $subDistrictCode
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeBySubDistrict($query, $subDistrictCode = null)
    {
        if ($subDistrictCode == -1) {
            return;
        }

        return $query->where('sub_district_code', $subDistrictCode);
    }
}
