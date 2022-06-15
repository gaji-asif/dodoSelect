<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    use HasFactory;

    public function shipping_cost()
    {
        return $this->hasMany(ShippingCost::class,'shipper_id','id');
    }

    /**
     * Query to search data over manage shipper datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function(Builder $shipper) use ($keyword) {
                $shipper->where('name', 'like', "%{$keyword}%");
            });
        }

        return;
    }

    /**
     * Query to count shipping_cost
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeTotalShippingCost($query)
    {
        $shipperTable = $this->getTable();
        $shippingCostTable = (new ShippingCost())->getTable();

        return $query->addSelect(['total_shipping_cost' => ShippingCost::selectRaw("COUNT(*)")
            ->whereColumn("{$shippingCostTable}.shipper_id", "{$shipperTable}.id")
            ->limit(1)
        ]);
    }
}
