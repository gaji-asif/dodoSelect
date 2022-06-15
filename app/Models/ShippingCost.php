<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCost extends Model
{
    use HasFactory;

    /**
     * Relationship to `shippers` table
     *
     * @return mixed
     */
    public function shipper()
    {
        return $this->belongsTo(Shipper::class)->withDefault();
    }

    /**
     * Query to filter by weight_from <--> weight_to
     *
     * @param  \Illuminate\Database\Query\Builder   $query
     * @param  double|null                          $weight
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFilterWeightBetween($query, $weight = null)
    {
        if ($weight > 0) {
            return $query->where('weight_from', '<=', $weight)
                        ->where('weight_to', '>=', $weight);
        }

        return;
    }
}
