<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class ShopeeTransaction extends Model
{
    /**
     * Define `status` column
     *
     * @var
     */
    const STATUS_FAILED = 'FAILED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_INITIAL = 'INITIAL';

    /**
     * Get Shopee shop data
     *
     * @return \Illuminat\Database\Eloquent\Relations\BelongsTo
     */
    public function shopee()
    {
        return $this->belongsTo(Shopee::class, 'shop_id', 'shop_id')->withDefault();
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
                    ->orWhere('buyer_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('transaction_id', 'LIKE', '%' . $search . '%')
                    ->orWhere('transaction_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('status', 'LIKE', '%' . $search . '%')
                    ->orWhere('amount', 'LIKE', '%' . $search . '%')
                    ->orWhere('transaction_fee', 'LIKE', '%' . $search . '%');
            });
        }

        return;
    }

    /**
     * Query to filter by date range of create_time field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  Date|null  $dateFrom
     * @param  Date|null  $dateTo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreateTimeDateRange($query, $dateFrom = null, $dateTo = null)
    {
        if (! empty($dateFrom) && ! empty($dateTo)) {
            $strDateFrom = strtotime($dateFrom . ' 00:00:00');
            $strDateTo = strtotime($dateTo . ' 23:59:59');

            return $query->whereBetween('create_time', [ $strDateFrom, $strDateTo ]);
        }

        return;
    }

    /**
     * Query to filter by shop_id field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  Date|null  $shopId
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
