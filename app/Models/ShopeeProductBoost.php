<?php

namespace App\Models;

use App\Traits\Models\ECommerceProductQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeProductBoost extends Model
{
    use HasFactory, ECommerceProductQuery;

    /**
     * Define `status` field value
     *
     * @var mixed
     */
    CONST STATUS_PUBLISHED = 'publish';

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'shopee_product_boosts';


    /**
     * Get the shop data
     *
     * @return mixed
     */
    public function shopee()
    {
        return $this->belongsTo(Shopee::class, 'website_id', 'shop_id')->withDefault();
    }


    /**
     * Get the product data
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Shopee::class, 'item_id', 'product_id')->withDefault();
    }


    /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDataTable($query)
    {
        $shopeeProductBoostTable = $this->getTable();
        $shopeeShopsTable = (new Shopee())->getTable();
        $shopeeProductsTable = (new ShopeeProduct())->getTable();
        return $query->join("{$shopeeShopsTable}", "{$shopeeShopsTable}.shop_id", '=', "{$shopeeProductBoostTable}.website_id")
            ->join("{$shopeeProductsTable}", "{$shopeeProductBoostTable}.item_id", '=', "{$shopeeProductsTable}.product_id");
    }


    /**
     * Match by "parent_id" of the shopee product.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  integer  $parent_id
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByProductParentId($query, $parent_id = 0)
    {
        $shopeeProductTable = $this->getTable();
        return $query->where("{$shopeeProductTable}.parent_id", "=", $parent_id);
    }


    public static function getChildProductCount($parent_id) 
    {
        return ShopeeProduct::whereParentId($parent_id)->count();
    }


    /**
     * Query to search by name from table
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function ($shopeeProduct) use ($keyword) {
                $shopeeProduct->where('product_name', 'like', "%$keyword%")
                    ->orWhere('product_code', 'like', "%$keyword%")
                    ->orWhere('type', 'like', "%$keyword%");
            });
        }
        return;
    }
}
