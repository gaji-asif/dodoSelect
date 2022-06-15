<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class LazadaOrderPurchase extends Model
{
    use HasFactory;

    /**
     * Avaialable Lazada order statuses.
     * https://open.lazada.com/doc/api.htm?spm=a2o9m.11193487.0.0.3ac413feIE9yU5#/api?cid=8&path=/orders/get
     */
    CONST ORDER_STATUS_UNPAID = 'UNPAID';
    CONST ORDER_STATUS_PENDING = 'PENDING';
    CONST ORDER_STATUS_PACKED = 'PACKED';
    CONST ORDER_STATUS_READY_TO_SHIP_PENDING = 'READY_TO_SHIP_PENDING';
    CONST ORDER_STATUS_READY_TO_SHIP = 'READY_TO_SHIP';
    CONST ORDER_STATUS_SHIPPED = 'SHIPPED';
    CONST ORDER_STATUS_DELIVERED = 'DELIVERED';
    CONST ORDER_STATUS_FAILED = 'FAILED';
    CONST ORDER_STATUS_CANCELLED = 'CANCELED';
    CONST ORDER_STATUS_TO_PACK = 'TOPACK';
    CONST ORDER_STATUS_TO_SHIP = 'TOSHIP';
    CONST ORDER_STATUS_RETURNED = 'RETURNED';
    CONST ORDER_STATUS_FAILED_DELIVERY = 'FAILED_DELIVERY';
    CONST ORDER_STATUS_LOST_BY_3PI = 'LOST_BY_3PI';
    CONST ORDER_STATUS_DAMAGED_BY_3PI = 'DAMAGED_BY_3PI';
    CONST ORDER_STATUS_SHIPPED_BACK = 'SHIPPED_BACK';
    CONST ORDER_STATUS_SHIPPED_BACK_SUCCESS = 'SHIPPED_BACK_SUCCESS';
    CONST ORDER_STATUS_SHIPPED_BACK_FAILED = 'SHIPPED_BACK_FAILED';
    CONST ORDER_STATUS_PACKAGE_SCRAPPED = 'PACKAGE_SCRAPPED';
    
    
    /**
     * Airway bill print status
     */
    CONST AIRWAY_BILL_STATUS_PRINTED = "PRINTED";
    CONST AIRWAY_BILL_STATUS_NOT_PRINTED = "NOT_PRINTED";


    /**
     * Custom status. 
     */
    CONST ORDER_STATUS_PROCESSING = 'PROCESSING';
    CONST ORDER_STATUS_TO_PAY = 'TO_PAY';
    CONST ORDER_STATUS_READY_TO_SHIP_AWB = 'READY_TO_SHIP_AWB';
    CONST ORDER_STATUS_UNVERIFIED = 'UNVERIFIED';
    CONST ORDER_STATUS_SHIPPED_TO_WEARHOUSE = 'SHIPPED_TO_WAREHOUSE';
    CONST ORDER_STATUS_CANCEL = 'CANCELED';
    CONST ORDER_STATUS_COMPLETED = 'COMPLETED';


    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'lazada_order_purchases';


    /**
     * Get all `status` for Lazadaorders.
     *
     * @return array
     */
    public static function getAllOrderStatus()
    {
        return [
            self::ORDER_STATUS_UNPAID => 'UNPAID',
            self::ORDER_STATUS_PENDING => 'PENDING',
            self::ORDER_STATUS_PACKED => 'PACKED',
            self::ORDER_STATUS_READY_TO_SHIP => 'READY TO SHIP',
            self::ORDER_STATUS_READY_TO_SHIP_PENDING => 'READY TO SHIP PENDING',
            self::ORDER_STATUS_SHIPPED => 'SHIPPED',
            self::ORDER_STATUS_DELIVERED => 'DELIVERED',
            self::ORDER_STATUS_FAILED => 'FAILED',
            self::ORDER_STATUS_COMPLETED => 'COMPLETED',
            self::ORDER_STATUS_CANCELLED => 'CANCELED',
            self::ORDER_STATUS_TO_PACK => 'TOPACK',
            self::ORDER_STATUS_TO_SHIP => 'TOSHIP',
            self::ORDER_STATUS_RETURNED => 'CANCELED',
            self::ORDER_STATUS_FAILED_DELIVERY => 'CANCELED',
            self::ORDER_STATUS_SHIPPED_BACK => 'CANCELED',
            self::ORDER_STATUS_SHIPPED_BACK_SUCCESS => 'CANCELED',
            self::ORDER_STATUS_SHIPPED_BACK_FAILED => 'CANCELED',
            self::ORDER_STATUS_LOST_BY_3PI => 'CANCELED',
            self::ORDER_STATUS_DAMAGED_BY_3PI => 'CANCELED',
            self::ORDER_STATUS_PACKAGE_SCRAPPED => 'CANCELED'
        ];
    }


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
        return $this->hasMany(LazadaOrderPurchaseItem::class,'order_id','id')->with('product');
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
     * Get all `status_custom` for Lazadaorders.
     *
     * @return array
     */
    public static function getAllOrderStatusCustom()
    {
        return [
            self::ORDER_STATUS_TO_PAY => 'PENDING',
            self::ORDER_STATUS_PENDING => 'PENDING',
            self::ORDER_STATUS_PROCESSING => 'PROCESSING',
            self::ORDER_STATUS_PACKED => 'PACKED',
            self::ORDER_STATUS_READY_TO_SHIP_PENDING => 'READY TO SHIP PENDING',
            self::ORDER_STATUS_READY_TO_SHIP => 'READY TO SHIP',
            self::ORDER_STATUS_SHIPPED => 'LOGISTICS PROCESSED',
            self::ORDER_STATUS_DELIVERED => 'COMPLETED',
            self::ORDER_STATUS_CANCELLED => 'CANCELED',
            self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE => 'WAREHOUSE SHIPPED',
            self::ORDER_STATUS_TO_PACK => 'TO PACK',
            self::ORDER_STATUS_TO_PAY => 'TO PAY',
        ];
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
            $splittedStatuses = explode(',', strtolower($orderStatuses));
            return $query->whereIn('status_custom', $splittedStatuses);
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
            $lazadaPurchaseOrderTable = $this->getTable();
            $shopsTable = (new Lazada())->getTable();
            return $query->where(function (Builder $order) use ($lazadaPurchaseOrderTable, $shopsTable, $keyword) {
                $order->where("{$lazadaPurchaseOrderTable}.order_id", 'like', "%$keyword%")
                    ->orWhere('price', 'like', "%$keyword%")
                    ->orWhere('payment_method_title', 'like', "%$keyword%")
                    ->orWhere('customer_first_name', 'like', "%$keyword%")
                    ->orWhere('customer_last_name', 'like', "%$keyword%")
                    ->orWhere("{$shopsTable}.shop_name", 'like', "%$keyword%");
            });
        }
        return;
    }


    /**
     * Determine "status_custom".
     */
    public static function determineStatusCustom($statuses) {
        if(is_string($statuses) and self::isJson($statuses)) {
            $statuses = json_decode($statuses);
        }
        $status = strtoupper(end($statuses));
        if (in_array($status, [
            self::ORDER_STATUS_READY_TO_SHIP,
        ])) {
            return strtolower(self::ORDER_STATUS_READY_TO_SHIP);
        } else if (in_array($status, [
            self::ORDER_STATUS_DELIVERED
        ])) {
            return strtolower(self::ORDER_STATUS_DELIVERED);
        } else if (in_array($status, [
            self::ORDER_STATUS_CANCELLED,
            self::ORDER_STATUS_RETURNED,
            self::ORDER_STATUS_FAILED_DELIVERY,
            self::ORDER_STATUS_SHIPPED_BACK,
            self::ORDER_STATUS_SHIPPED_BACK_SUCCESS,
            self::ORDER_STATUS_SHIPPED_BACK_FAILED,
            self::ORDER_STATUS_LOST_BY_3PI,
            self::ORDER_STATUS_DAMAGED_BY_3PI,
            self::ORDER_STATUS_PACKAGE_SCRAPPED
        ])) {
            return strtolower(self::ORDER_STATUS_CANCELLED);
        } else if (in_array($status, [
            self::ORDER_STATUS_SHIPPED
        ])) {
            return strtolower(self::ORDER_STATUS_SHIPPED);
        } else if (in_array($status, [
            self::ORDER_STATUS_UNPAID
        ])) {
            return strtolower(self::ORDER_STATUS_UNPAID);
        } else if (in_array($status, [
            self::ORDER_STATUS_TO_PACK
        ])) {
            return strtolower(self::ORDER_STATUS_TO_PACK);
        } else if (in_array($status, [
            self::ORDER_STATUS_TO_PAY
        ])) {
            return strtolower(self::ORDER_STATUS_TO_PAY);
        } else if (in_array($status, [
            self::ORDER_STATUS_PENDING,
            self::ORDER_STATUS_PACKED,
            self::ORDER_STATUS_READY_TO_SHIP_PENDING
        ])) {
            return strtolower(self::ORDER_STATUS_PROCESSING);
        }
        return null;
    }


    /**
     * Get derived status.
     */
    public static function getDerivedStatus($statuses) 
    {
        if(is_string($statuses) and self::isJson($statuses)) {
            $statuses = json_decode($statuses);
        }
        $status = strtoupper(end($statuses));
        if (array_key_exists($status, self::getAllOrderStatus())) {
            return self::getAllOrderStatus()[$status];
        }
        return null;
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
     * Check if json.
     * 
     * @param string $string
     */
    public static function isJson($string) 
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
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
                    ]
                ],
                'count' => self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId) + 
                    self::getVerifiedOrderSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId, false)
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
                    ]
                ],
                'count' => self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_NOT_PRINTED, $shopId) + 
                    self::getAirwayBillPrintStatusCount(self::AIRWAY_BILL_STATUS_PRINTED, $shopId) +
                    self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE, $shopId) + 
                    self::getStatusSchemaCount(self::ORDER_STATUS_SHIPPED, $shopId)
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
                        'id' => self::ORDER_STATUS_DELIVERED,
                        'text' => 'Completed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_DELIVERED, $shopId)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_DELIVERED, $shopId)
            ],
        ];
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
            } else if ($status == self::ORDER_STATUS_DELIVERED) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_DELIVERED, $shopId);
            } else if ($status == self::ORDER_STATUS_DELIVERED) {
                $count += self::getStatusSchemaCount(self::ORDER_STATUS_CANCELLED, $shopId);
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
     * Get the number orders under a specific "status_custom".
     */
    public static function getStatusSchemaCount($statusId, $shopId = null)
    {
        $orderStatusCounts = LazadaOrderPurchase::selectRaw('status_custom')
            ->where('lazada_order_purchases.seller_id', Auth::id())
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->where('website_id', $shopId) : $query;
            });
        if ($statusId == self::ORDER_STATUS_PENDING) {
            $orderStatusCounts = $orderStatusCounts->whereDerivedStatus(self::ORDER_STATUS_PENDING);
        } else if ($statusId == self::ORDER_STATUS_PACKED) {
            $orderStatusCounts = $orderStatusCounts->whereDerivedStatus(self::ORDER_STATUS_PACKED);
        } else if ($statusId == self::ORDER_STATUS_SHIPPED) {
            $orderStatusCounts = $orderStatusCounts->where("status_custom", "not like", "%".self::ORDER_STATUS_SHIPPED_TO_WEARHOUSE."%")
                ->where('status_custom', 'like', "%$statusId%");
        } else {
            $orderStatusCounts = $orderStatusCounts->where('status_custom', 'like', "%$statusId%");
        }
        $orderStatusCounts = $orderStatusCounts->joinedDataTable();

        return $orderStatusCounts->count();
    }


    /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDataTable($query)
    {
        $lazadaPurchaseOrderTable = $this->getTable();
        $lazadaShopsTable = (new Lazada())->getTable();
        return $query->join("{$lazadaShopsTable}", "{$lazadaShopsTable}.id", '=', "{$lazadaPurchaseOrderTable}.website_id")
            ->where(function ($query) use ($lazadaPurchaseOrderTable) {
                $query->whereNotNull("{$lazadaPurchaseOrderTable}.order_item_ids")
                    ->orWhere("{$lazadaPurchaseOrderTable}.order_item_ids", "!=", "");
            });
    }
    
    
    /**
     * Count the number orders which have passed the 1 hour mark.
     */
    public static function getVerifiedOrderSchemaCount($statusId, $shopId = null, $verified=true)
    {
        $orderStatusCounts = 0;
        if ($statusId == self::ORDER_STATUS_PROCESSING) {
            $orderStatusCounts = LazadaOrderPurchase::selectRaw('status_custom')
                ->where('lazada_order_purchases.seller_id', Auth::id())
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
     * This is used to show orders whether they have downloaded airway bill executed by the user.
     */
    public static function getAirwayBillPrintStatusCount($status="printed", $shopId = null, $statusId = null)
    {
        if (!in_array($status, [
            self::AIRWAY_BILL_STATUS_PRINTED,
            self::AIRWAY_BILL_STATUS_NOT_PRINTED,
        ])) {
            return 0;
        }

        $orderPrintStatusCounts = LazadaOrderPurchase::selectRaw('downloaded_at')
            ->where('seller_id', Auth::id())
            // ->where('status_custom', self::ORDER_STATUS_READY_TO_SHIP_AWB)
            ->where('status_custom', self::ORDER_STATUS_READY_TO_SHIP)
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


    /**
     * Shipping methods for Shopee used for filtering orders.
     */
    public static function getShippingMethodForShopeeWithOrdersCount($shopId = null) 
    {
        return [
            [
                "id"    => "dropship",
                "text"  => "Dropship",
                "count" => self::getStatusSchemaCountForShippingMethod("dropship", $shopId)
            ]
        ];
    }


    /**
     * Shipping methods for Shopee used for filtering orders with the count of orders under each method.
     */
    public static function getStatusSchemaCountForShippingMethod($method, $shopId = null)
    {
        if (!in_array($method, ["dropship"])) {
            return 0;
        }
        $lazadaPurchaseOrderTable = (new LazadaOrderPurchase())->getTable();
        $orderStatusCounts = LazadaOrderPurchase::selectRaw("{$lazadaPurchaseOrderTable}.order_id")
            ->where("{$lazadaPurchaseOrderTable}.status_custom", strtolower(self::ORDER_STATUS_PROCESSING))
            ->where("{$lazadaPurchaseOrderTable}.seller_id", Auth::id())
            ->where(function ($query) use ($shopId, $lazadaPurchaseOrderTable) {
                return $shopId ? $query->where("{$lazadaPurchaseOrderTable}.website_id", $shopId) : '';
            });
        return $orderStatusCounts->count();
    }


    public function getCustomerFirstNameAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getCustomerLastNameAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getTaxCodeAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getNationalRegistrationNumberAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getBillingAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getShippingAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getBranchNumberAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getRemarksAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getVoucherAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getVoucherCodeAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getVoucherPlatformAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getVoucherSellerAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getDeliveryInfoAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getGiftMessageAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getPaymentMethodAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }


    public function getPaymentMethodTitleAttribute($value)
    {
        try {
            return Crypt::decrypt($value);
        } catch (Exception $exception) {
            return $value;
        }
    }

    /**
     * Get shop data
     *
     * @return BelongsTo
     */
    public function lazada()
    {
        return $this->belongsTo(Lazada::class, 'website_id', 'id')->withDefault();
    }
}
