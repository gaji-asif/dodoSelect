<?php

namespace App\Models;

use App\Enums\DueStatusEnum;
use App\Enums\OrderPurchaseStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderPurchase extends Model
{
    use HasFactory;

    /**
     * Define `status` value field
     *
     * @var string
     */
    CONST STATUS_OPEN = 'open';
    CONST STATUS_CLOSE = 'close';
    CONST STATUS_ARRIVE = 'arrive';
    CONST STATUS_DRAFT = 'draft';

    /**
     * Define `supply_form` value field
     *
     * @var int
     */
    CONST SUPPLY_FROM_IMPORT = 1;
    CONST SUPPLY_FROM_DOMESTIC = 2;

    /**
     * Attributes should cast
     *
     * @var array
     */
    protected $casts = [
        'e_d_f' => 'date',
        'e_d_t' => 'date',
        'e_a_d_f' => 'date',
        'e_a_d_t' => 'date',
        'order_date' => 'date',
        'ship_date' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];

    /**
     * Relationship to `suppliers` table
     *
     * @return mixed
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault(['supplier_name' => '']);
    }

    /**
     * Relationship to `agent_cargo_mark
     * `
     * @return mixed
     */
    public function agent_cargo_mark()
    {
        return $this->belongsTo(AgentCargoMark::class, 'shipping_mark_id', 'id')->withDefault(['shipping_mark' => ' ']);
    }

    /**
     * Relationship to `po_payments
     * `
     * @return mixed
     */
    public function po_payments()
    {
        return $this->belongsTo(PoPayments::class, 'id', 'order_purchase_id')->withDefault(['amount'=> '']);
    }

    /**
     * Relationship to `order_purchase_details
     * `
     * @return mixed
     */
    public function orderProductDetails()
    {
        return $this->hasMany(OrderPurchaseDetail::class, 'order_purchase_id', 'id')->with('product');
    }

    /**
     * Relationship to `order_purchase_details`
     * without relation to products table
     *
     * @return mixed
     */
    public function order_purchase_details()
    {
        return $this->hasMany(OrderPurchaseDetail::class);
    }

    /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id')->withDefault();
    }

    /**
     * Get the po_shipments data
     *
     * @return HasMany
     */
    public function po_shipments()
    {
        return $this->hasMany(PoShipment::class, 'order_purchase_id', 'id');
    }

    /**
     * Get latest po_shipment data
     *
     * @return HasOne
     */
    public function latest_po_shipment()
    {
        return $this->hasOne(PoShipment::class, 'order_purchase_id', 'id')->latestOfMany();
    }

    /**
     * Check if filtered by supplider or not
     *
     * @param  int|null  $supplier_id
     * @return bool
     */
    public static function isFilteredBySupplier($supplier_id = null)
    {
        return !empty($supplier_id) && $supplier_id > 0;
    }

    /**
     * Check if filtered by status or not
     *
     * @param  string|null  $status
     * @return bool
     */
    public static function isFilteredByStatus($status = null)
    {
        return !empty($status) && in_array($status, array_keys(OrderPurchaseStatusEnum::toArray()));
    }

    /**
     * Check if filtered by due status
     * - overdue
     * - arrive_soon
     *
     * @param  string|null  $status
     * @return bool
     */
    public static function isFilteredByDueStatus($status = null)
    {
        return !empty($status) && in_array($status, array_keys(DueStatusEnum::toArray()));
    }

    /**
     * Generate search term query for query builder paramaters
     *
     * @param  string|null  $keyword
     * @param  int  $count
     * @return mixed
     */
    public static function generateSearchTermQuery($keyword = null, $count = 0)
    {
        $keyword = '%' . $keyword . '%';

        $startFrom = 1;
        if ($count <= 1) {
            $startFrom = $count;
        }

        for ($i = $startFrom; $i <= $count; $i++) {
            yield $keyword;
        }
    }

    /**
     * Query to filter by `status`
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSupplier($query, $supplier_id = null)
    {
        if (self::isFilteredBySupplier($supplier_id)) {
            return $query->where('order_purchases.supplier_id', $supplier_id);
        }

        return;
    }

    /**
     * Query to filter by `status`
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status = null)
    {
        if (self::isFilteredByStatus($status)) {
            return $query->where('order_purchases.status', $status);
        }

        return;
    }

    /**
     * Query to join `order_purchases` with `suppliers` and `users` table
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedOrderPurchaseTable($query)
    {
        $orderPurchaseTable = $this->getTable();
        $supplierTable = (new Supplier())->getTable();
        $userTable = (new User())->getTable();

        return $query->selectRaw("{$orderPurchaseTable}.*,
                                {$supplierTable}.supplier_name,
                                IFNULL({$userTable}.name, '') AS author_name")
            ->leftJoin($supplierTable, "{$supplierTable}.id", '=', "{$orderPurchaseTable}.supplier_id")
            ->leftJoin($userTable, "{$userTable}.id", '=', "{$orderPurchaseTable}.author_id");
    }

    /**
     * Query to join `order_purchases` with `po_shipments` table
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedPoShipmentTable($query)
    {
        $orderPurchaseTable = $this->getTable();
        $poShipmentTable = (new PoShipment())->getTable();

        return $query->selectRaw("{$poShipmentTable}.factory_tracking as po_factory_tracking, {$poShipmentTable}.e_a_d_f, {$poShipmentTable}.e_a_d_t, {$poShipmentTable}.cargo_ref, {$poShipmentTable}.number_of_cartons")
            ->leftJoin($poShipmentTable, "{$poShipmentTable}.order_purchase_id", '=', "{$orderPurchaseTable}.id");
    }

    /**
     * Joined to `po_shipments` table with alias columns
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedPoShipment($query)
    {
        $orderPurchaseTable = $this->getTable();
        $poShipmentTable = (new PoShipment())->getTable();

        return $query->selectRaw("
                        {$poShipmentTable}.factory_tracking as pos_factory_tracking,
                        {$poShipmentTable}.e_a_d_f AS pos_e_a_d_f,
                        {$poShipmentTable}.e_a_d_t AS pos_e_a_d_t,
                        {$poShipmentTable}.cargo_ref AS pos_cargo_ref,
                        {$poShipmentTable}.number_of_cartons AS pos_number_of_cartons")
            ->leftJoin($poShipmentTable, "{$poShipmentTable}.order_purchase_id", '=', "{$orderPurchaseTable}.id");
    }

    /**
     * Query to search from order purchase datatable page
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchOrderPurchaseTable($query, $search = null)
    {
        if (!empty($search)) {
            $query->where(function(Builder $orderPurchase) use ($search) {
                $orderPurchase->where('order_purchases.order_date', 'like', "%{$search}%")
                    //->orWhere('po_shipments.e_a_d_f', 'like', "%{$search}%")
                    //->orWhere('po_shipments.e_a_d_t', 'like', "%{$search}%")
                    ->orWhere('po_shipments.number_of_cartons', 'like', "%{$search}%")
                    ->orWhere('po_shipments.domestic_logistics', 'like', "%{$search}%")
                    ->orWhere('po_shipments.number_of_cartons1', 'like', "%{$search}%")
                    ->orWhere('po_shipments.domestic_logistics1', 'like', "%{$search}%")
                    ->orWhere('po_shipments.cargo_ref', 'like', "%{$search}%")
                    ->orWhere('po_shipments.factory_tracking', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhereHas('order_purchase_details', function(Builder $odp) use ($search) {
                        $odp->whereHas('product', function(Builder $product) use ($search) {
                            $product->where('product_code', 'like', "%{$search}%");
                        });
                    });
            });
        }

        return;
    }

    /**
     * Get all Purchase Order & Details
     *
     * @return array
     */
    public static function orderPurchaseTotalByGroup()
    {
        return OrderPurchase::select("order_purchases.status", DB::raw("count(*) as total"))
        ->groupBy('order_purchases.status')
        ->get();
    }

    /**
     * Get all Purchase Order & Details
     *
     * @return array
     */
    public static function getAllPOData($id,$sellerId)
    {
        return OrderPurchase:: where('id', $id)
            ->where('seller_id', $sellerId)
            ->with('supplier')
            ->with([ 'order_purchase_details' => function($detail) {
                $detail->with('product')
                    ->with('exchange_rate');
            } ])
            ->with('author')
            ->first();
    }

    /**
     * Get all Today's Purchase Orders By Seller ID
     * param int
     * @return mixed
     */
    public static function getTodaysPODataBySellerID($today, $sellerId){
        return OrderPurchase::query()
            ->where('order_date', $today)
            ->where('seller_id', $sellerId)
            ->count();
   }

    /**
     * Get all Payment Info
     *
     * @return array
     */
    public static function getAllPaymentInfo($id)
    {
    return DB::table('po_payments')
              ->join('exchange_rate', 'exchange_rate.id', '=', 'po_payments.exchange_rate_id')
              ->where('order_purchase_id', $id)->first();

    }

    /**
     * Get order purchase data for datatable
     *
     * @param  array  $dt_params
     * @return mixed
     */
    public static function getDataForDatatable(array $dt_params = [])
    {
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();
        $poShipmentsTable = (new PoShipment())->getTable();

        $sellerId = $dt_params['sellerId'];
        $statusFilter = $dt_params['statusFilter'];
        $supplierId = $dt_params['supplierId'];
        $search = $dt_params['search'];
        $orderColumn = $dt_params['orderColumn'];
        $orderDir = $dt_params['orderDir'];
        $limit = $dt_params['limit'];
        $start = $dt_params['start'];
        $arriveOrOverDue = $dt_params['arrive_or_over_due'];
        $daysLimit = $dt_params['days_limit'];

        $orderPurchaseDetails = OrderPurchaseDetail::query()
            ->where("{$orderPurchaseDetailsTable}.seller_id", $sellerId)
            ->joinedProduct();

        if ($arriveOrOverDue == DueStatusEnum::arrive_soon()->value) {
            return OrderPurchase::query()
                ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
                ->where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->whereBetween('po_shipments.e_a_d_f', [
                    Carbon::now()->addDay()->format('Y-m-d'),
                    Carbon::now()->addDays($daysLimit)->format('Y-m-d')
                ])
                ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                    $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                        ->orWhere("products_code", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
                })
                ->with('agent_cargo_mark')
                ->with('po_payments')
                ->supplier($supplierId)
                ->status($statusFilter)
                ->joinedOrderPurchaseTable()
                ->joinedPoShipment()
                ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                    $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
                })
                ->groupBy("{$orderPurchaseTable}.id")
                ->orderBy($orderColumn, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();
        }

        if ($arriveOrOverDue == DueStatusEnum::overdue()->value) {
            return OrderPurchase::query()
                ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
                ->where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->whereDate('po_shipments.e_a_d_f', '<=', Carbon::now()->subDay()->format('Y-m-d'))
                ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                    $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                        ->orWhere("products_code", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
                })
                ->with('agent_cargo_mark')
                ->with('po_payments')
                ->supplier($supplierId)
                ->status($statusFilter)
                ->joinedOrderPurchaseTable()
                ->joinedPoShipment()
                ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                    $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
                })
                ->groupBy("{$orderPurchaseTable}.id")
                ->orderBy($orderColumn, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();
        }

        return OrderPurchase::query()
            ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
            ->where("{$orderPurchaseTable}.seller_id", $sellerId)
            ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                    ->orWhere("products_code", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
            })
            ->supplier($supplierId)
            ->status($statusFilter)
            ->with('agent_cargo_mark')
            ->with('po_payments')
            ->joinedOrderPurchaseTable()
            ->joinedPoShipment()
            ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
            })
            ->groupBy("{$orderPurchaseTable}.id")
            ->orderBy($orderColumn, $orderDir)
            ->take($limit)
            ->skip($start)
            ->get();
    }

    /**
     * Get Order Purchase Total
     * group by status
     *
     * @param  array  $dt_params
     * @return array
     */
    public static function getOrderPurchaseTotal(array $dt_params = [])
    {
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();
        $poShipmentsTable = (new PoShipment())->getTable();

        $sellerId = $dt_params['sellerId'];
        $statusFilter = $dt_params['statusFilter'];
        $supplierId = $dt_params['supplierId'];
        $search = $dt_params['search'];
        $arriveOrOverDue = $dt_params['arrive_or_over_due'];
        $daysLimit = $dt_params['days_limit'];

        $orderPurchaseDetails = OrderPurchaseDetail::query()
            ->where("{$orderPurchaseDetailsTable}.seller_id", $sellerId)
            ->joinedProduct();

        $orderPurchaseQuery = OrderPurchase::query()
            ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
            ->where("{$orderPurchaseTable}.seller_id", $sellerId)
            ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                    ->orWhere("products_code", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                    ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
            })
            ->supplier($supplierId)
            ->status($statusFilter)
            ->joinedOrderPurchaseTable()
            ->joinedPoShipment()
            ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
            })
            ->groupBy("{$orderPurchaseTable}.id")
            ->toSql();

        if ($arriveOrOverDue == DueStatusEnum::arrive_soon()->value) {
            $orderPurchaseQuery = OrderPurchase::query()
                ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
                ->where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                    $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                        ->orWhere("products_code", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
                })
                ->supplier($supplierId)
                ->status($statusFilter)
                ->whereBetween('po_shipments.e_a_d_f', [
                    Carbon::now()->addDay()->format('Y-m-d'),
                    Carbon::now()->addDays($daysLimit)->format('Y-m-d')
                ])
                ->joinedOrderPurchaseTable()
                ->joinedPoShipment()
                ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                    $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
                })
                ->groupBy("{$orderPurchaseTable}.id")
                ->toSql();
        }

        if ($arriveOrOverDue == DueStatusEnum::overdue()->value) {
            $orderPurchaseQuery = OrderPurchase::query()
                ->selectRaw("GROUP_CONCAT(details.products_code SEPARATOR ', ') as products_code")
                ->where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->where(function (Builder $query) use ($search, $orderPurchaseTable, $poShipmentsTable) {
                    $query->where("{$orderPurchaseTable}.order_date", 'like', "%{$search}%")
                        ->orWhere("products_code", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.number_of_cartons1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.domestic_logistics1", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.cargo_ref", 'like', "%{$search}%")
                        ->orWhere("{$poShipmentsTable}.factory_tracking", 'like', "%{$search}%");
                })
                ->supplier($supplierId)
                ->status($statusFilter)
                ->whereDate('po_shipments.e_a_d_f', '<=', Carbon::now()->subDay()->format('Y-m-d'))
                ->joinedOrderPurchaseTable()
                ->joinedPoShipment()
                ->leftJoinSub($orderPurchaseDetails, 'details', function ($join) use ($orderPurchaseTable) {
                    $join->on("{$orderPurchaseTable}.id", "=", "details.order_purchase_id");
                })
                ->groupBy("{$orderPurchaseTable}.id")
                ->toSql();
        }

        $rawQueryParams = [$sellerId, $sellerId, ...self::generateSearchTermQuery($search, 8)];

        if (self::isFilteredBySupplier($supplierId)) {
            $rawQueryParams = [...$rawQueryParams, $supplierId];
        }

        if (self::isFilteredByDueStatus($arriveOrOverDue) && $arriveOrOverDue == DueStatusEnum::overdue()->value) {
            $overDueDate = Carbon::now()->subDay()->format('Y-m-d');
            $rawQueryParams = [...$rawQueryParams, $overDueDate];
        }

        if (self::isFilteredByDueStatus($arriveOrOverDue) && $arriveOrOverDue == DueStatusEnum::arrive_soon()->value) {
            $arriveDateFrom = Carbon::now()->addDay()->format('Y-m-d');
            $arriveDateTo = Carbon::now()->addDays($daysLimit)->format('Y-m-d');

            $rawQueryParams = [...$rawQueryParams, $arriveDateFrom, $arriveDateTo];
        }

        if (self::isFilteredByStatus($statusFilter)) {
            $rawQueryParams = [...$rawQueryParams, $statusFilter];
        }

        return collect(DB::select(DB::raw("SELECT status, COUNT(*) AS total FROM ($orderPurchaseQuery) as tb GROUP BY status"), $rawQueryParams));
    }

    /**
     * Get Order Purchase Total Search
     *
     * @param  array  $arr_param
     * @param  string|int  $search
     * @return int
     */
    public static function orderPurchaseTotalSearch($arr_param,$search)
    {
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $sellerId = $arr_param['sellerId'];
        $statusFilter = $arr_param['statusFilter'];
        $supplierId = $arr_param['supplierId'];
        $search = $arr_param['search'];
        $orderColumn = $arr_param['orderColumn'];
        $orderDir = $arr_param['orderDir'];
        $limit = $arr_param['limit'];
        $start = $arr_param['start'];
        $arriveOrOverDue = $arr_param['arrive_or_over_due'];
        $daysLimit = $arr_param['days_limit'];

        $arriveSoonFilter = NULL;
        if ($arriveOrOverDue=='arrive_soon') {
            return OrderPurchase::where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->with([ 'order_purchase_details' => function($detail) {
                    $detail->with('product')
                        ->with('exchange_rate');
                } ])
                ->whereBetween('po_shipments.e_a_d_f', [
                    \carbon\Carbon::now()->adddays()->format('Y-m-d'),
                \carbon\Carbon::now()->adddays($daysLimit)->format('Y-m-d')
                ])
                ->with('agent_cargo_mark')
                ->with('po_payments')
                ->supplier($supplierId)
                ->joinedOrderPurchaseTable()
                ->joinedPoShipmentTable()
                ->searchOrderPurchaseTable($search)
                ->count();
        }

        if ($arriveOrOverDue=='overdue') {
            return OrderPurchase::where("{$orderPurchaseTable}.seller_id", $sellerId)
            ->with([ 'order_purchase_details' => function($detail) {
                $detail->with('product')
                    ->with('exchange_rate');
            } ])
            ->whereBetween('po_shipments.e_a_d_f', [
                \carbon\Carbon::now()->subdays($daysLimit)->format('Y-m-d'),
                \carbon\Carbon::now()->subday()->format('Y-m-d')
            ])
            ->with('agent_cargo_mark')
            ->with('po_payments')
            ->supplier($supplierId)
            ->joinedOrderPurchaseTable()
            ->joinedPoShipmentTable()
            ->searchOrderPurchaseTable($search)
            ->count();
        }

        return OrderPurchase::where("{$orderPurchaseTable}.seller_id",$sellerId)
            ->with([ 'order_purchase_details' => function($detail) {
                $detail->with('product')
                    ->with('exchange_rate');
            } ])
            ->with('agent_cargo_mark')
            ->with('po_payments')
            ->status($statusFilter)
            ->supplier($supplierId)
            ->joinedPoShipmentTable()
            ->joinedOrderPurchaseTable()
            ->searchOrderPurchaseTable($search)
            ->count();
    }

      /**
     * Get Order Purchase Total By Status
     *
     * @return Int
     */
    public static function orderPurchaseTotalCountByStatus($arr_param)
    {
    $orderPurchaseTable = (new OrderPurchase())->getTable();
    $sellerId = $arr_param['sellerId'];
    $statusFilter = $arr_param['statusFilter'];
    $supplierId = $arr_param['supplierId'];
    $search = $arr_param['search'];
    $orderColumn = $arr_param['orderColumn'];
    $orderDir = $arr_param['orderDir'];
    $limit = $arr_param['limit'];
    $start = $arr_param['start'];

    return OrderPurchase::where("{$orderPurchaseTable}.seller_id", $sellerId)
                ->supplier($supplierId)
                ->joinedOrderPurchaseTable()
                ->joinedPoShipmentTable()
                ->searchOrderPurchaseTable($search)
                ->take($limit)
                ->skip($start)
                ->groupBy("{$orderPurchaseTable}.status")
                ->select('order_purchases.status', DB::raw('count(*) as total'))
                ->get();
    }



    public static function poTotalCountByStatus($sellerId,$supplierId)
    {
        $orderPurchaseTable = (new OrderPurchase())->getTable();

        $supplierFilter = NULL;
        if($supplierId>0){
          $supplierFilter = 'AND '.$orderPurchaseTable.'.`supplier_id` ='.$supplierId;
        }
        return DB::select(DB::raw("
        SELECT `{$orderPurchaseTable}`.`status`, count(*) as total from {$orderPurchaseTable}
        where `{$orderPurchaseTable}`.`seller_id` = $sellerId
        {$supplierFilter}
        group by {$orderPurchaseTable}.`status`
        "));

    }


    /**
     * Get all supply from
     *
     * @return array
     */
    public static function getAllSupplyFrom()
    {
        return [
            self::SUPPLY_FROM_IMPORT => 'Import',
            self::SUPPLY_FROM_DOMESTIC => 'Domestic'
        ];
    }

    /**
     * Insert Data into Tatble
     *
     * @return last insert id
     */
    public static function insertData($table,$data = []){
        DB::table($table)->insert($data);
        $id = DB::getPdo()->lastInsertId();
        return $id;
    }

    /**
     * Insert Data into Tatble
     *
     * @return last insert id
     */
    public static function insertTableData($table,$data = []){
        DB::table($table)->insert($data);
        $id = DB::getPdo()->lastInsertId();
        return $id;
    }

     /**
     * Update payment
     *
     * @param int $id
     * @param array $data
     */
    public static function updatePayment($table,$id,$data = []){
        DB::table($table)->where('id', $id)->update($data);
    }

    /**
     * get Details
     *
     * @param table name , int $id
     * @param array $data
     */
    public static function getPoShipmentByOrderPurchaseID($poShipmentTable,$order_purchase_id){
        $supplierTable = (new Supplier())->getTable();

        $sql = "SELECT *,`{$poShipmentTable}`.`id` as id,`{$supplierTable}`.`supplier_name`  FROM $poShipmentTable
        INNER JOIN `{$supplierTable}` ON `{$supplierTable}`.`id` = `{$poShipmentTable}`.`supplier_id`
        WHERE `{$poShipmentTable}`.`order_purchase_id` = $order_purchase_id
        ";

        return DB::select(DB::raw($sql));
    }

    /**
     * Get PO Shipment Details By id
     *
     * @return Int
     */
    public static function poShipmentDetailsByOrderPurchaseID($order_purchase_id)
    {
        return PoShipment::where('order_purchase_id', $order_purchase_id)
        ->with('supplier')
        ->with('order_purchase_details')
        ->with([ 'po_shipment_details' => function($detail) {
            $detail->with('product')
            ->with('getShipped');
        }])
        ->get()->toArray();
    }
}
