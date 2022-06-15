<?php

namespace App\Models;

use App\Traits\HasProductTagsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasProductTagsTrait;

    /**
     * Define `stock_status`
     *
     * @var mixed
     */
    CONST STATUS_OUT_OF_STOCK = 1;
    CONST STATUS_LOW_STOCK = 2;
    CONST STATUS_AVAILABLE_STOCK = 3;
    CONST STATUS_NOT_AVAILABLE = 4;

    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'product_name',
        'specifications',
        'pack',
        'currency',
        'cost_pc',
        'shop_id',
        'category_id',
        'image',
        'product_code',
        'seller_id',
        'warehouse_id',
        'from_where',
        'price',
        'weight',
        'alert_stock',
        'cost_price',
        'dropship_price',
        'cost_currency'
    ];

    /**
     * Attributes should cast
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'pack' => 'integer'
    ];

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'image_url'
    ];

    /**
     * Relationship to `categories` table
     *
     * @return mixed
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault(['cat_name' => '']);
    }

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
     * Relationship to `product tags` table
     *
     * @return mixed
     */
    public function productTags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_has_tags', 'product_id', 'product_tag_id');
    }

    /**
     * Relationship to `product_main_stocks` table
     *
     * @return mixed
     */
    public function getQuantity() {
        return $this->hasOne(ProductMainStock::class)->withDefault(['quantity' => 0]);
    }

    /**
     * Get product_main_stocks data
     *
     * @return HasOne
     */
    public function product_main_stock()
    {
        return $this->hasOne(ProductMainStock::class)->withDefault([ 'quantity' => 0 ]);
    }

    /**
     * Relationship to `order_purchase_details` table
     *
     * @return mixed
     */
    public function getIncoming(){
        return $this->hasMany(OrderPurchaseDetail::class, 'product_id', 'id');
    }

     /**
     * Relationship to `po_shipment_details` table to find total shipped quantity by product ID
     *
     * @return mixed
     */
    public function getShipped(){
        return $this->hasMany(PoShipmentDetail::class, 'product_id', 'id')
                ->select('order_purchase_id','product_id',DB::raw('sum(ship_quantity) as totalShipped'))
                ->groupBy('order_purchase_id');
    }



    public function getTotalShippedQty()
    {
        return $this->hasMany(PoShipmentDetail::class,'product_id' ,'product_id')
                    ->select('product_id',DB::raw('sum(ship_quantity) as totalShipped'))
                    ->groupBy('product_id');
    }

     /**
     * Relationship to `product_costs_default_supplier` table
     *
     * @return mixed
     */
    public function productCostDetails()
    {
        return $this->hasOne(ProductCost::class, 'product_id', 'id')
            ->withDefault(['default_supplier' => 1]);
    }

    /**
     * Relationship to `product_costs` table WHERE default supplier = 1
     *
     * @return mixed
     */
    public function preferredProductCost()
    {
        return $this->hasOne(ProductCost::class, 'product_id', 'id')
            ->where('default_supplier', '=', 1)
            ->withDefault(['cost' => 0]);
    }

    /**
     * Relationship to `users` table
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    /**
     * Relationship to `product_costs` table
     *
     * @return mixed
     */
    public function productCost() {
        return $this->hasMany(ProductCost::class, 'product_id', 'id')->withDefault(['cost' => 0]);
    }

    /**
     * Relationship to `order_purchase_details` table
     *
     * @return mixed
     */
    public function getProductPrice(){
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Relationship to `woo_products` table
     *
     * @return mixed
     */
    public function wooProducts(){
        return $this->hasMany(WooProduct::class, 'dodo_product_id', 'id');
    }

    /**
     * Get the linked WooCommerce Product
     *
     * @return mixed
     */
    public function woo_products()
    {
        return $this->hasMany(WooProduct::class, 'dodo_product_id', 'id');
    }

    /**
     * Get Shopee Product data
     *
     * @return HasMany
     */
    public function shopeeProducts()
    {
        return $this->hasMany(ShopeeProduct::class, 'dodo_product_id', 'id');
    }

    /**
     * Get Lazada Product data
     *
     * @return HasMany
     */
    public function lazadaProducts()
    {
        return $this->hasMany(LazadaProduct::class, 'dodo_product_id', 'id');
    }

    /**
     * Query to search by name from table
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function ($product) use ($keyword) {
                $product->where('product_name', 'like', '%' . $keyword . '%')
                    ->orWhere('product_code', 'like', '%' . $keyword . '%');
            });
        }

        return;
    }

    /**
     * Sub Query to get total of incoming / order_purchase_detail
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeTotalIncoming($query)
    {
        $productTable = $this->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();

        return $query->addSelect(['total_incoming' => OrderPurchaseDetail::selectRaw("
                SUM({$orderPurchaseDetailsTable}.quantity)
            ")
            ->join("{$orderPurchaseTable}", "{$orderPurchaseTable}.id", '=', "{$orderPurchaseDetailsTable}.order_purchase_id")
            ->whereColumn("{$orderPurchaseDetailsTable}.product_id", "{$productTable}.id")
            ->where("{$orderPurchaseTable}.status", '<>', OrderPurchase::STATUS_CLOSE)
            ->limit(1)
        ]);
    }

    /**
     * Sub Query to get total of total_shipping / order_purchase_detail
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeTotalShipping($query)
    {
        $productTable = $this->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();

        return $query->addSelect(['total_shipping' => OrderPurchaseDetail::selectRaw("
        SUM({$orderPurchaseDetailsTable}.quantity)
        ")
        ->join("{$orderPurchaseTable}", "{$orderPurchaseTable}.id", '=', "{$orderPurchaseDetailsTable}.order_purchase_id")
            ->whereColumn("{$orderPurchaseDetailsTable}.product_id", "{$productTable}.id")
            ->where("{$orderPurchaseTable}.status", '<>', OrderPurchase::STATUS_CLOSE)
            ->limit(1)
        ]);
    }



        /**
     * Sub Query to get total of total_shipping / order_purchase_detail
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeTotalShippingByPOId($query)
    {
        $productTable = $this->getTable();
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();
        $poShipmentTable = (new PoShipment())->getTable();
        $poShipmentDetailsTable = (new PoShipmentDetail())->getTable();

        return $query->addSelect(['total_shipping' => PoShipmentDetail::selectRaw("
        SUM({$poShipmentDetailsTable}.ship_quantity)
        ")
        ->join("{$poShipmentTable}", "{$poShipmentTable}.id", '=', "{$poShipmentDetailsTable}.po_shipment_id")
        ->join("{$orderPurchaseDetailsTable}", "{$poShipmentDetailsTable}.order_purchase_id", '=', "{$orderPurchaseDetailsTable}.order_purchase_id")
            ->whereColumn("{$poShipmentDetailsTable}.product_id", "{$productTable}.id")
            ->where("{$poShipmentTable}.status1", '<>', OrderPurchase::STATUS_CLOSE)
            ->limit(1)
        ]);

    }

    /**
     * Sub Query to get the quantity
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeQuantity($query)
    {
        return $query->addSelect(['quantity' => ProductMainStock::selectRaw("quantity")
                ->whereColumn('product_main_stocks.product_id', 'products.id')
                ->limit(1)
        ]);
    }

    /**
     * Sub Query to get the quantity
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWarehouseQuantity($query)
    {
        return $query->addSelect(['warehouse_quantity' => ProductMainStock::selectRaw("warehouse_quantity")
                ->whereColumn('product_main_stocks.product_id', 'products.id')
                ->limit(1)
        ]);
    }

    /**
     * Sub Query to get the quantity
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeReservedQuantity($query)
    {
        return $query->addSelect(['reserved_quantity' => ProductMainStock::selectRaw("reserved_quantity")
                ->whereColumn('product_main_stocks.product_id', 'products.id')
                ->limit(1)
        ]);
    }

    /**
     * Sub Query to get the quantity
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeDisplayReservedQty($query)
    {
        return $query->addSelect(['display_reserved_qty' => ProductMainStock::selectRaw("display_reserved_qty")
                ->whereColumn('product_main_stocks.product_id', 'products.id')
                ->limit(1)
        ]);
    }

    /**
     * Query to filter products by category
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $categoryId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFilterByCategory($query, $categoryId = null)
    {
        if (!empty($categoryId)) {
            return $query->where('category_id', $categoryId);
        }

        return;
    }

    /**
     * Query to filter products by product tags
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $productTag
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFilterByProductTag($query, $productTag = null)
    {
        if (!empty($productTag)) {
            $productTagsTable = (new ProductTag())->getTable();

            return $query->whereHas('productTags', function ($query) use ($productTagsTable, $productTag) {
                 $query->where("{$productTagsTable}.id", $productTag);
            });
        }

        return;
    }

    /**
     * Query to filter products by Auth user
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param $userRole
     * @param null $userId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFilterByAuthUser($query, $userRole, $userId = null)
    {
        if (!empty($userRole)) {
            if ($userRole == 'dropshipper'){
                $userPermissions = Permission::dropshipperProductPermissions($userId);
                return $query->whereIn('product_code', $userPermissions);
            } else

                return;
        }

        return;
    }

    /**
     * Update product cost price and cost currency
     *
     * @param int $id
     * @param array $data
     */
    public static function updateData($id,$data = []){
        DB::table('products')->where('id', $id)->update($data);
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        try {
            if (isset($this->attributes['image'])) {
                $imageAttribute = $this->attributes['image'];

                if (!empty($imageAttribute) && Storage::disk('s3')->exists($imageAttribute)) {
                    return Storage::disk('s3')->url($imageAttribute);
                }
            }
        } catch (\Exception $exception) {
            // Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return asset('No-Image-Found.png');
    }

    /**
     * Query for `Product Details ` data
     * Relationship to `order_purchase_details`  & `po_shipment_details` table
     * @param  int  $sellerId
     * @param  array|null  $arrReportStatus
     * @param  array|null  $otherParams
     * @return mixed
     */

    public static function getProductDetailsWithIncommingAndShipped($productCodes,$order_purchase_id,$sellerId){

        $productTable = (new Product())->getTable();
        $productStockTable = (new ProductMainStock())->getTable();
        $productCostTable = (new ProductCost())->getTable();
        $exchangeRateTable = (new ExchangeRate())->getTable();
        $poShipmentTable = (new PoShipment())->getTable();
        $poShipmentDetailTable = (new PoShipmentDetail())->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();
        $suppliersTable = (new Supplier())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $productReorderTable = (new ProductReorders())->getTable();

        $orderPurchaseCloseStatus = OrderPurchase::STATUS_CLOSE;
        $poCloseStatus = PoShipmentDetail::PO_STATUS_CLOSE;

        $orderPurchaseTableFilter = NULL;
        $poShipmentDetailTableFilter = NULL;

        // If PO edit page then it will render data by order_purchase_id
        if($order_purchase_id > 0){
            $orderPurchaseTableFilter = "AND `{$orderPurchaseTable}`.id = {$order_purchase_id}";
            $poShipmentDetailTableFilter = "AND `{$poShipmentDetailTable}`.order_purchase_id = {$order_purchase_id}";
        }


        $sql = "SELECT `{$productTable}`.*,
                `{$suppliersTable}`.supplier_name,
                    `tb_po_shipping`.`order_purchase_id`,
                    `tb_product_cost`.cost as product_cost,
                    `tb_product_cost`.default_cost_currency,
                    `tb_product_cost`.operation_cost,
                    `tb_product_cost`.pieces_per_pack as pieces_per_pack_for_default_supplier,
                    `tb_product_cost`.pieces_per_carton as pieces_per_carton_for_default_supplier,
                    `tb_product_cost`.`exchange_rate_id`,
                    `tb_reorders`.`reorder_status`,
                    `tb_reorders`.`reorder_shiptype`,
                    `tb_reorders`.`reorder_qty`,
                    IFNULL(`{$productStockTable}`.`quantity`, 0) AS stock_quantity,
                    IFNULL(`tb_po_shipping`.`total_shipped`, 0) AS total_shipped,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_incoming,
                    IFNULL(`tb_incoming`.`order_packs`, 0) as order_packs,
                    IFNULL(`tb_incoming`.`product_price`, 0) as product_price,
                    IFNULL(`tb_incoming`.`exchange_rate_id`, 0) as price_exchange_rate_id,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) - IFNULL(`tb_po_shipping`.`total_shipped`, 0) as total_available_qty
                FROM `{$productTable}`
                LEFT JOIN `{$productStockTable}` ON `{$productStockTable}`.`product_id` = `{$productTable}`.`id`

                 LEFT JOIN (
                    SELECT `{$poShipmentDetailTable}`.product_id,
                     `{$poShipmentDetailTable}`.order_purchase_id,
                            SUM(`{$poShipmentDetailTable}`.`ship_quantity`) AS total_shipped
                    FROM `{$poShipmentDetailTable}`
                    LEFT JOIN `{$poShipmentTable}` ON `{$poShipmentTable}`.`id` = `{$poShipmentDetailTable}`.`po_shipment_id`
                    WHERE `{$poShipmentTable}`.`status` <> '{$poCloseStatus}'
                    {$poShipmentDetailTableFilter}
                    GROUP BY `{$poShipmentDetailTable}`.product_id,`{$poShipmentDetailTable}`.order_purchase_id
                ) `tb_po_shipping` ON `tb_po_shipping`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$productCostTable}`.product_id,
                     `{$productCostTable}`.cost,
                     `{$productCostTable}`.`exchange_rate_id`,
                     `{$exchangeRateTable}`.`name` as default_cost_currency,
                     `{$productCostTable}`.operation_cost,
                     `{$productCostTable}`.pieces_per_pack,
                     `{$productCostTable}`.pieces_per_carton
                    FROM `{$productCostTable}`
                    LEFT JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$productCostTable}`.`exchange_rate_id`
                    WHERE `{$productCostTable}`.`default_supplier` = '1'
                ) `tb_product_cost` ON `tb_product_cost`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                    `{$orderPurchaseDetailTable}`.product_price as product_price,
                    `{$orderPurchaseDetailTable}`.`exchange_rate_id`,
                    `{$orderPurchaseDetailTable}`.quantity as order_packs,
                    SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_incoming
                    FROM `{$orderPurchaseDetailTable}`
                    LEFT JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$orderPurchaseDetailTable}`.`exchange_rate_id`
                    LEFT JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    WHERE `{$orderPurchaseTable}`.`status` <> '{$orderPurchaseCloseStatus}'
                    {$orderPurchaseTableFilter}
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_incoming` ON `tb_incoming`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$productReorderTable}`.`product_id`,
                    GROUP_CONCAT( `{$productReorderTable}`.`status` SEPARATOR ',') AS reorder_status,
                    GROUP_CONCAT( `{$shipTypeTable}`.`name` SEPARATOR ',') AS reorder_shiptype,
                    GROUP_CONCAT( `{$productReorderTable}`.`quantity` SEPARATOR ',') AS reorder_qty
                    FROM `{$productReorderTable}`
                    LEFT JOIN `{$shipTypeTable}` ON `{$shipTypeTable}`.`id` = `{$productReorderTable}`.`type`
                    GROUP BY `{$productReorderTable}`.product_id
                ) `tb_reorders` ON `tb_reorders`.product_id = `{$productTable}`.id

                LEFT JOIN  `{$suppliersTable}` ON  `{$suppliersTable}`.id = `{$productTable}`.supplier_id

                WHERE `{$productTable}`.seller_id = {$sellerId}
                AND `{$productTable}`.product_code in ({$productCodes})
                GROUP BY `{$productTable}`.product_code
                ";

            $data[] =  DB::select(DB::raw("SELECT *
                            FROM (
                                $sql
                            ) `tb1`
                            WHERE 1 = 1


                        "));


        return $data;
    }


    public static function getOrderAndShipped($productCodes,$order_purchase_id,$sellerId){
        $poShipmentTable = (new PoShipment())->getTable();
        $poShipmentDetailTable = (new PoShipmentDetail())->getTable();
        $productTable = (new Product())->getTable();

        $exchangeRateTable = (new ExchangeRate())->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $productReorderTable = (new ProductReorders())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $supplierTable = (new Supplier())->getTable();

        $orderPurchaseCloseStatus = OrderPurchase::STATUS_CLOSE;
        $poCloseStatus = PoShipmentDetail::PO_STATUS_CLOSE;

        $orderPurchaseTableFilter = NULL;
        $poShipmentDetailTableFilter = NULL;

        $orderPurchaseTableFilter = NULL;
        $poShipmentDetailTableFilter = NULL;

        // If PO edit page then it will render data by order_purchase_id
        if($order_purchase_id > 0){
            $orderPurchaseTableFilter = "AND `{$orderPurchaseTable}`.id = {$order_purchase_id}";
           // $poShipmentDetailTableFilter = "AND `{$poShipmentDetailTable}`.order_purchase_id = {$order_purchase_id}";
        }
        $sql = "
                SELECT `{$productTable}`.*,
                    IFNULL(`tb_po_shipping`.`total_shipped`, 0) AS total_shipped

                FROM `{$productTable}`
                LEFT JOIN (
                    SELECT `{$poShipmentDetailTable}`.product_id,
                     `{$poShipmentDetailTable}`.order_purchase_id,
                            SUM(`{$poShipmentDetailTable}`.`ship_quantity`) AS total_shipped
                    FROM `{$poShipmentDetailTable}`
                    LEFT JOIN `{$poShipmentTable}` ON `{$poShipmentTable}`.`id` = `{$poShipmentDetailTable}`.`po_shipment_id`
                    WHERE `{$poShipmentTable}`.`status` <> '{$poCloseStatus}'
                    {$poShipmentDetailTableFilter}
                    GROUP BY `{$poShipmentDetailTable}`.product_id,`{$poShipmentDetailTable}`.order_purchase_id
                ) `tb_po_shipping` ON `tb_po_shipping`.product_id = `{$productTable}`.id


                WHERE `{$productTable}`.seller_id = {$sellerId}

                AND `{$productTable}`.product_code in ({$productCodes})
                GROUP BY `{$productTable}`.product_code
                    ";

                return DB::select(DB::raw("SELECT *
                    FROM (
                        $sql
                    ) `tb1`
                    WHERE 1 = 1

                "));
    }
    /**
     * Query for `Product Details with Stock,Incoming,Shipment` data
     *
     * @param  int  $sellerId
     * @param  array|null  $arrReportStatus
     * @param  array|null  $otherParams
     * @return mixed
     */
    public static function reportStockTable($sellerId, $arrReportStatus, $otherParams = null)
    {
        $productCostTable = (new ProductCost())->getTable();
        $productTable = (new Product())->getTable();
        $productStockTable = (new ProductMainStock())->getTable();
        $exchangeRateTable = (new ExchangeRate())->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $productReorderTable = (new ProductReorders())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $stockLogsTable = (new StockLog())->getTable();
        $supplierTable = (new Supplier())->getTable();

        $poShipmentTable = (new PoShipment())->getTable();
        $poShipmentDetailTable = (new PoShipmentDetail())->getTable();

        $orderPurchaseCloseStatus = OrderPurchase::STATUS_CLOSE;
        $poCloseStatus = PoShipmentDetail::PO_STATUS_CLOSE;

        $orderPurchaseTableFilter = NULL;
        $poShipmentDetailTableFilter = NULL;


        $categoryId = isset($otherParams['category_id']) ? $otherParams['category_id'] : 0;
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : 0;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;
        $dateFrom = isset($otherParams['dateFrom']) ? $otherParams['dateFrom'] : null;
        $dateTo = isset($otherParams['dateTo']) ? $otherParams['dateTo'] : null;

        $offset = isset($otherParams['offset']) ? $otherParams['offset'] : 0;
        $limit = isset($otherParams['limit']) ? $otherParams['limit'] : 10;

        $orderColumn = isset($otherParams['order_column']) ? $otherParams['order_column'] : 'id';
        $orderDir = isset($otherParams['order_dir']) ? $otherParams['order_dir'] : 'asc';


        $dateFilter = null;
        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateFilter = "AND `date` BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        }


        $POdateFilter = null;
        if (!empty($dateFrom) && !empty($dateTo)) {
            $POdateFilter = "WHERE `{$orderPurchaseTable}`.`order_date` BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        }



        $searchFilter = null;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    `product_name` LIKE '%{$keyword}%'
                    OR `product_code` LIKE '%{$keyword}%'
                )";
        }

        $categoryFilter = null;
        if ($categoryId > 0) {
            $categoryFilter = "AND `category_id` = '{$categoryId}'";
        }

        $supplierFilter = null;
        if ($supplierId > 0) {
            $supplierFilter = "AND  `tb_product_cost`.`supplier_id` = '{$supplierId}'";
        }


        $qr[1]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) <= 0)";
        $qr[2]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) <= alert_stock AND IFNULL(tb_stocks.total_qty, 0) > 0)";
        $qr[3]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) > alert_stock)";
        $qr[4]= "AND (IFNULL(alert_stock, '') = '')";

        $reportStatusFilter = null;
        $sql = "";
        $union = " ";

        if(count($arrReportStatus)>0){
            foreach($arrReportStatus as $index=>$value){
                    $reportStatusFilter = $qr[$value];
                    if($index>0){ $union = "UNION ";}else{ $union = " ";}
                    $sql .= $union.
                    "SELECT `{$productTable}`.*,
                    IFNULL(`tb_stocks`.`total_qty`, 0) AS quantity,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_incoming,
                    IFNULL(`tb_po_shipping`.`total_shipped`, 0) AS total_shipped,
                    IFNULL(`tb_ordered_qty`.`total_ordered_qty`, 0) AS total_ordered_qty,
                    IFNULL(`tb_stock_logs_in`.`stock_in`, 0) AS total_stock_in,
                    IFNULL(`tb_stock_logs_out`.`stock_out`, 0) AS total_stock_out,
                    `tb_reorders`.`reorder_shiptype`,
                    `tb_reorders`.`reorder_qty`,
                    `tb_product_cost`.`product_cost`,
                    `tb_product_cost`.`product_cost_default_supplier`,
                    `tb_product_cost`.`product_cost_currencies`,
                    `tb_product_cost`.`product_cost_suppliers`

                FROM `{$productTable}`
                LEFT JOIN (
                    SELECT `{$productStockTable}`.product_id,SUM(quantity) AS total_qty from `{$productStockTable}`
                            GROUP BY `{$productStockTable}`.product_id
                    )`tb_stocks` ON `tb_stocks`.product_id = `products`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_incoming
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    WHERE `{$orderPurchaseTable}`.`status` <> '{$orderPurchaseCloseStatus}'
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_incoming` ON `tb_incoming`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_ordered_qty
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    {$POdateFilter}
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_ordered_qty` ON `tb_ordered_qty`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$poShipmentDetailTable}`.product_id,
                     `{$poShipmentDetailTable}`.order_purchase_id,
                            SUM(`{$poShipmentDetailTable}`.`ship_quantity`) AS total_shipped
                    FROM `{$poShipmentDetailTable}`
                    LEFT JOIN `{$poShipmentTable}` ON `{$poShipmentTable}`.`id` = `{$poShipmentDetailTable}`.`po_shipment_id`
                    WHERE `{$poShipmentTable}`.`status` <> '{$poCloseStatus}'
                    {$poShipmentDetailTableFilter}
                    GROUP BY `{$poShipmentDetailTable}`.product_id
                ) `tb_po_shipping` ON `tb_po_shipping`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$productCostTable}`.product_id,
                    `{$productCostTable}`.`supplier_id`,
                    GROUP_CONCAT( `{$productCostTable}`.`default_supplier` SEPARATOR '##') AS product_cost_default_supplier,
                    GROUP_CONCAT( `{$productCostTable}`.`cost` SEPARATOR '##') AS product_cost,
                    GROUP_CONCAT( `{$exchangeRateTable}`.`name` SEPARATOR '##') AS product_cost_currencies,
                    GROUP_CONCAT( `{$supplierTable}`.`supplier_name` SEPARATOR '##') AS product_cost_suppliers
                    FROM `{$productCostTable}`
                    INNER JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$productCostTable}`.`exchange_rate_id`
                    INNER JOIN `{$supplierTable}` ON `{$supplierTable}`.`id` = `{$productCostTable}`.`supplier_id`
                    GROUP BY `{$productCostTable}`.product_id
                ) `tb_product_cost` ON `tb_product_cost`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_in
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=1
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_in` ON `tb_stock_logs_in`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_out
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=0
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_out` ON `tb_stock_logs_out`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$productReorderTable}`.`product_id`,
                    GROUP_CONCAT( `{$productReorderTable}`.`status` SEPARATOR ',') AS reorder_status,
                    GROUP_CONCAT( `{$shipTypeTable}`.`name` SEPARATOR ',') AS reorder_shiptype,
                    GROUP_CONCAT( `{$productReorderTable}`.`quantity` SEPARATOR ',') AS reorder_qty
                    FROM `{$productReorderTable}`
                    LEFT JOIN `{$shipTypeTable}` ON `{$shipTypeTable}`.`id` = `{$productReorderTable}`.`type`
                    GROUP BY `{$productReorderTable}`.product_id
                ) `tb_reorders` ON `tb_reorders`.product_id = `{$productTable}`.id



                WHERE `{$productTable}`.seller_id = {$sellerId}
                    {$reportStatusFilter}
                    {$searchFilter}
                    ";
            }
        }else{
            $sql = ' ';
            $sql = "
                SELECT `{$productTable}`.*,
                    IFNULL(`tb_stocks`.`total_qty`, 0) AS quantity,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_incoming,
                    IFNULL(`tb_po_shipping`.`total_shipped`, 0) AS total_shipped,
                    IFNULL(`tb_ordered_qty`.`total_ordered_qty`, 0) AS total_ordered_qty,
                    IFNULL(`tb_stock_logs_in`.`stock_in`, 0) AS total_stock_in,
                    IFNULL(`tb_stock_logs_out`.`stock_out`, 0) AS total_stock_out,
                    `tb_reorders`.`reorder_status`,
                    `tb_reorders`.`reorder_shiptype`,
                    `tb_reorders`.`reorder_qty`,
                    `tb_product_cost`.`product_cost_default_supplier`,
                    `tb_product_cost`.`product_cost`,
                    `tb_product_cost`.`product_cost_currencies`,
                    `tb_product_cost`.`product_cost_suppliers`

                FROM `{$productTable}`
                LEFT JOIN (
                    SELECT `{$productStockTable}`.product_id,SUM(quantity) AS total_qty from `{$productStockTable}`
                            GROUP BY `{$productStockTable}`.product_id
                    )`tb_stocks` ON `tb_stocks`.product_id = `products`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_incoming
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    WHERE `{$orderPurchaseTable}`.`status` <> '{$orderPurchaseCloseStatus}'
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_incoming` ON `tb_incoming`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_ordered_qty
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    {$POdateFilter}
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_ordered_qty` ON `tb_ordered_qty`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$poShipmentDetailTable}`.product_id,
                     `{$poShipmentDetailTable}`.order_purchase_id,
                            SUM(`{$poShipmentDetailTable}`.`ship_quantity`) AS total_shipped
                    FROM `{$poShipmentDetailTable}`
                    LEFT JOIN `{$poShipmentTable}` ON `{$poShipmentTable}`.`id` = `{$poShipmentDetailTable}`.`po_shipment_id`
                    WHERE `{$poShipmentTable}`.`status` <> '{$poCloseStatus}'
                    {$poShipmentDetailTableFilter}
                    GROUP BY `{$poShipmentDetailTable}`.product_id
                ) `tb_po_shipping` ON `tb_po_shipping`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_in
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=1
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_in` ON `tb_stock_logs_in`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_out
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=0
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_out` ON `tb_stock_logs_out`.product_id = `{$productTable}`.id



                LEFT JOIN (
                    SELECT `{$productReorderTable}`.`product_id`,
                    GROUP_CONCAT( `{$productReorderTable}`.`status` SEPARATOR ',') AS reorder_status,
                    GROUP_CONCAT( `{$shipTypeTable}`.`name` SEPARATOR ',') AS reorder_shiptype,
                    GROUP_CONCAT( `{$productReorderTable}`.`quantity` SEPARATOR ',') AS reorder_qty
                    FROM `{$productReorderTable}`
                    INNER JOIN `{$shipTypeTable}` ON `{$shipTypeTable}`.`id` = `{$productReorderTable}`.`type`
                    GROUP BY `{$productReorderTable}`.product_id
                ) `tb_reorders` ON `tb_reorders`.product_id = `{$productTable}`.id

                 LEFT JOIN (
                    SELECT `{$productCostTable}`.product_id,
                    `{$productCostTable}`.`supplier_id`,
                    GROUP_CONCAT( `{$productCostTable}`.`default_supplier` SEPARATOR '##') AS product_cost_default_supplier,
                    GROUP_CONCAT( `{$productCostTable}`.`cost` SEPARATOR '##') AS product_cost,
                    GROUP_CONCAT( `{$exchangeRateTable}`.`name` SEPARATOR '##') AS product_cost_currencies,
                    GROUP_CONCAT( `{$supplierTable}`.`supplier_name` SEPARATOR '##') AS product_cost_suppliers
                    FROM `{$productCostTable}`
                    INNER JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$productCostTable}`.`exchange_rate_id`
                    INNER JOIN `{$supplierTable}` ON `{$supplierTable}`.`id` = `{$productCostTable}`.`supplier_id`
                    GROUP BY `{$productCostTable}`.product_id
                ) `tb_product_cost` ON `tb_product_cost`.product_id = `{$productTable}`.id

                WHERE `{$productTable}`.seller_id = {$sellerId}
                {$reportStatusFilter}
                {$searchFilter}
                {$categoryFilter}
                {$supplierFilter}
                    ";
        }


        $limitFilter = NULL;
        if ($limit > 0) {
            $limitFilter = "LIMIT {$limit} OFFSET {$offset}";
        }

        //echo $sql;

        return DB::select(DB::raw("SELECT *
                    FROM (
                        $sql
                    ) `tb1`
                    WHERE 1 = 1

                        {$categoryFilter}
                    ORDER BY `{$orderColumn}` {$orderDir}
                    {$limitFilter}

                "));
    }

    /**
     *  Query for `Product Details with Stock,Incoming,Shipment` data
     *
     * @param  int  $sellerId
     * @param  int|null  $arrReportStatus
     * @param  array|null  $otherParams
     * @return int
     */
    public static function reportStockTableCount($sellerId, $arrReportStatus = null, $otherParams = null)
    {
        $productCostTable = (new ProductCost())->getTable();
        $productTable = (new Product())->getTable();
        $productStockTable = (new ProductMainStock())->getTable();
        $exchangeRateTable = (new ExchangeRate())->getTable();
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $productReorderTable = (new ProductReorders())->getTable();
        $shipTypeTable = (new ShipType())->getTable();
        $productReorderTable = (new ProductReorders())->getTable();
        $supplierTable = (new Supplier())->getTable();
        $stockLogsTable = (new StockLog())->getTable();


        $poShipmentTable = (new PoShipment())->getTable();
        $poShipmentDetailTable = (new PoShipmentDetail())->getTable();

        $orderPurchaseCloseStatus = OrderPurchase::STATUS_CLOSE;
        $poCloseStatus = PoShipmentDetail::PO_STATUS_CLOSE;

        $orderPurchaseTableFilter = NULL;
        $poShipmentDetailTableFilter = NULL;


        $categoryId = isset($otherParams['category_id']) ? $otherParams['category_id'] : 0;
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : 0;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : NULL;
        $dateFrom = isset($otherParams['dateFrom']) ? $otherParams['dateFrom'] : null;
        $dateTo = isset($otherParams['dateTo']) ? $otherParams['dateTo'] : null;


        $dateFilter = null;
        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateFilter = "AND `date` BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        }

        $POdateFilter = null;
        if (!empty($dateFrom) && !empty($dateTo)) {
            $POdateFilter = "WHERE `{$orderPurchaseTable}`.`order_date` BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        }


        $searchFilter = NULL;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    products.`product_name` LIKE '%{$keyword}%'
                    OR products.`product_code` LIKE '%{$keyword}%'
                )";
        }


        $categoryFilter = NULL;
        if ($categoryId > 0) {
            $categoryFilter = "AND `category_id` = '{$categoryId}'";
        }

        $supplierFilter = NULL;
        if ($supplierId > 0) {
            $supplierFilter = "AND  `tb_product_cost`.`supplier_id` = '{$supplierId}'";
        }

        $qr[1]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) <= 0)";
        $qr[2]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) <= alert_stock AND IFNULL(tb_stocks.total_qty, 0) > 0)";
        $qr[3]= "AND (IFNULL(alert_stock, 0) > 0 AND IFNULL(tb_stocks.total_qty, 0) > alert_stock)";
        $qr[4]= "AND (IFNULL(alert_stock, '') = '')";

        $reportStatusFilter = null;
        $sql = "";
        $union = " ";
        if(!empty($arrReportStatus)){
            foreach($arrReportStatus as $index=>$value){
                    $reportStatusFilter = $qr[$value];
                    if($index>0){ $union = "UNION ";}else{ $union = " ";}
                    $sql .= $union."SELECT `{$productTable}`.*,
                    IFNULL(`tb_stocks`.`total_qty`, 0) AS quantity,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_incoming,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_shipped,
                    IFNULL(`tb_ordered_qty`.`total_ordered_qty`, 0) AS total_ordered_qty,
                    IFNULL(`tb_stock_logs_in`.`stock_in`, 0) AS total_stock_in,
                    IFNULL(`tb_stock_logs_out`.`stock_out`, 0) AS total_stock_out

                FROM `{$productTable}`

                LEFT JOIN (
                    SELECT `{$productStockTable}`.product_id,SUM(quantity) AS total_qty from `{$productStockTable}`
                            GROUP BY `{$productStockTable}`.product_id
                    )`tb_stocks` ON `tb_stocks`.product_id = `products`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_incoming
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    WHERE `{$orderPurchaseTable}`.`status` <> '{$orderPurchaseCloseStatus}'
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_incoming` ON `tb_incoming`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_ordered_qty
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    {$POdateFilter}
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_ordered_qty` ON `tb_ordered_qty`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$poShipmentDetailTable}`.product_id,
                     `{$poShipmentDetailTable}`.order_purchase_id,
                            SUM(`{$poShipmentDetailTable}`.`ship_quantity`) AS total_shipped
                    FROM `{$poShipmentDetailTable}`
                    LEFT JOIN `{$poShipmentTable}` ON `{$poShipmentTable}`.`id` = `{$poShipmentDetailTable}`.`po_shipment_id`
                    WHERE `{$poShipmentTable}`.`status` <> '{$poCloseStatus}'
                    {$poShipmentDetailTableFilter}
                    GROUP BY `{$poShipmentDetailTable}`.product_id,`{$poShipmentDetailTable}`.order_purchase_id
                ) `tb_po_shipping` ON `tb_po_shipping`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_in
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=1
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_in` ON `tb_stock_logs_in`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_out
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=0
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_out` ON `tb_stock_logs_out`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$productReorderTable}`.`product_id`,
                    GROUP_CONCAT( `{$productReorderTable}`.`status` SEPARATOR ',') AS reorder_status,
                    GROUP_CONCAT( `{$shipTypeTable}`.`name` SEPARATOR ',') AS reorder_shiptype,
                    GROUP_CONCAT( `{$productReorderTable}`.`quantity` SEPARATOR ',') AS reorder_qty
                    FROM `{$productReorderTable}`
                    INNER JOIN `{$shipTypeTable}` ON `{$shipTypeTable}`.`id` = `{$productReorderTable}`.`type`
                    GROUP BY `{$productReorderTable}`.product_id
                ) `tb_reorders` ON `tb_reorders`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$productCostTable}`.product_id,
                    `{$productCostTable}`.supplier_id,
                    GROUP_CONCAT( `{$productCostTable}`.`default_supplier` SEPARATOR '##') AS product_cost_default_supplier,
                    GROUP_CONCAT( `{$productCostTable}`.`cost` SEPARATOR '##') AS product_cost,
                    GROUP_CONCAT( `{$exchangeRateTable}`.`name` SEPARATOR '##') AS product_cost_currencies,
                    GROUP_CONCAT( `{$supplierTable}`.`supplier_name` SEPARATOR '##') AS product_cost_suppliers
                    FROM `{$productCostTable}`
                    INNER JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$productCostTable}`.`exchange_rate_id`
                    INNER JOIN `{$supplierTable}` ON `{$supplierTable}`.`id` = `{$productCostTable}`.`supplier_id`
                    GROUP BY `{$productCostTable}`.product_id
                ) `tb_product_cost` ON `tb_product_cost`.product_id = `{$productTable}`.id


                WHERE `{$productTable}`.seller_id = {$sellerId}
                {$reportStatusFilter}
                {$searchFilter}
                {$categoryFilter}
                {$supplierFilter}
                ";
            }
        }else{

            $sql = "SELECT *
            FROM (
                SELECT `{$productTable}`.*,
                    IFNULL(`tb_stocks`.`total_qty`, 0) AS quantity,
                    IFNULL(`tb_incoming`.`total_incoming`, 0) AS total_incoming,
                    IFNULL(`tb_ordered_qty`.`total_ordered_qty`, 0) AS total_ordered_qty,
                    IFNULL(`tb_stock_logs_in`.`stock_in`, 0) AS total_stock_in,
                    IFNULL(`tb_stock_logs_out`.`stock_out`, 0) AS total_stock_out
                FROM `{$productTable}`
                LEFT JOIN (
                    SELECT `{$productStockTable}`.product_id,SUM(quantity) AS total_qty from `{$productStockTable}`
                            GROUP BY `{$productStockTable}`.product_id
                    )`tb_stocks` ON `tb_stocks`.product_id = `products`.id

                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_incoming
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    WHERE `{$orderPurchaseTable}`.`status` <> '{$orderPurchaseCloseStatus}'
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_incoming` ON `tb_incoming`.product_id = `{$productTable}`.id


                LEFT JOIN (
                    SELECT `{$orderPurchaseDetailTable}`.product_id,
                            SUM(`{$orderPurchaseDetailTable}`.`quantity`) AS total_ordered_qty
                    FROM `{$orderPurchaseDetailTable}`
                    INNER JOIN `{$orderPurchaseTable}` ON `{$orderPurchaseTable}`.`id` = `{$orderPurchaseDetailTable}`.`order_purchase_id`
                    {$POdateFilter}
                    GROUP BY `{$orderPurchaseDetailTable}`.product_id
                ) `tb_ordered_qty` ON `tb_ordered_qty`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_in
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=1
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_in` ON `tb_stock_logs_in`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$stockLogsTable}`.`product_id`,
                    SUM( `{$stockLogsTable}`.`quantity`) AS stock_out
                    FROM `{$stockLogsTable}`
                    WHERE `{$stockLogsTable}`.check_in_out=0
                    {$dateFilter}
                    GROUP BY `{$stockLogsTable}`.product_id
                ) `tb_stock_logs_out` ON `tb_stock_logs_out`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$productReorderTable}`.`product_id`,
                    GROUP_CONCAT( `{$productReorderTable}`.`status` SEPARATOR ',') AS reorder_status,
                    GROUP_CONCAT( `{$shipTypeTable}`.`name` SEPARATOR ',') AS reorder_shiptype,
                    GROUP_CONCAT( `{$productReorderTable}`.`quantity` SEPARATOR ',') AS reorder_qty
                    FROM `{$productReorderTable}`
                    INNER JOIN `{$shipTypeTable}` ON `{$shipTypeTable}`.`id` = `{$productReorderTable}`.`type`
                    GROUP BY `{$productReorderTable}`.product_id
                ) `tb_reorders` ON `tb_reorders`.product_id = `{$productTable}`.id

                LEFT JOIN (
                    SELECT `{$productCostTable}`.product_id,
                    `{$productCostTable}`.supplier_id,
                    GROUP_CONCAT( `{$productCostTable}`.`default_supplier` SEPARATOR '##') AS product_cost_default_supplier,
                    GROUP_CONCAT( `{$productCostTable}`.`cost` SEPARATOR '##') AS product_cost,
                    GROUP_CONCAT( `{$exchangeRateTable}`.`name` SEPARATOR '##') AS product_cost_currencies,
                    GROUP_CONCAT( `{$supplierTable}`.`supplier_name` SEPARATOR '##') AS product_cost_suppliers
                    FROM `{$productCostTable}`
                    INNER JOIN `{$exchangeRateTable}` ON `{$exchangeRateTable}`.`id` = `{$productCostTable}`.`exchange_rate_id`
                    INNER JOIN `{$supplierTable}` ON `{$supplierTable}`.`id` = `{$productCostTable}`.`supplier_id`
                    GROUP BY `{$productCostTable}`.product_id
                ) `tb_product_cost` ON `tb_product_cost`.product_id = `{$productTable}`.id


                WHERE `{$productTable}`.seller_id = {$sellerId}
                {$searchFilter}
                {$supplierFilter}
            ) `tb1`
            WHERE 1 = 1
                {$reportStatusFilter}
                {$categoryFilter}

                ";

        }

        $resultQuery = DB::select(DB::raw("SELECT COUNT(*) AS total
                            FROM (
                                $sql
                            ) tb1
                            WHERE 1 = 1

                                {$categoryFilter}
                        "));

        return $resultQuery[0]->total ?? 0;


    }



    /**
     * Query to filter by category
     *
     * @param  \Illuminate\Database\Query\Builder   $query
     * @param  int|nullable                         $categoryId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeCategory($query, $categoryId = null)
    {
        if ($categoryId > 0) {
            return $query->where('category_id', $categoryId);
        }

        return;
    }



    public static function getpendingStockAmount($product_id){

        $shipment_status_1 = Shipment::SHIPMENT_STATUS_PENDING_STOCK;

        $pendingStockAmount = ShipmentProduct::leftJoin('shipments', function($join) {
         $join->on('shipment_products.shipment_id', '=', 'shipments.id');
         })
        ->where('shipment_products.product_id', $product_id)
        ->where('shipments.shipment_status', $shipment_status_1)
        ->sum('quantity');

        if($pendingStockAmount == NULL || empty($pendingStockAmount)){
            $totalPendingQty = 0;

        }
        else{
            $totalPendingQty = $pendingStockAmount;
        }
        return $totalPendingQty;
    }

    public static function getReservedNotPaid($product_id){

        $order_status_1 = OrderManagement::ORDER_STATUS_PENDING;
        $order_status_2 = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
        $order_status_3 = OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED;

        $reservedNotPaidStockAmount = DB::select(DB::raw("
        SELECT SUM(quantity) AS reservedNotPaidStock
        FROM `order_management_details`
        LEFT JOIN order_managements ON order_management_details.order_management_id = order_managements.id
        WHERE order_management_details.product_id = $product_id
        AND order_managements.order_status IN($order_status_1, $order_status_2, $order_status_3)
        "));

        if($reservedNotPaidStockAmount[0]->reservedNotPaidStock == NULL || empty($reservedNotPaidStockAmount[0]->reservedNotPaidStock)){
            $reservedNotPaidQty = 0;

        }
        else{
            $reservedNotPaidQty = $reservedNotPaidStockAmount[0]->reservedNotPaidStock;
        }
        return $reservedNotPaidQty;
    }



    /**
     * Get All `products` By Seller ID
     * param int
     * @return mixed
     */
    public static function getProductsBySellerID($sellerId){
        return DB::table('products')->where('seller_id', $sellerId)->get();
   }

   public function scopeSearchSelectTwo($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function($query) use ($keyword) {
                $query->where('product_name', 'like', "%$keyword%");
                $query->orWhere('product_code', 'like', "%$keyword%");
            });
        }

        return;
    }

    /**
     * Query for stock value reports
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeStockValueReport($query)
    {
        $productsTable = $this->getTable();
        $productStockTable = (new ProductMainStock())->getTable();
        $productCostTable = (new ProductCost())->getTable();
        $exchangeRateTable = (new ExchangeRate())->getTable();

        $productCostWithExchangeRate = DB::table("{$productCostTable}")
            ->selectRaw("{$productCostTable}.*, {$exchangeRateTable}.rate AS exchange_rate")
            ->leftJoin("{$exchangeRateTable}", "{$exchangeRateTable}.id", "=", "{$productCostTable}.exchange_rate_id")
            ->where("default_supplier", "=", 1);

        return $query->selectRaw("{$productsTable}.*,
                IFNULL({$productStockTable}.quantity, 0) AS quantity,
                {$productsTable}.price * IFNULL({$productStockTable}.quantity, 0) AS stock_value,
                CAST(IFNULL(pc.cost, 0) AS FLOAT) AS pc_cost,
                CAST(IFNULL(pc.pieces_per_pack, 0) AS SIGNED) AS pc_pieces_per_pack,
                CAST(IFNULL(pc.operation_cost, 0) AS FLOAT) AS pc_operation_cost,
                CAST(IFNULL(pc.exchange_rate, 0) AS FLOAT) AS pc_exchange_rate,
                CAST(IFNULL(pc.cost, 0) * IFNULL(pc.exchange_rate, 0) * IFNULL(pc.pieces_per_pack, 0) * IFNULL(pc.operation_cost, 0) AS FLOAT) AS pc_cost_price,
                CAST(IFNULL(pc.cost, 0) * IFNULL(pc.exchange_rate, 0) * IFNULL(pc.pieces_per_pack, 0) * IFNULL(pc.operation_cost, 0) * IFNULL({$productStockTable}.quantity, 0) AS FLOAT) AS stock_cost_value,
                CAST(({$productsTable}.price - (IFNULL(pc.cost, 0) * IFNULL(pc.exchange_rate, 0) * IFNULL(pc.pieces_per_pack, 0) * IFNULL(pc.operation_cost, 0))) / {$productsTable}.price * 100 AS FLOAT) AS profit_margin")
            ->leftJoin("{$productStockTable}", "{$productStockTable}.product_id", "=", "{$productsTable}.id")
            ->leftJoinSub($productCostWithExchangeRate, "pc", function ($join) use ($productsTable) {
                $join->on("{$productsTable}.id", "=", "pc.product_id");
            });
    }
}
