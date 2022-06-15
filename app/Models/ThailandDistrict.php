<?php

namespace App\Models;

use App\Traits\Models\ThailandAddressQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThailandDistrict extends Model
{
    use HasFactory, ThailandAddressQuery;

    /**
     * Filter by province
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|int|null  $provinceCode
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByProvince($query, $provinceCode = null)
    {
        if ($provinceCode == -1) {
            return;
        }

        return $query->where('province_code', $provinceCode);
    }
}
