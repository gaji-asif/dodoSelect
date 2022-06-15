<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait ECommerceProductQuery
{
    /**
     * Query to filter by `website_id`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int|null  $websiteId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopebyWebsite($query, $websiteId = null)
    {
        if ($websiteId >= 1) {
            return $query->where('website_id', $websiteId);
        }

        return;
    }

    /**
     * Query to get published data only
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Query to get published data only
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int|null  $isLinked
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeIsLinked($query, $isLinked = null)
    {
        if (!is_null($isLinked) && $isLinked >= 0) {
            return $query->where('is_linked', $isLinked);
        }

        return;
    }

    /**
     * Query to filter by `type`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $type
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopebyType($query, $type = null)
    {
        if ($type == 'ex_variable') {
            return $query->where(function (Builder $product) {
                $product->where('type', '<>', 'variable')
                    ->where('type', '<>', 'variation');
            });
        }
        if($type == 'simple'){
            return $query->where('type', $type);
        }

        return;
    }

    /**
     * Query to filter by `byDiscountRange`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $discount
     * @return \Illuminate\Database\Query\Builder
     */

    public function scopebyDiscountRange($query, $discount = null){
            if($discount == '1'){
                return $query->orderBy('discount', 'DESC');
            }
            if($discount == '2'){
                return $query->orderBy('discount', 'ASC');
            }
            return;
        }
}