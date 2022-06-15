<?php

namespace App\Models;

use App\Traits\Models\ECommerceProductQuery;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class WooProduct extends Model
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_name',
        'images',
        'type',
        'category_id',
        'image',
        'product_code',
        'seller_id',
        'price',
        'weight',
        'dodo_product_id',
        'is_linked',
        'description',
        'short_description',
        'images'
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get parent data
     *
     * @return mixed
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')->withDefault();
    }

    /**
     * Get the product data
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the stock of the product
     *
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->belongsTo(WooProductMainStock::class, 'id', 'product_id')
                    ->withDefault(['quantity' => 0]);
    }

    /**
     * Get the shop
     *
     * @return mixed
     */
    public function wooShop()
    {
        return $this->belongsTo(WooShop::class, 'website_id', 'id')->with('shops');
    }

    /**
     * Get the price
     *
     * @return mixed
     */
    public function getProductPrice()
    {
        return $this->hasMany(WooProductPrice::class, 'id', 'proudct_id');
    }

    /**
     * Get the inventory
     *
     * @return mixed
     */
    public function woo_inventory()
    {
        return $this->belongsTo(WooInventory::class, 'inventory_id', 'id')->withDefault();
    }

    /**
     * Get the shop
     *
     * @return mixed
     */
    public function woo_shop()
    {
        return $this->belongsTo(WooShop::class, 'website_id', 'id')->withDefault();
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
     * Check if product is variation or not by parent_id
     *
     * @param  self  $product
     * @return bool
     */
    public static function isVariation(self $product)
    {
        return $product->parent_id != 0;
    }

     /**
     * Check if product has parent or Not. If not then return parent_id
     *
     * @param  website id  $website_id  product id $product_id
     * @return $parent_id
     */

    public static function isParent($website_id, $product_id)
    {
        $product = WooProduct::where('website_id', $website_id)
                             ->where('product_id', $product_id)
                             ->first();
        if(isset($product)){
            if(isset($product->parent_id)){
                if($product->parent_id == 0){
                    return 0;
                }
                else{
                    return $product->parent_id;
                }
            }
        }
        return false;
    }

    

    /**
     * Query to search by name from table
     *
     * @param Builder $query
     * @param string|null $keyword
     * @return Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function ($wooProduct) use ($keyword) {
                $wooProduct->where('product_name', 'like', "%$keyword%")
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
     * @param Builder $query
     * @param $filter
     * @param null $productSyncId
     * @return Builder
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
     * @param $query
     * @param $shopId
     * @return Builder
     */
    public function scopeShopFilter($query, $shopId)
    {
        $wooShopsTable = (new WooShop())->getTable();

        if (!empty($shopId)) {
            return $query->where("{$wooShopsTable}.id", $shopId);
        }

        return;
    }

    /**
     * Join query for the datatable
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeJoinedWooShopDataTable($query)
    {
        $wooProductTable = $this->getTable();
        $wooShopsTable = (new WooShop())->getTable();
        $shopsTable = (new Shop())->getTable();

        return $query->join("{$wooShopsTable}", "{$wooShopsTable}.id", '=', "{$wooProductTable}.website_id")
            ->join("{$shopsTable}", "{$shopsTable}.id", '=', "{$wooShopsTable}.shop_id");
    }

    /**
     * returns the woo product attributes
     *
     * @param  $id
     * @return attributes data
     */    

    public static function getProductAttr($attributes){
        $attribute_1 = '';
        $attribute_2 = '';
        $attribute_1_option = '';
        $attribute_2_option = '';
        if(isset($attributes)){

            $attributes_data = json_decode($attributes);
            if(isset($attributes_data[0])){
                $attribute_1 = $attributes_data[0]->name;
                $attribute_1_option = $attributes_data[0]->option;
            }
            else{
                $attribute_1 = '';
                $attribute_1_option = '';
            }

            if(isset($attributes_data[1])){
                $attribute_2 = $attributes_data[1]->name;
                $attribute_2_option = $attributes_data[1]->option;
            }
            else{
                $attribute_2 = '';
                $attribute_2_option= '';
            }
        }
        return $data = [
            'attribute_1' =>$attribute_1,
            'attribute_1_option' =>$attribute_1_option,
            'attribute_2' =>$attribute_2,
            'attribute_2_option' =>$attribute_2_option,

        ];
    }

     /**
     * Get shop data
     *
     * @return BelongsTo
     */
    public function shops()
    {
        return $this->belongsTo(Shop::class, 'website_id', 'id')->withDefault(['name' => '']);
    }
}
