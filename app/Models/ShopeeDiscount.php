<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopeeDiscount extends Model
{
    use HasFactory;

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'shopee_discount';

    protected $fillable = [
        'discount_id'
    ];

    /**
     * Query to filter by `website_id`
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int|null $websiteId
     * @return bool|\Illuminate\Database\Query\Builder
     */
    public function scopeByShopeeWebsiteId($query, $websiteId = null)
    {
        $shopeeDiscountTable = $this->getTable();
        if ($websiteId >= 1) {
            return $query->where("{$shopeeDiscountTable}.website_id", $websiteId);
        }

        return false;
    }

    public function shop()
    {
        return $this->belongsTo(Shopee::class, 'website_id', 'shop_id');
    }
}
