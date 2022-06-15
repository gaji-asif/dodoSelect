<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Define `order_status` field value
     *
     * @var mixed
     */
    CONST ORDER_STATUS_PENDING = 1;
    CONST ORDER_STATUS_PROCESSING = 2;
    CONST ORDER_STATUS_READY_TO_SHIP = 3;
    CONST ORDER_STATUS_SHIPPED = 4;

    /**
     * Define `payment_status` field value
     *
     * @var mixed
     */
    CONST PAYMENT_STATUS_UNPAID = 0;
    CONST PAYMENT_STATUS_PAID = 1;

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'str_order_status',
        'str_payment_status'
    ];

    /**
     * Relationship to `custom_order_details` table
     *
     * @return mixed
     */
    public function customOrderDetails()
    {
        return $this->hasMany(CustomOrderDetail::class);
    }

    /**
     * Relationship to `shops` table
     *
     * @return mixed
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Relationship to `channels` table
     *
     * @return mixed
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class)->withDefault();
    }

    /**
     * Relationship to `customers` table
     *
     * @return mixed
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }

    /**
     * Get all `order_status`
     *
     * @return array
     */
    public static function getAllOrderStatus()
    {
        return [
            self::ORDER_STATUS_PENDING => 'Pending',
            self::ORDER_STATUS_PROCESSING => 'Processing',
            self::ORDER_STATUS_READY_TO_SHIP => 'Ready to Ship',
            self::ORDER_STATUS_SHIPPED => 'Shipped'
        ];
    }

    /**
     * Get all `payment_status`
     *
     * @return array
     */
    public static function getAllPaymentStatus()
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'Unpaid',
            self::PAYMENT_STATUS_PAID => 'Paid'
        ];
    }

    /**
     * Accessor for `str_order_status`
     *
     * @return string
     */
    public function getStrOrderStatusAttribute()
    {
        $orderStatuses = self::getAllOrderStatus();
        $orderStatusAttribute = $this->attributes['order_status'] ?? '';

        return $orderStatuses[$orderStatusAttribute] ?? 'Unknown';
    }

    /**
     * Accessor for `str_payment_status`
     *
     * @return string
     */
    public function getStrPaymentStatusAttribute()
    {
        $paymentStatuses = self::getAllPaymentStatus();
        $paymentStatusAttribute = $this->attributes['payment_status'] ?? '';

        return $paymentStatuses[$paymentStatusAttribute] ?? 'Unknown';
    }

    /**
     * Query to filter by `order_status`
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $orderStatus
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrderStatus($query, $orderStatus = 0)
    {
        if ($orderStatus > 0) {
            return $query->where('order_status', $orderStatus);
        }

        return;
    }

    /**
     * Query to search by from custom-order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $customersTable = (new Customer())->getTable();

            return $query->where(function(Builder $customOrder) use ($customersTable, $keyword) {
                $customOrder->where('in_total', 'like', "%$keyword%")
                        ->orWhere('shipping_cost', 'like', "%$keyword%")
                        ->orWhere("{$customersTable}.customer_name", 'like', "%$keyword%")
                        ->orWhere("{$customersTable}.contact_phone", 'like', "%$keyword%");
            });
        }

        return;
    }

    /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDataTable($query)
    {
        $customOrdersTable = $this->getTable();
        $customersTable = (new Customer())->getTable();

        return $query->join("{$customersTable}", "{$customersTable}.id", '=', "{$customOrdersTable}.customer_id");
    }

    /**
     * Sub Query to get the total product count for each customer
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeQuantity($query)
    {
        $customOrderTable = $this->getTable();
        $customOrderDetailsTable = (new CustomOrderDetail())->getTable();

        return $query->addSelect(['quantity' => CustomOrderDetail::selectRaw("SUM({$customOrderDetailsTable}.quantity)")
            ->whereColumn("{$customOrderDetailsTable}.custom_order_id", "{$customOrderTable}.id")
            ->limit(1)
        ]);
    }
}
