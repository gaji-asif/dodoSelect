<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\ShopeeOrderSyncTrait;

class ShopeeOrderPurchase extends Model
{
    use HasFactory, ShopeeOrderSyncTrait;

    /**
     * Define status field value
     *
     * @var mixed
     */
    CONST ORDER_STATUS_PENDING = 'PENDING';
    CONST ORDER_STATUS_PROCESSING = 'PROCESSING';
    CONST ORDER_STATUS_ON_HOLD = 'ON_HOLD';
    CONST ORDER_STATUS_PRE_ORDERED = 'PRE_ORDERED';
    CONST ORDER_STATUS_CANCEL = 'CANCELLED';
    CONST ORDER_STATUS_REFUNDED = 'REFUNDED';
    CONST ORDER_STATUS_COMPLETED = 'COMPLETED';


    /**
     * Avaialable Shopee order statuses.
     * https://open.shopee.com/documents?module=63&type=2&id=50&version=1
     */
    CONST ORDER_STATUS_UNPAID = 'UNPAID';
    CONST ORDER_STATUS_READY_TO_SHIP = 'READY_TO_SHIP';
    CONST ORDER_STATUS_RETRY_SHIP = 'RETRY_SHIP';
    CONST ORDER_STATUS_SHIPPED = 'SHIPPED';
    CONST ORDER_STATUS_TO_CONFIRM_RECEIVE = 'TO_CONFIRM_RECEIVE';
    CONST ORDER_STATUS_IN_CANCEL = 'IN_CANCEL';
    CONST ORDER_STATUS_CANCELLED = 'CANCELLED';
    CONST ORDER_STATUS_TO_RETURN = 'TO_RETURN';
    CONST ORDER_STATUS_INVOICE_PENDING = 'INVOICE_PENDING';

    /**
     * Custom status.
     */
    CONST ORDER_STATUS_TO_PAY = 'TO_PAY';
    CONST ORDER_STATUS_READY_TO_SHIP_AWB = 'READY_TO_SHIP_AWB';
    CONST ORDER_STATUS_UNVERIFIED = 'UNVERIFIED';
    CONST ORDER_STATUS_SHIPPED_TO_WEARHOUSE = 'SHIPPED_TO_WAREHOUSE';

    /**
     * Order cancel reasons.
     */
    CONST ORDER_CANCEL_REASON_OUT_OF_STOCK = 'OUT_OF_STOCK';
    CONST ORDER_CANCEL_REASON_CUSTOMER_REQUEST = 'REASON_CUSTOMER_REQUEST';
    CONST ORDER_CANCEL_REASON_UNDELIVERABLE_AREA = 'UNDELIVERABLE_AREA';
    CONST ORDER_CANCEL_REASON_COD_NOT_SUPPORTED = 'COD_NOT_SUPPORTED';

    /**
     * Shipping method for Shopee.
     */
    CONST SHIPPING_METHOD_PICKUP = "pickup";
    CONST SHIPPING_METHOD_DROPOFF = "dropoff";

    /**
     * Airway bill print status
     */
    CONST AIRWAY_BILL_STATUS_PRINTED = "PRINTED";
    CONST AIRWAY_BILL_STATUS_NOT_PRINTED = "NOT_PRINTED";

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'shopee_order_purchases';

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'str_order_status', 'str_order_status_custom'
    ];


    /**
     * Relationship to `suppliers` table
     *
     * @return mixed
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id', 'id');
    }

    /**
     * Relationship to `shopee_order_purchase_details` table
     *
     * @return mixed
     */
    public function orderProductDetails()
    {
       return $this->hasMany(ShopeeOrderPurchaseDetail::class,'order_purchase_id','id')->with('product');
    }

    /**
     * Get shop data
     *
     * @return BelongsTo
     */
    public function shopee()
    {
        return $this->belongsTo(Shopee::class, 'website_id', 'id')->withDefault();
    }

    /**
     * Get shopee_income data
     *
     * @return HasOne
     */
    public function shopee_income()
    {
        return $this->hasOne(ShopeeIncome::class, 'ordersn', 'order_id')->withDefault();
    }

    /**
     * Get seller data
     *
     * @return BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id')->withDefault();
    }

    /**
     * Get all `status` for Shopee orders.
     *
     * @return array
     */
    public static function getAllOrderStatus()
    {
        return [
            self::ORDER_STATUS_UNPAID => 'UNPAID',
            self::ORDER_STATUS_READY_TO_SHIP => 'READY TO SHIP',
            self::ORDER_STATUS_RETRY_SHIP => 'RETRY SHIP',
            self::ORDER_STATUS_SHIPPED => 'SHIPPED',
            self::ORDER_STATUS_TO_CONFIRM_RECEIVE => 'TO CONFIRM RECEIVE',
            self::ORDER_STATUS_COMPLETED => 'COMPLETED',
            self::ORDER_STATUS_CANCELLED => 'CANCELLED',
            self::ORDER_STATUS_IN_CANCEL => 'IN CANCEL',
            self::ORDER_STATUS_TO_RETURN => 'TO RETURN',
            self::ORDER_STATUS_INVOICE_PENDING => 'INVOICE PENDING'
        ];
    }


    /**
     * Get all `status_custom` for Shopee orders.
     *
     * @return array
     */
    public static function getAllOrderStatusCustom()
    {
        return [
            self::ORDER_STATUS_TO_PAY => 'PENDING',
            self::ORDER_STATUS_PROCESSING => 'PROCESSING',
            self::ORDER_STATUS_READY_TO_SHIP => 'READY TO SHIP',
            self::ORDER_STATUS_READY_TO_SHIP_AWB => 'READY TO SHIP',
            // self::ORDER_STATUS_RETRY_SHIP => 'RETRY SHIP',
            self::ORDER_STATUS_RETRY_SHIP => 'FAILED PICKUP',
            self::ORDER_STATUS_SHIPPED => 'LOGISTICS PROCESSED',
            self::ORDER_STATUS_COMPLETED => 'COMPLETED',
            self::ORDER_STATUS_CANCELLED => 'CANCELLED',
            self::ORDER_STATUS_IN_CANCEL => 'CANCELLATION REQUEST',
            self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE => 'WAREHOUSE SHIPPED'
        ];
    }


    /**
     * Get status schema for order datatable
     *
     * @return array
     */
    public static function getStatusSchemaForDatatable()
    {
        return [
            [
                'id' => self::ORDER_STATUS_UNPAID,
                'text' => __("shopee.top_nav.status_tab.to_pay"),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-currency-dollar" viewBox="0 0 16 16"><path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_UNPAID,
                        'text' => __("shopee.top_nav.shopee_order_statuses.unpaid")
                    ],
                    [
                        'id' => self::ORDER_STATUS_INVOICE_PENDING,
                        'text' => __("shopee.top_nav.shopee_order_statuses.invoice_pending")
                    ]
                ]
            ],
            [
                'id' => 'P2',
                'text' => __("shopee.top_nav.status_tab.to_process"),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-arrow-repeat" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_READY_TO_SHIP,
                        'text' => __("shopee.top_nav.shopee_order_statuses.ready_to_ship")
                    ],
                    [
                        'id' => self::ORDER_STATUS_TO_CONFIRM_RECEIVE,
                        'text' => __("shopee.top_nav.shopee_order_statuses.to_confirm_receive")
                    ],
                    [
                        'id' => self::ORDER_STATUS_TO_RETURN,
                        'text' => __("shopee.top_nav.shopee_order_statuses.to_return")
                    ]
                ]
            ],
            [
                'id' => 'P3',
                'text' => __("shopee.top_nav.status_tab.shipping"),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_RETRY_SHIP,
                        'text' => __("shopee.top_nav.shopee_order_statuses.retry_ship")
                    ],
                    [
                        'id' => self::ORDER_STATUS_SHIPPED,
                        'text' => __("shopee.top_nav.shopee_order_statuses.shipped")
                    ]
                ]
            ],
            [
                'id' => 'P4',
                'text' => __("shopee.top_nav.status_tab.cancelled"),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_IN_CANCEL,
                        'text' => __("shopee.top_nav.shopee_order_statuses.in_cancel")
                    ],
                    [
                        'id' => self::ORDER_STATUS_CANCELLED,
                        'text' => __("shopee.top_nav.shopee_order_statuses.cancelled")
                    ]
                ]
            ],
            [
                'id' => 'P5',
                'text' => __("shopee.top_nav.status_tab.completed"),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_COMPLETED,
                        'text' => __("shopee.top_nav.shopee_order_statuses.completed")
                    ]
                ]
            ],
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
        $orderStatusAttribute = $this->attributes['status'] ?? '';

        return $orderStatuses[$orderStatusAttribute] ?? 'Unknown';
    }

    /**
     * Query to filter by `status`
     *
     * @param  Builder  $query
     * @param  int  $orderStatus
     * @return Builder
     */
    public function scopeOrderStatus($query, $orderStatus = 0)
    {
        if ($orderStatus > 0) {
            return $query->where('status', $orderStatus);
        }

        return;
    }

    /**
     * By multiple "status" from datatable
     *
     * @param  Builder  $query
     * @param  string|null  $orderStatuses
     * @return Builder
     */
    public function scopeByMultipleOrderStatus($query, $orderStatuses = null)
    {
        if (!empty($orderStatuses)) {
            $splittedStatuses = explode(',', $orderStatuses);
            return $query->whereIn('status', $splittedStatuses);
        }

        return;
    }

    /**
     * Accessor for `str_order_status_custom`
     *
     * @return string
     */
    public function getStrOrderStatusCustomAttribute()
    {
        $orderStatuses = self::getAllOrderStatusCustom();
        $orderStatusAttribute = $this->attributes['status_custom'] ?? '';
        return $orderStatuses[strtoupper($orderStatusAttribute)] ?? 'Unknown';
    }

    /**
     * By multiple "status_custom" for datatable.
     *
     * @param  Builder  $query
     * @param  string|null  $orderStatuses
     * @return Builder
     */
    public function scopeByMultipleOrderStatusCustom($query, $orderStatuses = null)
    {
        if (!empty($orderStatuses)) {
            /**
             * If the custom status is "processing" and the orders have tracking number, then they are supposed to
             * be shown while filtering "Processing". For "init" to Shopee against an order may return "tracking_number" and
             * its stored in db but "status" of the order is updated by webhook. If webhook is delayed then in order datatable
             * orders with "processing" custom status with "tracking_number" are shown in table which breaks the condition for
             * "processing"
             * NOTE:
             * Processing = "Ready To Ship" + no tracking number
             */
            $splittedStatuses = explode(',', strtolower($orderStatuses));
            if (in_array(strtolower(self::ORDER_STATUS_PROCESSING), $splittedStatuses)) {
                return $query->whereIn('status_custom', $splittedStatuses)
                    ->where(function ($q) {
                        return $q->whereNull('tracking_number')
                        ->orWhere('tracking_number', '=', "");
                    });
            }
            return $query->whereIn('status_custom', $splittedStatuses);
        }
        return;
    }

    /**
     * Get data by print status for datatable.
     * If "status" is "PRINTED", then check if "downloaded_at" is not null.
     * If "status" is "NOT_PRINTED", then check if "downloaded_at" is null.
     *
     * @param  Builder  $query
     * @param  string|null  $orderStatuses
     * @return Builder
     */
    public function scopeByPrintStatus($query, $orderStatus = null)
    {
        if (!empty($orderStatus) and in_array($orderStatus, [
            self::AIRWAY_BILL_STATUS_PRINTED,
            self::AIRWAY_BILL_STATUS_NOT_PRINTED
        ])) {
            if ($orderStatus == self::AIRWAY_BILL_STATUS_PRINTED) {
                return $query->whereNotNull('downloaded_at');
            } else {
                return $query->whereNull('downloaded_at');
            }
        }
        return;
    }

    /**
     * Query to filter by website_id field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int|null $websiteId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByShop($query, $websiteId = null)
    {
        if (! empty($websiteId)) {
            return $query->where('website_id', $websiteId);
        }

        return;
    }

    /**
     * Query to search by from order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     */
    public function scopeSearchDatatable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $shopeePurchaseOrderTable = $this->getTable();
            $shopsTable = (new Shopee())->getTable();
            return $query->where(function (Builder $order) use ($shopeePurchaseOrderTable, $shopsTable, $keyword) {
                $order->where("{$shopeePurchaseOrderTable}.order_id", 'like', "%$keyword%")
                    ->orWhere('total', 'like', "%$keyword%")
                    ->orWhere('payment_method_title', 'like', "%$keyword%")
                    ->orWhere('billing->first_name', 'like', "%$keyword%")
                    ->orWhere('billing->last_name', 'like', "%$keyword%")
                    ->orWhere("{$shopsTable}.shop_name", 'like', "%$keyword%");
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
        $shopeePurchaseOrderTable = $this->getTable();
        $shopeeShopsTable = (new Shopee())->getTable();
        $shopeeParamInitTable = (new ShopeeOrderParamInit())->getTable();
        return $query->join("{$shopeeShopsTable}", "{$shopeeShopsTable}.id", '=', "{$shopeePurchaseOrderTable}.website_id")
            ->join("{$shopeeParamInitTable}", "{$shopeeParamInitTable}.ordersn", '=', "{$shopeePurchaseOrderTable}.order_id");
    }

    /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDataTableWithShopsOnly($query)
    {
        $shopeePurchaseOrderTable = $this->getTable();
        $shopeeShopsTable = (new Shopee())->getTable();
        return $query->join("{$shopeeShopsTable}", "{$shopeeShopsTable}.id", '=', "{$shopeePurchaseOrderTable}.website_id");
    }

    /**
     * Query to filter by date range of `order_date` field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  Date|null  $dateFrom
     * @param  Date|null  $dateTo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrderDateRange($query, $dateFrom = null, $dateTo = null)
    {
        if (empty($dateFrom) OR empty($dateTo)) {
            return;
        }

        $strDateFrom = $dateFrom . ' 00:00:00';
        $strDateTo = $dateTo . ' 23:59:59';

        return $query->whereBetween('order_date', [$strDateFrom, $strDateTo]);
    }

    /**
     * Query to filter by `status` field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status = null)
    {
        if (empty($status)) {
            return;
        }

        return $query->where('status', $status);
    }

    /**
     * Get all `cancel_reason`.
     *
     * @return array
     */
    public static function getAllOrderCancelReasons()
    {
        return [
            self::ORDER_CANCEL_REASON_OUT_OF_STOCK => 'OUT OF STOCK',
            self::ORDER_CANCEL_REASON_CUSTOMER_REQUEST => 'REASON CUSTOMER REQUEST',
            self::ORDER_CANCEL_REASON_UNDELIVERABLE_AREA => 'UNDELIVERABLE AREA',
            self::ORDER_CANCEL_REASON_COD_NOT_SUPPORTED => 'COD NOT SUPPORTED'
        ];
    }


    /**
     * Determine "status_custom".
     */
    public static function determineStatusCustom($status, $tracking_num="") {
        if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
        ])) {
            if (isset($tracking_num) and !empty($tracking_num)) {
                return strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
            } else {
                return strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING);
            }
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_COMPLETED
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_COMPLETED);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_CANCELLED
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_CANCELLED);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_IN_CANCEL
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_IN_CANCEL);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_SHIPPED
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_SHIPPED);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_UNPAID,
            ShopeeOrderPurchase::ORDER_STATUS_INVOICE_PENDING
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_TO_PAY);
        } else if (in_array($status, [
            ShopeeOrderPurchase::ORDER_STATUS_TO_CONFIRM_RECEIVE
        ])) {
            return strtolower(ShopeeOrderPurchase::ORDER_STATUS_TO_CONFIRM_RECEIVE);
        }
        return null;
    }


    /**
     * Get main status schema for order datatable
     *
     * @return array
     */
    public static function getMainStatusSchemaForDatatable($shopId = '')
    {
        return [
            [
                'id' => 'P1',
                'text' => 'To Process',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-arrow-repeat" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_PROCESSING,
                        'text' => 'Processing',
                        'count' => self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_UNVERIFIED,
                        'text' => 'Unverified',
                        'count' => self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId, false)
                    ],
                    [
                        'id' => self::ORDER_STATUS_IN_CANCEL,
                        'text' => 'Cancellation Request',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_IN_CANCEL, $shopId)
                    ]
                ],
                'count' => self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId)
            ],
            [
                'id' => 'P2',
                'text' => 'To Ship',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::AIRWAY_BILL_STATUS_NOT_PRINTED,
                        'text' => 'Ready To Ship',
                        'count' => self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_NOT_PRINTED, $shopId)
                    ],
                    [
                        'id' => self::AIRWAY_BILL_STATUS_PRINTED,
                        'text' => 'Ready To Ship (Printed)',
                        'count' => self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_PRINTED, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE,
                        'text' => 'Warehouse Shipped',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_SHIPPED,
                        'text' => 'Logistics Processed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_RETRY_SHIP,
                        'text' => 'Failed Pickup',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_RETRY_SHIP, $shopId)
                    ]
                ],
                'count' => self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_NOT_PRINTED, $shopId) +
                    self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_PRINTED, $shopId) +
                    self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE, $shopId) +
                    self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED, $shopId) + 
                    self::getStatusSchemaCount(self::ORDER_STATUS_RETRY_SHIP, $shopId)
            ],
        ];
    }


    public static function getMainStatusSchemaForDatatableForShipment($shopId = '')
    {
        return [
            self::getMainStatusSchemaForDatatable()[1]
        ];
    }


    /**
     * Get the number orders under a specific "status_custom".
     */
    public static function getStatusSchemaCount($statusId, $shopId = null)
    {
        $orderStatusCounts = ShopeeOrderPurchase::selectRaw('status_custom')
            ->where('shopee_order_purchases.seller_id', Auth::id())
            ->where('status_custom', 'like', "%$statusId%")
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->where('website_id', $shopId) : $query;
            });
        /**
         * If the custom status is "processing" and the orders have tracking number, then they are supposed to
         * be shown while filtering "Processing". For "init" to Shopee against an order may return "tracking_number" and
         * its stored in db but "status" of the order is updated by webhook. If webhook is delayed then in order datatable
         * orders with "processing" custom status with "tracking_number" are shown in table which breaks the condition for
         * "processing"
         * NOTE:
         * Processing = "Ready To Ship" + no tracking number
         */
        if ($statusId == self::ORDER_STATUS_PROCESSING) {
            $orderStatusCounts = $orderStatusCounts->where(function ($q) {
                $q->whereNull('tracking_number')
                ->orWhere('tracking_number', '=', "");
            });
        } else if ($statusId == self::ORDER_STATUS_SHIPPED) {
            $orderStatusCounts = $orderStatusCounts->where("status_custom", "not like", "%".self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE."%");
        }
        $orderStatusCounts = $orderStatusCounts->joinedDataTable();

        return $orderStatusCounts->count();
    }


    /**
     * Count the number orders which have passed the 1 hour mark.
     */
    public static function getVerifiedOrderSchemaCount($statusId, $shopId = null, $verified=true)
    {
        $orderStatusCounts = 0;
        /**
         * If the custom status is "processing" and the orders have tracking number, then they are supposed to
         * be shown while filtering "Processing". For "init" to Shopee against an order may return "tracking_number" and
         * its stored in db but "status" of the order is updated by webhook. If webhook is delayed then in order datatable
         * orders with "processing" custom status with "tracking_number" are shown in table which breaks the condition for
         * "processing"
         * NOTE:
         * Processing = "Ready To Ship" + no tracking number
         */
        if ($statusId == self::ORDER_STATUS_PROCESSING) {
            $orderStatusCounts = ShopeeOrderPurchase::selectRaw('status_custom')
                ->where('shopee_order_purchases.seller_id', Auth::id())
                ->where('status_custom', 'like', "%$statusId%")
                ->where(function ($query) use ($shopId) {
                    return $shopId ? $query->where('website_id', $shopId) : $query;
                })
                ->where(function ($q) {
                    $q->whereNull('tracking_number')
                    ->orWhere('tracking_number', '=', "");
                })
                ->where(function ($q) use ($verified) {
                    $now = Carbon::now()->subHours(1)->toDateTimeString();
                    if ($verified) {
                        $q->where('order_date', '<', $now);
                    } else {
                        $q->where('order_date', '>', $now);
                    }
                })
                ->joinedDataTable()
                ->count();
        }

        return $orderStatusCounts;
    }


    /**
     * Count total number orders for a collection of statuses.
     */
    public static function getMultipleStatusSchemaCount($statusIds, $shopId = null)
    {
        if (empty($statusIds)) {
            return 0;
        }
        $splittedStatuses = explode(',', $statusIds);
        if (sizeof($splittedStatuses) == 1) {
            return self::getStatusSchemaCount($splittedStatuses[0], $shopId = null);
        }

        $count = 0;
        foreach($splittedStatuses as $status) {
            if ($status == self::ORDER_STATUS_PROCESSING) {
                $count += self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId);
            } else if ($status == self::ORDER_STATUS_UNVERIFIED) {
                $count += self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId, false);
            } else if ($status == self::ORDER_STATUS_RETRY_SHIP) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_RETRY_SHIP, $shopId);
            } else if ($status == self::ORDER_STATUS_RETRY_SHIP) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_IN_CANCEL, $shopId);
            } else if ($status == self::AIRWAY_BILL_STATUS_NOT_PRINTED) {
                $count += self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_NOT_PRINTED, $shopId);
            } else if ($status == self::AIRWAY_BILL_STATUS_PRINTED) {
                $count += self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_PRINTED, $shopId);
            } else if ($status == self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE, $shopId);
            } else if ($status == self::ORDER_STATUS_SHIPPED) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED, $shopId);
            }
        }

        return $count;
    }


    /**
     * This is used to show orders whether they have downloaded airway bill executed by the user.
     * NOTE:
     * Configured for returning orders havint "READY_TO_SHIP_AWB" as "custom_status".
     */
    public static function getAirwayBillPrintStatusCount($status="printed", $shopId = null, $statusId = null)
    {
        if (!in_array($status, [
            self::AIRWAY_BILL_STATUS_PRINTED,
            self::AIRWAY_BILL_STATUS_NOT_PRINTED,
        ])) {
            return 0;
        }

        $orderPrintStatusCounts = ShopeeOrderPurchase::selectRaw('downloaded_at')
            ->where('seller_id', Auth::id())
            ->where('status_custom', self::ORDER_STATUS_READY_TO_SHIP_AWB)
            ->where(function ($query) use ($status) {
                if ($status == self::AIRWAY_BILL_STATUS_PRINTED) {
                    return $query->whereNotNull('downloaded_at');
                } else {
                    return $query->whereNull('downloaded_at');
                }
            })
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->where('website_id', $shopId) : '';
            })
            ->count();

        return $orderPrintStatusCounts;
    }


    /**
     * Get secondary status schema for order datatable
     *
     * @return array
     */
    public static function getSecondaryStatusSchemaForDatatable($shopId = '')
    {
        return [
            [
                'id' => 'P3',
                'text' => 'To Pay',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-currency-dollar" viewBox="0 0 16 16"><path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_TO_PAY,
                        'text' => 'Pending',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_TO_PAY, $shopId)
                    ],
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_TO_PAY, $shopId),
            ],
            [
                'id' => 'P4',
                'text' => 'Cancelled',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_CANCELLED,
                        'text' => 'Cancelled',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCELLED, $shopId)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCELLED, $shopId),
            ],
            [
                'id' => 'P5',
                'text' => 'Completed',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_COMPLETED,
                        'text' => 'Completed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_COMPLETED, $shopId)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_COMPLETED, $shopId)
            ],
        ];
    }


    /**
     * Shipping methods for Shopee used for filtering orders.
     */
    public static function getShippingMethodForShopee()
    {
        return [
            "pickup"    => "Pickup",
            "dropoff_branch_id" => "Dropoff Branch (ems)",
            "dropoff_tracking_no" => "Dropoff Tracking No (ems)"
        ];
    }


    /**
     * Shipping methods for Shopee used for filtering orders.
     */
    public static function getShippingMethodForShopeeWithOrdersCount($shopId = null)
    {
        return [
            [
                "id"    => "pickup",
                "text"  => "Pickup",
                "count" => self::getStatusSchemaCountForShippingMethod("pickup", $shopId)
            ],
            [
                "id"    => "dropoff_branch_id",
                "text"  => "Dropoff Branch (ems)",
                "count" => self::getStatusSchemaCountForShippingMethod("dropoff_branch_id", $shopId)
            ],
            [
                "id"    => "dropoff_tracking_no",
                "text"  => "Dropoff Tracking No (ems)",
                "count" => self::getStatusSchemaCountForShippingMethod("dropoff_tracking_no", $shopId)
            ]
        ];
    }


    /**
     * Shipping methods for Shopee used for filtering orders with the count of orders under each method.
     * NOTE:
     * Only work for orders having passed the 1 hour mark.
     */
    public static function getStatusSchemaCountForShippingMethod($method, $shopId = null)
    {
        if (!in_array($method, ["pickup", "dropoff_branch_id", "dropoff_tracking_no"])) {
            return 0;
        }
        $shopeePurchaseOrderTable = (new ShopeeOrderPurchase())->getTable();
        $shopeeParamInitTable = (new ShopeeOrderParamInit())->getTable();
        $shopeeShopsTable = (new Shopee())->getTable();

        $orderStatusCounts = ShopeeOrderPurchase::selectRaw("{$shopeePurchaseOrderTable}.order_id")
            ->join("{$shopeeParamInitTable}", "{$shopeeParamInitTable}.ordersn", '=', "{$shopeePurchaseOrderTable}.order_id");
        if (isset($shopId)) {
            $orderStatusCounts = $orderStatusCounts->where(function ($query) use ($shopId, $shopeePurchaseOrderTable) {
                return $shopId ? $query->where("{$shopeePurchaseOrderTable}.website_id", $shopId) : $query;
            });
        } else {
            $orderStatusCounts = $orderStatusCounts->join("{$shopeeShopsTable}", "{$shopeeShopsTable}.id", '=', "{$shopeePurchaseOrderTable}.website_id");
        }
        $orderStatusCounts = $orderStatusCounts->where("{$shopeePurchaseOrderTable}.status_custom", strtolower(self::ORDER_STATUS_PROCESSING))
            ->where("{$shopeePurchaseOrderTable}.seller_id", Auth::id())
            ->where(function ($query) use ($shopeePurchaseOrderTable) {
                return $query->whereNull("{$shopeePurchaseOrderTable}.tracking_number")
                ->orWhere("{$shopeePurchaseOrderTable}.tracking_number", '=', "");
            })
            ->where("{$shopeePurchaseOrderTable}.order_date", '<', Carbon::now()->subHours(1)->toDateTimeString());
        if ($method == "pickup") {
            $orderStatusCounts = $orderStatusCounts->where("{$shopeeParamInitTable}.pickup", "like", "%address_id%");
        } else if ($method == "dropoff_branch_id") {
            $orderStatusCounts = $orderStatusCounts->where("{$shopeeParamInitTable}.dropoff", "like", "%branch_id%");
        } else if ($method == "dropoff_tracking_no") {
            $orderStatusCounts = $orderStatusCounts->where("{$shopeeParamInitTable}.dropoff", "like", "%tracking_no%");
        }
        return $orderStatusCounts->count();
    }


    /**
     * Used for testing sql.
     */
    public static function logSqlQuery($builder) {
        try {
            $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
            // $query = vsprintf($query, $builder->getBindings());
            Log::debug($query);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public static function getProductDetails($item_sku) {
        $productDetails = ShopeeProduct::where('product_code', $item_sku)->first();
        return $productDetails;
    }
}
