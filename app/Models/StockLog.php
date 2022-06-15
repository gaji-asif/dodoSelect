<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Define `check_in_out` attributes
     *
     * @var mixed
     */
    CONST CHECK_IN_OUT_ADD = 1;
    CONST CHECK_IN_OUT_REMOVE = 0;

    /**
     * Define `defect_status` attributes
     *
     * @var mixed
     */
    CONST IS_DEFECT_YES = 1;
    CONST IS_DEFECT_NO = 0;

    /**
     * Define `defect_status` attributes
     *
     * @var mixed
     */
    CONST DEFECT_STATUS_OPEN = 'open';
    CONST DEFECT_STATUS_CLOSE = 'close';

    /**
     * Appends some custom fields
     *
     * @var array
     */
    public $appends = [
        'str_in_out',
    ];

    /**
     * Attributes should hidden
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Attributes should cast
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime',
        'quantity' => 'integer'
    ];

    /**
     * Get seller data
     *
     * @return BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    /**
     * Get staff data
     *
     * @return BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    /**
     * Get product data
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault([ 'product_name' => 'Unknown' ]);
    }

    /**
     * Get product's main stock
     *
     * @return BelongsTo
     */
    public function main_stock()
    {
        return $this->belongsTo(ProductMainStock::class, 'product_id');
    }

    /**
     * Accessor for `str_in_out` attribute
     *
     * @return string
     */
    public function getStrInOutAttribute()
    {
        $checkInOutAttribute = $this->attributes['check_in_out'] ?? -1;

        if ($checkInOutAttribute == self::CHECK_IN_OUT_ADD) {
            return 'Add';
        }

        if ($checkInOutAttribute == self::CHECK_IN_OUT_REMOVE) {
            return 'Remove';
        }

        return 'Unknown';
    }

//    /**
//     * Get total check in quantity of a product
//     *
//     * @return int
//     */
//    public function getProductTotalAddedAttribute(){
//        $product_id = $this->attributes['product_id'];
//        $added = DB::table('stock_logs')->where('is_defect', 0)
//            ->where('product_id', '=', $product_id)
//            ->where('seller_id', Auth::user()->id)
//            ->where('check_in_out', 1)
//            ->SUM('quantity');
//        return $added;
//    }

    /**
     * Query to get total CHECK IN quantity of a product within given date
     *
     * @param string $product_id
     * @param string|null $from_date
     * @param string|null $to_date
     * @return int
     */
    public static function productTotalAdded($product_id, $from_date = null, $to_date = null)
    {
        $query =  StockLog::where('product_id', '=', $product_id)
            ->where('is_defect', 0)
            ->where('check_in_out', 1)
            ->get();

        if (!empty($from_date)) {
            return $query->whereBetween('date', array(date('Y-m-d 00:00:00', strtotime($from_date)), date('Y-m-d 23:59:59', strtotime($to_date))))
                ->SUM('quantity');
        }
        return $query->SUM('quantity');
    }

    /**
     * Query to get total CHECK OUT quantity of a product within given date
     *
     * @param string $product_id
     * @param string|null $from_date
     * @param string|null $to_date
     * @return int
     */
    public static function productTotalRemoved($product_id, $from_date = null, $to_date = null)
    {
        $query =  StockLog::where('product_id', '=', $product_id)
            ->where('is_defect', 0)
            ->where('check_in_out', 0)
            ->get();

        if (!empty($from_date)) {
            return $query->whereBetween('date', array(date('Y-m-d 00:00:00', strtotime($from_date)), date('Y-m-d 23:59:59', strtotime($to_date))))
                ->SUM('quantity');
        }
        return $query->SUM('quantity');
    }

    /**
     * Query to get NET CHANGE of quantity of a product within given date
     *
     * @param string $product_id
     * @param string|null $from_date
     * @param string|null $to_date
     * @return int
     */
    public static function productNetChange($product_id, $from_date = null, $to_date = null)
    {
        if (!empty($from_date)) {
            $total_add = StockLog::productTotalAdded($product_id, $from_date, $to_date);
            $total_remove = StockLog::productTotalRemoved($product_id, $from_date, $to_date);
        } else{
            $total_add = StockLog::productTotalAdded($product_id);
            $total_remove = StockLog::productTotalRemoved($product_id);
        }
        return $total_add - $total_remove;
    }

    /**
     * Query to search from table
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchProductHistoryTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->whereHas('product', function(Builder $product) use ($keyword) {
                $product->searchTable($keyword);
            })
            ->orWhereHas('seller', function(Builder $seller) use ($keyword) {
                $seller->searchByName($keyword);
            })
            ->orWhereHas('staff', function(Builder $seller) use ($keyword) {
                $seller->searchByName($keyword);
            })
            ->orWhere(function(Builder $stock_log) use ($keyword) {
                $stock_log->where('date', 'like', "%$keyword%")
                        ->orWhere('quantity', 'like', "%$keyword%");
            });
        }

        return;
    }


    /**
     * Query to search from quantity logs table page.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchQuantityLogTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->where(function(Builder $stockLog) use ($keyword) {
                $stockLog->whereHas('seller', function(Builder $seller) use ($keyword) {
                        $seller->searchByName($keyword);
                    })
                    ->orWhereHas('staff', function(Builder $seller) use ($keyword) {
                        $seller->searchByName($keyword);
                    })
                    ->orWhere(function(Builder $stock_log) use ($keyword) {
                        $stock_log->where('date', 'like', "%$keyword%")
                                ->orWhere('quantity', 'like', "%$keyword%");
                    });
            });
        }

        return;
    }


    /**
     * SubQuery to make `product_name` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeProductNameAsColumn($query)
    {
        return $query->addSelect(['product_name' => Product::select('product_name')
            ->whereColumn('id', 'stock_logs.product_id')
            ->limit(1)
        ]);
    }


    /**
     * SubQuery to make `product_code` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeProductCodeAsColumn($query)
    {
        return $query->addSelect(['product_code' => Product::select('product_code')
            ->whereColumn('id', 'stock_logs.product_id')
            ->limit(1)
        ]);
    }

    /**
     * SubQuery to make `seller_name` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSellerNameAsColumn($query)
    {
        return $query->addSelect(['seller_name' => User::select('name')
            ->whereColumn('id', 'stock_logs.seller_id')
            ->limit(1)
        ]);
    }

    /**
     * Joined query for `defect_stock` datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedDefectStockDatatable($query)
    {
        $productsTable = (new Product())->getTable();
        $stockLogsTable = $this->getTable();

        return $query->join("{$productsTable}", "{$productsTable}.id", "{$stockLogsTable}.product_id");
    }

    /**
     * Searching query for `defect_stock` datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchDefectStockDatatable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            $query->where(function(Builder $query) use ($keyword) {
                $query->where('product_id', 'like', "%$keyword%")
                    ->orWhere('product_name', 'like', "%$keyword%")
                    ->orWhere('product_code', 'like', "%$keyword%");
            });
        }

        return;
    }


    /**
     * Get All `products` Stocks
     * param int
     * @return mixed
     */
    public static function getAllProductStocks(){
        return ProductMainStock::with('product')->get();
   }


    /**
     * Get Total Defect Stocks
     * param int
     * @return mixed
     */
    public static function getTotalDefectStocks(){
        return StockLog::where('is_defect', 1)
        ->where('seller_id', Auth::id())
        ->where('deffect_status', 'open')
        ->orderBy('id', 'desc')
        ->count();
   }

    /**
     * Get Last nth number of Changes 'Stock Data'
     * param int
     * @return mixed
     */
    // public static function getLastChangesStock($limit){
    //     return StockLog::where('seller_id', Auth::user()->id)
    //                 ->with('product')
    //                 ->with('main_stock')
    //                 ->with('seller')
    //                 ->with('staff')
    //                 ->orderBy('date', 'desc')
    //                 ->take($limit)
    //                 ->get();
    // }

    /**
     * Get Top nth number of 'Stock Data'
     * param int
     * @return mixed
     */
    // public static function getTopNthStock($limit){
    //     return ProductMainStock::with('product')
    //         ->orderBy('quantity', 'desc')
    //         ->take($limit)
    //         ->get();
    // }

    /**
     * Query for `Product Details with Stock,Incoming,Shipment` data
     *
     * @param  int  $sellerId
     * @param  array|null  $arrReportStatus
     * @param  array|null  $otherParams
     * @return mixed
     */
    public static function reportStockTable($sellerId, $otherParams = null)
    {
        $stockLogTable = (new StockLog())->getTable();
        $productTable = (new Product())->getTable();


        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;

        $offset = isset($otherParams['offset']) ? $otherParams['offset'] : 0;
        $limit = isset($otherParams['limit']) ? $otherParams['limit'] : 10;

        $orderColumn = isset($otherParams['order_column']) ? $otherParams['order_column'] : 'stock_logs.id';
        $orderDir = isset($otherParams['order_dir']) ? $otherParams['order_dir'] : 'desc';

        $searchFilter = null;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    `product_name` LIKE '%{$keyword}%'
                    OR `product_code` LIKE '%{$keyword}%'
                )";
        }


        $dateFilter = null;
        if(!empty($otherParams['from_date']) && !empty($otherParams['to_date'])){
            $from_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $to_date = date('Y-m-d 00:00:00', strtotime($otherParams['to_date']));
            $dateFilter = "AND `{$stockLogTable}`.`date` >= '{$from_date}' AND `{$stockLogTable}`.`date` < '{$to_date}'";
        } elseif(!empty($otherParams['from_date']) && empty($otherParams['to_date'])){
            $from_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $to_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $dateFilter = "AND `{$stockLogTable}`.`date` >= '{$from_date}' AND `{$stockLogTable}`.`date` < '{$to_date}'";
        } elseif(empty($otherParams['from_date']) && empty($otherParams['to_date'])){
            $dateFilter = null;
        }



        $limitFilter = NULL;
        if ($limit > 0) {
            $limitFilter = "LIMIT {$limit} OFFSET {$offset}";
        }

        $reportStatusFilter = null;
            $sql = "
            SELECT `{$stockLogTable}`.product_id,
            `{$stockLogTable}`.id,
            products.product_name,
            products.product_code,
            products.image,
            IFNULL(tb_added.total, 0) AS added,
            IFNULL(tb_removed.total, 0) AS removed,
            IFNULL(tb_added.total, 0) - IFNULL(tb_removed.total, 0) as net_change
            FROM `{$stockLogTable}`
            LEFT JOIN products ON products.id = `{$stockLogTable}`.product_id
            LEFT JOIN (
                SELECT `{$stockLogTable}`.product_id, SUM(`{$stockLogTable}`.quantity) as total FROM `{$stockLogTable}`
                WHERE `{$stockLogTable}`.is_defect = 0
                AND `{$stockLogTable}`.check_in_out = 1
                {$dateFilter}
                GROUP BY `{$stockLogTable}`.product_id
            ) tb_added ON tb_added.product_id = stock_logs.product_id

            LEFT JOIN (
                SELECT `{$stockLogTable}`.product_id, SUM(`{$stockLogTable}`.quantity) as total FROM `{$stockLogTable}`
                WHERE `{$stockLogTable}`.is_defect = 0
                AND `{$stockLogTable}`.check_in_out = 0
                {$dateFilter}
                GROUP BY `{$stockLogTable}`.product_id
            ) tb_removed ON tb_removed.product_id = stock_logs.product_id

            WHERE (tb_added.total <> 0 OR tb_removed.total <> 0)
            AND products.product_name IS NOT NULL

            GROUP BY stock_logs.product_id
            {$searchFilter}
            ORDER BY `{$orderColumn}` {$orderDir}
            {$limitFilter}
                    ";

        return DB::select(DB::raw("$sql"));
    }

    /**
     *  Query for `Product Details with Stock,Incoming,Shipment` data
     *
     * @param  int  $sellerId
     * @param  int|null  $arrReportStatus
     * @param  array|null  $otherParams
     * @return int
     */
    public static function reportStockTableCount($sellerId, $otherParams = null)
    {
        $stockLogTable = (new StockLog())->getTable();
        $productTable = (new Product())->getTable();



        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : 0;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : NULL;



        $searchFilter = NULL;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    products.`product_name` LIKE '%{$keyword}%'
                    OR products.`product_code` LIKE '%{$keyword}%'
                )";
        }

        $dateFilter = null;
        if(!empty($otherParams['from_date']) && !empty($otherParams['to_date'])){
            $from_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $to_date = date('Y-m-d 00:00:00', strtotime($otherParams['to_date']));
            $dateFilter = "AND `{$stockLogTable}`.`date` >= '{$from_date}' AND `{$stockLogTable}`.`date` < '{$to_date}'";
        } elseif(!empty($otherParams['from_date']) && empty($otherParams['to_date'])){
            $from_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $to_date = date('Y-m-d 00:00:00', strtotime($otherParams['from_date']));
            $dateFilter = "AND `{$stockLogTable}`.`date` >= '{$from_date}' AND `{$stockLogTable}`.`date` < '{$to_date}'";
        } elseif(empty($otherParams['from_date']) && empty($otherParams['to_date'])){
            $dateFilter = null;
        }

        $reportStatusFilter = null;


        $sql = "
            SELECT `{$stockLogTable}`.product_id,
            products.id,
            products.product_name,
            products.product_code,
            products.image,
            IFNULL(tb_added.total, 0) AS added,
            IFNULL(tb_removed.total, 0) AS removed,
            IFNULL(tb_added.total, 0) - IFNULL(tb_removed.total, 0) as net_change
            FROM `{$stockLogTable}`
            LEFT JOIN products ON products.id = `{$stockLogTable}`.product_id
            LEFT JOIN (
                SELECT `{$stockLogTable}`.product_id, SUM(`{$stockLogTable}`.quantity) as total FROM `{$stockLogTable}`
                WHERE `{$stockLogTable}`.is_defect = 0
                AND `{$stockLogTable}`.check_in_out = 1
                {$dateFilter}
                GROUP BY `{$stockLogTable}`.product_id
            ) tb_added ON tb_added.product_id = stock_logs.product_id

            LEFT JOIN (
                SELECT `{$stockLogTable}`.product_id, SUM(`{$stockLogTable}`.quantity) as total FROM `{$stockLogTable}`
                WHERE `{$stockLogTable}`.is_defect = 0
                AND `{$stockLogTable}`.check_in_out = 0
                {$dateFilter}
                GROUP BY `{$stockLogTable}`.product_id
            ) tb_removed ON tb_removed.product_id = stock_logs.product_id

            WHERE (tb_added.total <> 0 OR tb_removed.total <> 0)
            AND products.product_name IS NOT NULL

            GROUP BY stock_logs.product_id
            {$searchFilter}
                    ";



        $resultQuery = DB::select(DB::raw("SELECT COUNT(*) AS total
                            FROM (
                                $sql
                            ) tb1
                            WHERE 1 = 1


                        "));

        return $resultQuery[0]->total ?? 0;
    }

    /**
     * Query to filter by `check_in_out` field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $checkInOut
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByCheckInOut($query, $checkInOut)
    {
        return $query->where('check_in_out', $checkInOut);
    }

    /**
     * Query to filter by daterange time of `date` field
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  Date  $dateFrom
     * @param  Date  $dateTo
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeByDateRange($query, $dateFrom, $dateTo)
    {
        $datetimeFrom = Carbon::createFromDate($dateFrom)->format('Y-m-d 00:00:00');
        $datetimeTo = Carbon::createFromDate($dateTo)->format('Y-m-d 23:59:59');

        return $query->whereBetween('date', [$datetimeFrom, $datetimeTo]);
    }
}
