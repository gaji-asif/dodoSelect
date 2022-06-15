<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderManagement extends Model
{
    use HasFactory;

    /**
     * Define `order_status` field value
     *
     * @var mixed
     */
    CONST ORDER_STATUS_PENDING = 1;
    CONST ORDER_STATUS_PROCESSING = 2;
    CONST ORDER_STATUS_COMPLETED = 3; // New added status
    CONST ORDER_STATUS_SHIPPED = 5;
    CONST ORDER_STATUS_CANCEL = 6;
    CONST ORDER_STATUS_PENDING_PAYMENT = 7;
    CONST ORDER_STATUS_PAYMENT_UNCONFIRMED = 8;
    CONST ORDER_STATUS_PROCESSED = 9;

    /**
     * Define `payment_status` field value
     *
     * @var mixed
     */
    CONST PAYMENT_STATUS_UNPAID = 0;
    CONST PAYMENT_STATUS_PAID = 1;

    /**
     * Define `Wait for Stock` field value
     *
     * @var mixed
     */
    CONST PENDING_STOCK_NO = 0;
    CONST PENDING_STOCK_YES = 1;

    /**
     * Define `ready_to_ship` field value
     *
     * @var mixed
     */
    CONST READY_TO_SHIP_NO = 0;
    CONST READY_TO_SHIP_YES = 1;

    /**
     * Define `payment_method` field value
     *
     * @var mixed
     */
    CONST PAYMENT_METHOD_BANK_TRANSFER = 1;
    CONST PAYMENT_METHOD_INSTANT = 2;

    /**
     * Define `customer_type` field value
     *
     * @var mixed
     */
    CONST CUSTOMER_TYPE_NORMAL_CUSTOMER = 0;
    CONST CUSTOMER_TYPE_DROPSHIPPER = 1;

    /**
     * Define `tax_enable` field value
     *
     * @var mixed
     */
    CONST TAX_ENABLE_NO = 0;
    CONST TAX_ENABLE_YES = 1;


    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'order_managements';

    /**
     * Mass fillable field
     *
     * @var array
     */
    protected $fillable = [
        'channel',
        'channel_id',
        'contact_name',
        'shipping_phone'
    ];

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'str_order_status',
        'str_payment_status',
        'amount_discount_total'
    ];

    /**
     * Relationship to `order_management_details` table
     *
     * @return mixed
     */
    public function order_management_details()
    {
        return $this->hasMany(OrderManagementDetail::class);
    }

    /**
     * Relationship to `order_management_details` with the `products` table
     *
     * @return mixed
     */
    public function orderProductDetails()
    {
        return $this->hasMany(OrderManagementDetail::class, 'order_management_id', 'id')->with('product');
    }

    /**
     * Relationship to `channels` table
     *
     * @return mixed
     */
    public function channels()
    {
        return $this->belongsTo(Channel::class, 'channel_id', 'id')->withDefault(['name' => '']);
    }

    /**
     * Relationship to `customers` table
     *
     * @return mixed
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withDefault();
    }

    /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withDefault();
    }

    /**
     * Relationship to `shipments` table
     *
     * @return mixed
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'order_id', 'id')->withDefault([ 'id' => 0, 'shipment_date' => null ]);
    }

    /**
     * Relationship to `shops` table
     *
     * @return mixed
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class)->withDefault();
    }

    /**
     * Relationship to `customer_shipping_methods` table
     *
     * @return mixed
     */
    public function customer_shipping_methods()
    {
        return $this->hasMany(CustomerShippingMethod::class, 'order_id');
    }

    /**
     * Relationship to `payments` table
     *
     * @return mixed
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    /**
     * Sub Query to get the total product count
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeQuantity($query)
    {
        $orderManagementTable = $this->getTable();
        $orderManagementDetailsTable = (new OrderManagementDetail())->getTable();

        return $query->addSelect(['quantity' => OrderManagementDetail::selectRaw("SUM({$orderManagementDetailsTable}.quantity)")
            ->whereColumn("{$orderManagementDetailsTable}.order_management_id", "{$orderManagementTable}.id")
            ->limit(1)
        ]);
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
     * By multiple status ids from datatable
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $orderStatusId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByOrderShipmentStatus($query, $orderStatusId = null)
    {
        if (!empty($orderStatusId)) {
            $shipmentTable = (new Shipment())->getTable();

            if ($orderStatusId < 10) {
                return $query->where('order_status', $orderStatusId);
            }
            else
                return $query->where("{$shipmentTable}.shipment_status", $orderStatusId);
        }

        return;
    }

    /**
     * Query to search by from order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $orderManagementsTable = $this->getTable();
            $customersTable = (new Customer())->getTable();

            return $query->where(function(Builder $order) use ($orderManagementsTable, $customersTable, $keyword) {
                $order->where("{$orderManagementsTable}.id", 'like', "%$keyword%")
                    ->orWhere('in_total', 'like', "%$keyword%")
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
        $ordersTable = $this->getTable();
        $customersTable = (new Customer())->getTable();
        $shipmentTable = (new Shipment())->getTable();

        return $query->join("{$customersTable}", "{$customersTable}.id", '=', "{$ordersTable}.customer_id")
                     ->leftjoin("{$shipmentTable}", "{$shipmentTable}.order_id", '=', "{$ordersTable}.id");
    }

    /**
     * Group by query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeGroupByShipmentStatus($query)
    {
        $ordersTable = $this->getTable();
        $shipmentTable = (new Shipment())->getTable();

        return $query->groupBy("{$ordersTable}.id", "{$shipmentTable}.shipment_status");
    }

    /**
     * Group by query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeGroupByOrderStatus($query, $orderStatusId)
    {
        $ordersTable = $this->getTable();

        if ($orderStatusId != 0 && $orderStatusId < 10) {
            return $query->groupBy("{$ordersTable}.id", "{$ordersTable}.order_status");
        }

        return ;
    }

    /**
     * Query to search by from 'tax invoice' datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchTaxInvoiceDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function($order) use ($keyword) {
                $order->where('id', 'like', "%$keyword%")
                    ->orWhere('company_name', 'like', "%$keyword%")
                    ->orWhere('company_address', 'like', "%$keyword%")
                    ->orWhere('in_total', 'like', "%$keyword%");
            });
        }

        return;
    }

    /**
     * Filter if user `dropshipper` or `customer` role
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $roleName
     * @param  string  $customer_type
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeCustomerAsset($query, $roleName, $customerType = '0')
    {
        if ($roleName == User::ROLE_DROPSHIPPER) {
            return $query->where('customer_id', Auth::user()->customer_id)
                    ->where('customer_type', self::CUSTOMER_TYPE_DROPSHIPPER);
        }

        elseif ($customerType == User::ROLE_DROPSHIPPER) {
            return $query->where('customer_type', self::CUSTOMER_TYPE_DROPSHIPPER);
        }

        return $query->where('customer_type', self::CUSTOMER_TYPE_NORMAL_CUSTOMER);
    }

    /**
     * Get all `order_status`
     *
     * @return array
     */
    public static function getAllOrderStatus()
    {
        return [
            self::ORDER_STATUS_PENDING => __('translation.New Order'),
            self::ORDER_STATUS_PROCESSING => __('translation.Processing'),
            self::ORDER_STATUS_SHIPPED => __('translation.Shipped'),
            self::ORDER_STATUS_CANCEL => __('translation.Canceled'),
            self::ORDER_STATUS_PENDING_PAYMENT => __('translation.Pending Payment'),
            self::ORDER_STATUS_PAYMENT_UNCONFIRMED => __('translation.Payment Unconfirmed'),
            Shipment::SHIPMENT_STATUS_PENDING_STOCK => __('translation.Waiting For Stock'),
            Shipment::SHIPMENT_STATUS_READY_TO_SHIP => __('translation.Ready To Ship'),
            Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED => __('translation.Ready To Ship - Printed'),
            Shipment::SHIPMENT_STATUS_SHIPPED => __('translation.Shipped'),
            Shipment::SHIPMENT_STATUS_CANCEL => __('translation.Cancelled'),
        ];
    }

    /**
     * Get available status for edit page
     *
     * @return array
     */
    public static function getAllAvailableStatusForEdit()
    {
        return [
            self::ORDER_STATUS_PENDING => 'New Order',
            self::ORDER_STATUS_PROCESSING => 'Processing',
            self::ORDER_STATUS_CANCEL => 'Cancelled'
        ];
    }

    /**
     * Get status schema for order datatable
     *
     * @return array
     */
    public static function getStatusSchemaForDatatable($roleName = '', $customerType = '')
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
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSING, $roleName, $customerType)
                    ],
                    [
                        'id' => self::ORDER_STATUS_PAYMENT_UNCONFIRMED,
                        'text' => 'Payment Unconfirmed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PAYMENT_UNCONFIRMED, $roleName, $customerType)
                    ],
                    [
                        'id' => self::ORDER_STATUS_PROCESSED,
                        'text' => 'Processed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSED, $roleName, $customerType)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PROCESSING, $roleName, $customerType)
            ],
            [
                'id' => 'P2',
                'text' => 'To Ship',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>',
                'sub_status' => [
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP,
                        'text' => 'Ready To Ship',
                        'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $roleName, $customerType)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED,
                        'text' => 'Ready To Ship (Printed)',
                        'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $roleName, $customerType)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_SHIPPED,
                        'text' => 'Shipped',
                        'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_SHIPPED, $roleName, $customerType)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_PENDING_STOCK,
                        'text' => 'Waiting for Stock',
                        'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $roleName, $customerType)
                    ],
                    [
                        'id' => Shipment::SHIPMENT_STATUS_CANCEL,
                        'text' => 'Cancelled',
                        'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $roleName, $customerType)
                    ],
                ],
                'count' => self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP, $roleName, $customerType) + self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED, $roleName, $customerType) + self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_SHIPPED, $roleName, $customerType) + self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_PENDING_STOCK, $roleName, $customerType) + self::getStatusSchemaCount(Shipment::SHIPMENT_STATUS_CANCEL, $roleName, $customerType),
            ],
            [
                'id' => 'P3',
                'text' => 'To Pay',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-currency-dollar" viewBox="0 0 16 16"><path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_PENDING,
                        'text' => 'New Order',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PENDING, $roleName, $customerType)
                    ],
                    [
                        'id' => self::ORDER_STATUS_PENDING_PAYMENT,
                        'text' => 'Payment Pending',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PENDING_PAYMENT, $roleName, $customerType)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_PENDING, $roleName, $customerType) + self::getStatusSchemaCount(self::ORDER_STATUS_PENDING_PAYMENT, $roleName, $customerType),
            ],
            [
                'id' => 'P4',
                'text' => 'Cancelled',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_CANCEL,
                        'text' => 'Cancelled',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCEL, $roleName, $customerType)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_CANCEL, $roleName, $customerType),
            ],
            [
                'id' => 'P5',
                'text' => 'Completed',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>',
                'sub_status' => [
                    [
                        'id' => self::ORDER_STATUS_COMPLETED,
                        'text' => 'Completed',
                        'count' => self::getStatusSchemaCount(self::ORDER_STATUS_COMPLETED, $roleName, $customerType)
                    ]
                ],
                'count' => self::getStatusSchemaCount(self::ORDER_STATUS_COMPLETED, $roleName, $customerType),
            ],
        ];
    }

    public static function getStatusSchemaCount($statusId, $roleName = null, $customerType = null)
    {
        $ordersTable = (new OrderManagement())->getTable();

        if ($statusId < 10)
        $orderStatusCounts = OrderManagement::selectRaw('order_status, COUNT(id) AS total')
            ->where("{$ordersTable}.seller_id", Auth::id())
            ->byOrderShipmentStatus($statusId)
            ->customerAsset($roleName, $customerType)
//            ->joinedDatatable()
            ->groupBy('order_status')
            ->count();

        else
            $orderStatusCounts = OrderManagement::where("{$ordersTable}.seller_id", Auth::id())
            ->byOrderShipmentStatus($statusId)
            ->customerAsset($roleName, $customerType)
            ->joinedDatatable()
            ->get()->count();

        return $orderStatusCounts;
    }

    /**
     * Get available status for datatable page
     *
     * @return array
     */
    public static function getFirstStatusForDatatable()
    {
        return [
            self::ORDER_STATUS_PROCESSING => __('translation.Processing'),
            Shipment::SHIPMENT_STATUS_PENDING_STOCK => __('translation.Wait for Stock'),
            self::ORDER_STATUS_PENDING => __('translation.New Order')
        ];
    }

    /**
     * Get available status for datatable page
     *
     * @return array
     */
    public static function getSecondStatusForDatatable()
    {
        return [
            self::ORDER_STATUS_PENDING_PAYMENT => __('translation.Pending Payment'),
            self::ORDER_STATUS_PAYMENT_UNCONFIRMED => __('translation.Payment Unconfirmed'),
            self::ORDER_STATUS_PENDING => __('translation.New Order'),
            self::ORDER_STATUS_SHIPPED => __('translation.Shipped'),
            self::ORDER_STATUS_CANCEL => __('translation.Cancelled')
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
            self::PAYMENT_STATUS_UNPAID => __('translation.Unpaid'),
            self::PAYMENT_STATUS_PAID => __('translation.Paid')
        ];
    }

    /**
     * Get all status for `info alert` in public page
     *
     * @return array
     */
    public static function getStatusForInfoAlert()
    {
        return [
            self::ORDER_STATUS_PROCESSING => __('translation.Processing'),
            Shipment::SHIPMENT_STATUS_PENDING_STOCK => __('translation.Wait for Stock'),
            Shipment::SHIPMENT_STATUS_READY_TO_SHIP => __('translation.Ready to Ship'),
            self::ORDER_STATUS_SHIPPED => __('translation.Shipped')
        ];
    }

    /**
     * Get all `payment_method`
     *
     * @return array
     */
    public static function getAllPaymentMethod()
    {
        return [
            self::PAYMENT_METHOD_BANK_TRANSFER => __('translation.Bank Transfer'),
            self::PAYMENT_METHOD_INSTANT => __('translation.Instant Payment')
        ];
    }

    /**
     * Get all `tax_enable`
     *
     * @return array
     */
    public static function getAllTaxEnableValues()
    {
        return [
            self::TAX_ENABLE_NO => __('translation.No'),
            self::TAX_ENABLE_YES => __('translation.Yes')
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

        return $orderStatuses[$orderStatusAttribute] ?? __('translation.Unknown');
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

        return $paymentStatuses[$paymentStatusAttribute] ?? __('translation.Unknown');
    }

    /**
     * Accessor for `amount_discount_total`
     *
     * @return double
     */
    public function getAmountDiscountTotalAttribute()
    {
        $subTotalAttribute = doubleval($this->attributes['sub_total']) ?? 0;
        $shippingCostAttribute = doubleval($this->attributes['shipping_cost']) ?? 0;
        $inTotalAttribute = doubleval($this->attributes['in_total']) ?? 0;
        $taxRateAttribute = doubleval($this->attributes['tax_rate']) ?? 0;

        $subTotalAndShippingCostAmount = $subTotalAttribute + $shippingCostAttribute;

        return ($subTotalAttribute + $shippingCostAttribute + $taxRateAttribute) - $inTotalAttribute;
    }

    /**
     * Return Total Orders Group By Order Status
     *
     * @return double
     */
    public static function getTotalOrderManagementSBYStatus($sellerId)
    {
        return OrderManagement::where("seller_id", $sellerId)
                    ->groupBy("order_status")
                    ->select('order_status', DB::raw('count(*) as total'))
                    ->get();
    }

    public static function getManualPaymentSum($id){
        $result = PaymentManual::where('order_id',$id)->where('is_confirmed',1)->where('is_refund',0)->sum('amount');
        return $result;
    }

    public static function getmanualRefundedSum($id){
        $result = PaymentManual::where('order_id',$id)->where('is_confirmed',1)->where('is_refund',1)->sum('amount');
        return $result;
    }

    public static function getOrderStatus($order_status){
        $order_status_text = '';

        if($order_status == OrderManagement::ORDER_STATUS_PENDING){
            $order_status_text = __('translation.NEW ORDER');
        }

        if($order_status == OrderManagement::ORDER_STATUS_PROCESSING){
            $order_status_text = __('translation.PROCESSING');
        }

        if($order_status == OrderManagement::ORDER_STATUS_CANCEL){
            $order_status_text = __('translation.CANCELLED');
        }

        if($order_status == OrderManagement::ORDER_STATUS_PENDING_PAYMENT){
            $order_status_text = __('translation.PENDING PAYMENT');
        }

        if($order_status == OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED){
            $order_status_text = __('translation.PAYMENT UNCONFIRMED');
        }

        if($order_status == OrderManagement::ORDER_STATUS_PROCESSED){
            $order_status_text = __('translation.PROCESSED');
        }

        if($order_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
            $order_status_text = __('translation.WAITING FOR STOCK');
        }

        if($order_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
            $order_status_text = __('translation.READY TO SHIP');
        }

        if($order_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
            $order_status_text = __('translation.READY TO SHIP - PRINTED');
        }

        if($order_status == Shipment::SHIPMENT_STATUS_SHIPPED){
            $order_status_text = __('translation.SHIPPED');
        }

        if($order_status == Shipment::SHIPMENT_STATUS_CANCEL){
            $order_status_text = __('translation.CANCELLED');
        }

        if($order_status == OrderManagement::ORDER_STATUS_COMPLETED){
            $order_status_text = __('translation.COMPLETED');
        }

        return $order_status_text;
    }

    public static function getAllOredredDetails($order_id){
         $data = OrderManagementDetail::leftJoin('products', function($join) {
                  $join->on('order_management_details.product_id', '=', 'products.id');
                })
                ->select('products.product_name', 'products.product_code', 'products.id', 'order_management_details.quantity as ordered_qty', 'order_management_details.order_management_id')
                ->where('order_management_details.seller_id', '=', Auth::user()->id)
                ->where('order_management_details.order_management_id', '=', $order_id)
                ->get();
         return $data;
    }

    public static function getAllQtyByStatus($order_id, $product_id, $shipment_status){
        $data = Shipment::leftJoin('shipment_products', function($join) {
            $join->on('shipments.id', '=', 'shipment_products.shipment_id');
        })
            ->where('shipments.order_id', '=', $order_id)
            ->where('shipments.is_custom', '=', 0)
            ->where('shipments.shipment_status', '=', $shipment_status)
            ->where('shipment_products.product_id', '=', $product_id)
            ->sum('shipment_products.quantity');

        if(!empty($data)){
            return $data;
        }
        else{
            return 0;
        }

    }

    public static function getShipmentProductsQty($order_id, $shipment_id, $is_custom){
         $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $shipment_id)
                    ->select('shipment_products.*')
                    ->get();
         return $getTotalItems;
    }

    public static function isVatRequest($order_id){
         $result = OrderManagement::where('id', '=', $order_id)
                    ->select('tax_enable')
                    ->first();
         
         if($result->tax_enable == 1){
            return $result->tax_enable;
         }
         
    }
 }
