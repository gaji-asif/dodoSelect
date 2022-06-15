<?php

namespace App\Models;

use App\Traits\Models\ECommerceProductQuery;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class ShopeeProduct extends Model
{
    use HasFactory, ECommerceProductQuery;

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'shopee_products';

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
     * Get the shop data
     *
     * @return mixed
     */
    public function shopee()
    {
        return $this->belongsTo(Shopee::class, 'website_id', 'shop_id')->withDefault();
    }

    /**
     * Get the linked product/catalogue
     *
     * @return mixed
     */
    public function catalog()
    {
        return $this->belongsTo(Product::class, 'dodo_product_id', 'id')->withDefault();
    }

    /**
     * Get the shop product boost data
     *
     * @return mixed
     */
    public function shopeeProductBoost()
    {
        return $this->belongsTo(ShopeeProductBoost::class, 'item_id', 'product_id');
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
     * Join query for the datatable with shopee table
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedShopeeShopDataTable($query)
    {
        $shopeeProductTable = $this->getTable();
        $shopeeTable = (new Shopee())->getTable();
        return $query->join("{$shopeeTable}", "{$shopeeTable}.shop_id", '=', "{$shopeeProductTable}.website_id");
    }

    /**
     * Get missing info options.
     *
     * @return array
     */
    public static function getShopeeProductMissingInfoOptions()
    {
        return [
            "-1" => ucwords(__('translation.all')),
            "missing_variable_image" => ucwords(__('translation.missing variation image')),
            "not_enough_cover_image" => ucwords(__('translation.not enough cover image')),
        ];
    }

    /**
     * Check for missing info for shopee products.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $missing_infos
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByMissingInfo($query, $missing_info = "")
    {
        $shopeeProductTable = $this->getTable();
        if (!empty($missing_info) and array_key_exists($missing_info, self::getShopeeProductMissingInfoOptions())) {
            switch ($missing_info) {
                case "missing_variable_image":
                    return $query->whereParentId(0)
                        ->where("{$shopeeProductTable}.total_size_wise_options", ">", 0)
                        ->whereRaw("{$shopeeProductTable}.total_size_wise_variation_images != {$shopeeProductTable}.total_size_wise_options");
                case "not_enough_cover_image":
                    return $query->whereParentId(0)
                        ->where("total_cover_images", "<", 5);
            }
        }
        return;
    }

    /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDataTableWithBoostedInfo($query)
    {
        $shopeeProductsTable = $this->getTable();
        $shopeeShopsTable = (new Shopee())->getTable();
        $shopeeProductBoostTable = (new ShopeeProductBoost())->getTable();
        return $query->join("{$shopeeShopsTable}", "{$shopeeShopsTable}.shop_id", '=', "{$shopeeProductsTable}.website_id")
            ->leftJoin("{$shopeeProductBoostTable}", "{$shopeeProductBoostTable}.item_id", '=', "{$shopeeProductsTable}.product_id");
    }

    /**
     * Match by "seller_id" of the shopee product.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  integer  $seller_id
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeBySellerId($query, $seller_id)
    {
        $shopeeProductTable = $this->getTable();
        return $query->where("{$shopeeProductTable}.seller_id", "=", $seller_id);
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

    /**
     * Count the number of child product
     *
     * @return int
     */
    public static function getChildProductCount($parent_id)
    {
        return ShopeeProduct::whereParentId($parent_id)->count();
    }

    /**
     * Query to filter by `website_id`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int|null  $websiteId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByShopeeWebsiteId($query, $websiteId = null)
    {
        $shopeeProductTable = $this->getTable();
        if ($websiteId >= 1) {
            return $query->where("{$shopeeProductTable}.website_id", $websiteId);
        }

        return;
    }

    /**
     * Query to get published data only
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByStatusPublished($query)
    {
        $shopeeProductTable = $this->getTable();
        return $query->where("{$shopeeProductTable}.status", self::STATUS_PUBLISHED);
    }

    /**
     * Match by "status" of the shopee boosted product.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  String $status
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByProductBoostStatus($query, $status = "all")
    {
        if ($status == "all" || !in_array($status, ["boosting", "queued", "not_queued", "boost_repeat", "boost_once"])) {
            return $query;
        }
        $shopeeProductBoostTable = (new ShopeeProductBoost())->getTable();
        if ($status == "not_queued") {
            return $query->whereNull("{$shopeeProductBoostTable}.status");
        } else if ($status == "boost_repeat") {
            return $query->where("{$shopeeProductBoostTable}.repeat_boost", "=", 1);
        } else if ($status == "boost_once") {
            return $query->where("{$shopeeProductBoostTable}.repeat_boost", "=", 0);
        }
        return $query->where("{$shopeeProductBoostTable}.status", "=", $status);
    }

    /**
     * Used for testing sql.
     */
    public static function logSqlQuery($builder) {
        try {
            $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
            $query = vsprintf($query, $builder->getBindings());
            Log::debug($query);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
