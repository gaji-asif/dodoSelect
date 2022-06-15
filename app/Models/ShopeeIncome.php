<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeIncome extends Model
{
    /**
     * Attributes should be casts
     *
     * @var array
     */
    protected $casts = [
        'returnsn_list' => 'array',
        'refund_id_list' => 'array',
        'seller_voucher_code' => 'array'
    ];

    /**
     * Get Shopee shop data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shopee()
    {
        return $this->belongsTo(Shopee::class, 'shop_id', 'shop_id')->withDefault();
    }

    /**
     * Get shopee_order_purchases data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shopee_order_purchase()
    {
        return $this->hasOne(ShopeeOrderPurchase::class, 'order_id', 'ordersn')->withDefault();
    }

    /**
     * Query to search data from ShopeeTransaction table
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchTable($query, $search = null)
    {
        if (! empty($search)) {
            return $query->where(function ($query) use ($search) {
                $query->where('ordersn', 'LIKE', '%' . $search . '%')
                    ->orWhere('buyer_user_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('escrow_amount', 'LIKE', '%' . $search . '%')
                    ->orWhere('buyer_total_amount', 'LIKE', '%' . $search . '%');
            });
        }

        return;
    }

    /**
     * Query to filter by shop_id field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $shopId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByShop($query, $shopId = null)
    {
        if (! empty($shopId)) {
            return $query->where('shop_id', $shopId);
        }

        return;
    }
}
