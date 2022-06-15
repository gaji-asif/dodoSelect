<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WooOrderPurchase extends Model
{
    use HasFactory;

    /**
     * Define status field value
     *
     * @var mixed
     */
    CONST ORDER_STATUS_PENDING = 'pending';
    CONST ORDER_STATUS_PROCESSING = 'processing';
    CONST ORDER_STATUS_PROCESSED = 'processed';
    CONST ORDER_STATUS_ON_HOLD = 'on-hold';
    CONST ORDER_STATUS_PRE_ORDERED = 'pre-ordered';
    CONST ORDER_STATUS_READY_TO_SHIP = 'ready-to-ship';
    CONST ORDER_STATUS_REFUNDED = 'refunded';
    CONST ORDER_STATUS_CANCEL = 'cancelled';
    CONST ORDER_STATUS_COMPLETED = 'completed';

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
     * Define table name
     *
     * @var string
     */
    protected $table = 'woo_order_purchases';

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'str_order_status',
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
     * Relationship to `woo_order_purchase_details` table
     *
     * @return mixed
     */
    public function orderProductDetails()
    {
        return $this->hasMany(WooOrderPurchaseDetail::class,'order_purchase_id','id')->with('product');
    }


    /**
     * Relationship to `shipments` table
     *
     * @return mixed
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'order_id', 'order_id')->withDefault([ 'id' => 0, 'shipment_date' => null ]);
    }
    
    
    /**
     * Relationship to `shipments` table
     *
     * @return mixed
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class,'order_id','order_id');
    }


    


      /**
     * Relationship to `shipment_products` table
     *
     * @return mixed
     */
    public function shipmentProducts()
    {
        return $this->hasMany(ShipmentProduct::class,'order_id','order_id')->with('shipments');
    }


     /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id')->withDefault();
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
    public function getStrWooOrderStatusAttribute()
    {
        $wooOrderStatuses = self::getAllOrderStatus();
        $wooOrderStatusAttribute = $this->attributes['status'] ?? '';

        return $wooOrderStatuses[$wooOrderStatusAttribute] ?? 'Unknown';
    }

    /**
     * By multiple status ids from datatable
     *
     * @param  Builder  $query
     * @param  string|null  $orderStatuses
     * @return Builder
     */
    public function scopeByMultipleOrderStatus($query, $orderStatuses = null)
    {
        
        $shipmentTable = (new Shipment())->getTable();
        $wooOrderPurchaseTable = (new ShipmentProduct())->getTable();


        if (!empty($orderStatuses)) {
            $splittedStatuses = explode(',', $orderStatuses);
            if(in_array(Shipment::SHIPMENT_STATUS_READY_TO_SHIP,$splittedStatuses) 
            || in_array(Shipment::SHIPMENT_STATUS_SHIPPED,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_PENDING_STOCK,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_CANCEL,$splittedStatuses)
            ){
                return $query->whereIn('shipment_status', $splittedStatuses);
            }else{
                return $query->whereIn('status', $splittedStatuses);
            }
            
            
        }

        return;
    }

    /**
     * Query to search by from order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $wooPurchaseOrderTable = $this->getTable();
            $shopsTable = (new Shop())->getTable();

            return $query->where(function (Builder $order) use ($wooPurchaseOrderTable, $shopsTable, $keyword) {
                $order->where("{$wooPurchaseOrderTable}.order_id", 'like', "%$keyword%")
                    ->orWhere('total', 'like', "%$keyword%")
                    ->orWhere('payment_method_title', 'like', "%$keyword%")
                    ->orWhere('billing->first_name', 'like', "%$keyword%")
                    ->orWhere('billing->last_name', 'like', "%$keyword%")
                    ->orWhere("{$shopsTable}.name", 'like', "%$keyword%");
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
        $wooPurchaseOrderTable = $this->getTable();
        $wooShopsTable = (new WooShop())->getTable();
        $shopsTable = (new Shop())->getTable();

        return $query->join("{$wooShopsTable}", "{$wooShopsTable}.id", '=', "{$wooPurchaseOrderTable}.website_id")
            ->join("{$shopsTable}", "{$shopsTable}.id", '=', "{$wooShopsTable}.shop_id");
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
            self::ORDER_STATUS_PROCESSED => 'Processed',
            self::ORDER_STATUS_ON_HOLD => 'On Hold',
            self::ORDER_STATUS_READY_TO_SHIP => 'Ready to Ship',
            self::ORDER_STATUS_PRE_ORDERED => 'Pre-ordered',
            self::ORDER_STATUS_REFUNDED => 'Refunded',
            self::ORDER_STATUS_CANCEL => 'Cancelled',
            self::ORDER_STATUS_COMPLETED => 'Completed'
        ];
    }

    /**
     * Get statuses for 'To Process'
     *
     * @return array
     */
    public static function getStatusesForProcess()
    {
        return [
            self::ORDER_STATUS_PROCESSING => 'Processing',
            self::ORDER_STATUS_PRE_ORDERED => 'Pre-ordered',
        ];
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
                'id' => self::ORDER_STATUS_PROCESSING,
                'text' => 'To Process',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-arrow-repeat" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_PROCESSING,
                        'text' => 'Processing',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId)
                    ],
                    
                     [
                        'id' => self::ORDER_STATUS_PROCESSED,
                        'text' => 'Processed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSED, $shopId)
                    ],
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSING, $shopId) 
            ],
            [
                'id' => self::ORDER_STATUS_READY_TO_SHIP,
                'text' => 'To Ship ',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>',
                'sub_status' => [
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP,
                        'text' => 'Ready to Ship',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED,
                        'text' => 'Ready to Ship Printed',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_SHIPPED,
                        'text' => 'Shipped',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_SHIPPED, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_PENDING_STOCK,
                        'text' => 'Waiting For Stock',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_CANCEL,
                        'text' => 'CANCELLED',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $shopId)
                    ],
                ],
                'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $shopId) 
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $shopId)
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $shopId)
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $shopId)
            ],
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
                        'id' => self::ORDER_STATUS_PENDING,
                        'text' => 'Pending',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PENDING, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_ON_HOLD,
                        'text' => 'On Hold',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_ON_HOLD, $shopId)
                    ],
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PENDING, $shopId) + self::getStatusSchemaCount(self::ORDER_STATUS_ON_HOLD, $shopId),
            ],
            [
                'id' => 'P4',
                'text' => 'Cancelled',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_CANCEL,
                        'text' => 'Cancelled',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCEL, $shopId)
                    ],
                    [
                        'id' => self::ORDER_STATUS_REFUNDED,
                        'text' => 'Refunded',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_REFUNDED, $shopId)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCEL, $shopId) + self::getStatusSchemaCount(self::ORDER_STATUS_REFUNDED, $shopId),
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
     * Get main status schema for order datatable
     *
     * @return array
     */
    public static function getShipmentStatusSchemaForDatatable($shopId = '')
    {
        return [
            [
                'id' => self::ORDER_STATUS_READY_TO_SHIP,
                'text' => 'To Ship ',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>',
                'sub_status' => [
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP,
                        'text' => 'Ready to Ship',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED,
                        'text' => 'Ready to Ship Printed',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_SHIPPED,
                        'text' => 'Shipped',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_SHIPPED, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_PENDING_STOCK,
                        'text' => 'Waiting For Stock',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $shopId)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_CANCEL,
                        'text' => 'CANCELLED',
                        'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $shopId)
                    ],
                ],
                'count' => self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $shopId) 
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $shopId)
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_SHIPPED, $shopId)
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $shopId)
                + self::getShipmentStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $shopId)
            ],
        ];
    }


    public static function getStatusSchemaCount($statusId, $shopId = null)
    {
        $orderStatusCounts = WooOrderPurchase::selectRaw('status, COUNT(id) AS total')
            ->where('woo_order_purchases.seller_id', Auth::id())
            ->where('status', $statusId)
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->where('website_id', $shopId) : '';
            })
            ->groupBy('status')
            ->count();

        return $orderStatusCounts;
    }

    public static function getOrdersFromShop($statusId, $shopId = null)
    {
        $orderCounts = WooOrderPurchase::selectRaw('status, COUNT(id) AS total')
            ->where('woo_order_purchases.seller_id', Auth::id())
            ->where('status', $statusId)
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->where('website_id', $shopId) : '';
            })
            ->groupBy('status')
            ->count();

        return $orderCounts;
    }

    public static function getShipmentStatusSchemaCount($statusId, $shopId = null)
    {
        
            $wooPurchaseOrderTable = (new WooOrderPurchase())->getTable();
            $shipmentTable = (new Shipment())->getTable();

            $seller_id = Auth::id();
            $shipment_for = Shipment::SHIPMENT_FOR_WOO;

            $orderStatusCounts = DB::select(DB::raw("select count(*) as total from 
                                    (select $wooPurchaseOrderTable.* from $wooPurchaseOrderTable
                                    inner join $shipmentTable on $shipmentTable.`order_id` = $wooPurchaseOrderTable.`order_id` 
                                    where $wooPurchaseOrderTable.`seller_id` = $seller_id 
                                    and `shipment_status` in ($statusId) 
                                    and $shipmentTable.`shipment_for` = $shipment_for group by $shipmentTable.`id` order by `id` desc) count_row_table
                                "));
     

        return $orderStatusCounts[0]->total;
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

    public static function getShipmentDataStatusWise($status){
        $today = Carbon::today()->toDateString();
        if($status == 'today'){
            
            $data = WooOrderPurchase::select('woo_order_purchases.*', 'shops.name', 'shops.logo')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->whereDate('woo_order_purchases.order_date',$today)
                    ->where('woo_order_purchases.seller_id', '=', Auth::user()->id)
                    ->get();
        }
        if($status == 'late'){

            $data = WooOrderPurchase::select('woo_order_purchases.*', 'shops.name', 'shops.logo')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->whereDate('woo_order_purchases.order_date','<',$today)
                    ->where('woo_order_purchases.seller_id', '=', Auth::user()->id)
                    ->get();
        }
        if($status == 'future'){
            $data = WooOrderPurchase::select('woo_order_purchases.*', 'shops.name', 'shops.logo')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->whereDate('woo_order_purchases.order_date','>',$today)
                    ->where('woo_order_purchases.seller_id', '=', Auth::user()->id)
                    ->get();
        }
        return $data;
     }


     public static function getShipmentDataShipStatusWise($shipment_status){
        $data = WooOrderPurchase::select('woo_order_purchases.*', 'shops.name', 'shops.logo')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->where('woo_order_purchases.seller_id', '=', Auth::user()->id)
                    ->where('status', '=', $shipment_status)
                    ->get();

        return $data;
     }

     public static function getShipmentDataShipNoWise($shipment_no){
        $data = WooOrderPurchase::select('woo_order_purchases.*', 'shops.name', 'shops.logo')
                ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                ->where('woo_order_purchases.seller_id', '=', Auth::user()->id)
                ->where('woo_order_purchases.id', '=', $shipment_no)
                ->get();
        return $data;
     }


     public static function getAllQtyByStatus($shop_id,$order_id, $product_id, $shipment_status){
        $data = DB::select(DB::raw("SELECT 
            SUM(quantity) as quantity FROM `shipment_products`
            LEFT JOIN shipments ON shipments.id=shipment_products.shipment_id
            WHERE shipments.shop_id=$shop_id 
            AND shipments.order_id=$order_id
            AND shipments.shipment_status=$shipment_status
            AND shipment_products.product_id=$product_id
            GROUP BY `product_id`,`variation_id`
        "));

        if(!empty($data)){
            return $data[0]->quantity;
        }
        else{
            return 0;
        }

    }

    public static function getProductWiseShipmentTotal($shop_id,$order_id)
    {
        return DB::select(DB::raw("SELECT 
            product_id,variation_id,SUM(quantity) as total_shipped FROM `shipment_products`
            LEFT JOIN shipments ON shipments.id=shipment_products.shipment_id
            WHERE shipments.shop_id=$shop_id AND shipments.order_id=$order_id
            GROUP BY `product_id`,`variation_id`
        "));
    }

        /**
     *  By Order Shipment Status ids from datatable
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $orderStatusId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByOrderShipmentStatus($query, $orderStatusId = null)
    {
        if (!empty($orderStatusId)) {
            $shipmentTable = (new Shipment())->getTable();
            $wooOrderPurchaseTable = (new WooOrderPurchase())->getTable();
            //echo $orderStatusId;
            //if($orderStatusId==Shipment::SHIPMENT_STATUS_READY_TO_SHIP ){
            $shipment_status_id = Shipment::SHIPMENT_STATUS_READY_TO_SHIP;
                return $query->join("{$shipmentTable}", "{$shipmentTable}.order_id", '=', "{$wooOrderPurchaseTable}.order_id")
                ->where("{$shipmentTable}.shipment_for", Shipment::SHIPMENT_FOR_WOO);
                
           // }else{
           //     return ;
           // }
            
        }

        return;
    }

    
    public static function getDetails($order_id){
         $orderDetails = WooOrderPurchase::where('order_id',$order_id)->where('seller_id',Auth::user()->id)->first();

         $channelName = '';
         if(isset($orderDetails->channel_id)){
            $channelDetails = Channel::where('id',$orderDetails->channel_id)->first();
            if(isset($channelDetails->name)){
            $channelName = $channelDetails->name;
        }
            else{
                $channelName = '';
            }
        }

        
        $shipmentMethod = '';
        $shipping_lines = json_decode($orderDetails->shipping_lines);
        if (!empty($shipping_lines)) {
            $shipmentMethod = $shipping_lines[0]->method_title;
        }
        

        $data = [
            'channelName'=>$channelName,
            'shipmentMethod'=>$shipmentMethod
        ];

        return $data;
    }





    public static function getShopDetailsbyShopId($shop_id){
    return WooShop::where('woo_shops.id',$shop_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
    }

}
