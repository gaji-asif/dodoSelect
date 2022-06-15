<?php

namespace App\Models;

use App\Traits\Models\ECommerceProductQuery;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class LazadaProduct extends Model
{
    use HasFactory, ECommerceProductQuery;

    /**
     * Define `status` field value
     *
     * @var mixed
     */
    CONST STATUS_PUBLISHED = 'publish';

    /**
     * Define `is_linked` field value
     *
     * @var mixed
     */
    CONST IS_LINKED_NO = 0;
    CONST IS_LINKED_YES = 1;

    /**
     * Get lazada data
     *
     * @return mixed
     */
    public function lazada()
    {
        return $this->belongsTo(Lazada::class, 'website_id', 'id')->withDefault();
    }

    /**
     * Get product catalog data
     *
     * @return mixed
     */
    public function catalog()
    {
        return $this->belongsTo(Product::class, 'dodo_product_id', 'id')->withDefault();
    }

    /**
     * Query to search by name from table
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $keyword
     * @return void
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function ($lazadaProduct) use ($keyword) {
                $lazadaProduct->where('product_name', 'like', "%$keyword%")
                        ->orWhere('product_code', 'like', "%$keyword%")
                        ->orWhere('type', 'like', "%$keyword%")
                        ->orWhereHas('catalog', function (IlluminateBuilder $catalog) use ($keyword) {
                            $catalog->where('product_code', 'like', "%$keyword%");
                        });
            });
        }

        return;
    }

    /**
     * Query to filter by selection
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param $filter
     * @param null $productSyncId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFilterSelected($query, $filter, $productSyncId = null)
    {
        if (!empty($filter)) {
            if ($filter == 'selected')
                return $query->where('dodo_product_id', $productSyncId);
            elseif ($filter == 'available')
                return $query->where('is_linked', '=', 0);
            elseif ($filter == 'unavailable')
                return $query->where('is_linked', '=', 1)
                    ->where('dodo_product_id', '!=', $productSyncId);
            else
                return;
        }

        return;
    }

    /**
     * Query to filter by selection
     *
     * @param Builder $query
     * @param $shopId
     * @return Builder
     */
    public function scopeShopFilter($query, $shopId)
    {
        if (!empty($shopId)) {
            return $query->where('website_id', $shopId);
        }

        return;
    }


    /**
     * Join query for the datatable with lazada table
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedLazadaShopDataTable($query)
    {
        $lazadaProductTable = $this->getTable();
        $lazadaTable = (new Lazada())->getTable();

        return $query->join("{$lazadaTable}", "{$lazadaTable}.id", '=', "{$lazadaProductTable}.website_id");
    }
}
