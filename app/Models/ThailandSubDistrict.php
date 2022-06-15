<?php

namespace App\Models;

use App\Traits\Models\ThailandAddressQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThailandSubDistrict extends Model
{
    use HasFactory, ThailandAddressQuery;

    /**
     * Filter by district
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|int|null  $districtCode
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByDistrict($query, $districtCode = null)
    {
        if ($districtCode == -1) {
            return;
        }

        return $query->where('district_code', $districtCode);
    }
}
