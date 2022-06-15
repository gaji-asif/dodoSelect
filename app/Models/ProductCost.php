<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCost extends Model
{
    use HasFactory, SoftDeletes;

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
        'product_id',
        'cost',
        'operation_cost',
        'supplier_id'
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
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
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
     * Relationship to `exchange_rate` table
     *
     * @return mixed
     */
    public function exchangeRate()
    {
        return $this->belongsTo(ExchangeRate::class, 'exchange_rate_id', 'id')->withDefault(['name' => '']);
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
            $query->where('product_name', 'like', "%$keyword%")
                ->orWhere('product_code', 'like', "%$keyword%");
        }

        return;
    }

    public static function insertData($table,$data = []){
        DB::table($table)->insert($data);
        $id = DB::getPdo()->lastInsertId();
        return $id;
    }


    /**
     * Update product cost price and cost currency
     *
     * @param int $id
     * @param array $data
     */
    public static function updateData($id,$data = []){
        DB::table('product_costs')->where('id', $id)->update($data);
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!empty($this->attributes['image']) && file_exists(public_path($this->attributes['image']))) {
            return asset($this->attributes['image']);
        }

        return asset('No-Image-Found.png');
    }

    /**
     * Query for `report stock` data
     *
     * @param  int  $sellerId

     * @param  array|null  $otherParams
     * @return mixed
     */
    public static function ProductCostTable($sellerId, $otherParams = null)
    {
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : null;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;

        $offset = isset($otherParams['offset']) ? $otherParams['offset'] : 0;
        $limit = isset($otherParams['limit']) ? $otherParams['limit'] : 10;

        $orderColumn = isset($otherParams['order_column']) ? $otherParams['order_column'] : 'P.lowest_value';
        $orderDir = isset($otherParams['order_dir']) ? $otherParams['order_dir'] : 'asc';

        $searchFilter = null;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    P.product_name LIKE '%{$keyword}%'
                    OR P.product_code LIKE '%{$keyword}%'
                )";
        }

        $supplierFilter = null;
        if ($supplierId > 0) {
            $supplierFilter = "AND S.id = '{$supplierId}'";
        }

        return DB::select(DB::raw("SELECT *
                FROM(
                    SELECT P.*, S.supplier_name
                    FROM `products` P
                    LEFT JOIN `product_costs` PC ON P.id=PC.product_id
                    LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                    WHERE P.seller_id = {$sellerId}
                    {$searchFilter}
                    {$supplierFilter}
                    GROUP BY P.id
                    ORDER BY {$orderColumn} {$orderDir}
                ) tb1
                LIMIT {$limit} OFFSET {$offset}
                "));
    }

    /**
     * Query for `ProductCostTableCount` data counter
     *
     * @param  int  $sellerId
     * @param  array|null  $otherParams
     * @return int
     */
    public static function ProductCostTableCount($sellerId, $otherParams = null)
    {
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : null;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;

        $searchFilter = null;
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    P.product_name LIKE '%{$keyword}%'
                    OR P.product_code LIKE '%{$keyword}%'
                )";
        }

        $supplierFilter = null;
        if ($supplierId > 0) {
            $supplierFilter = "AND S.id = '{$supplierId}'";
        }

        $resultQuery = DB::select(DB::raw("
                            SELECT count(*) as total
                            FROM(
                                SELECT P.*, S.supplier_name
                                FROM `products` P
                                LEFT JOIN `product_costs` PC ON P.id=PC.product_id
                                LEFT JOIN `suppliers` S ON S.id=PC.supplier_id
                                WHERE P.seller_id = {$sellerId}
                                {$searchFilter}
                                {$supplierFilter}
                                GROUP BY P.id
                            ) tb1
                    "));

        return $resultQuery[0]->total ?? 0;
    }

    /**
     * Summary the total stock value and total stock cost value
     *
     * @param  int  $sellerId
     * @return mixed
     */
    public static function summaryStockValueBySeller(int $sellerId)
    {
        return collect(DB::select(DB::raw("
            SELECT SUM(p.price * pms.quantity) AS stock_value_sum,
                SUM(pc.cost
                    * pc.pieces_per_pack
                    * pc.operation_cost
                    * pms.quantity
                    * pc.exchange_rate
                    ) AS stock_cost_value_sum
            FROM products p
            LEFT JOIN product_main_stocks pms ON pms.product_id = p.id
            LEFT JOIN (
                SELECT pc.*, er.name AS exchange_name, IFNULL(er.rate, 0) AS exchange_rate
                FROM product_costs pc
                LEFT JOIN exchange_rate er ON er.id = pc.exchange_rate_id
                WHERE default_supplier = 1
            ) pc ON pc.product_id = p.id
            WHERE seller_id = {$sellerId}
                AND p.deleted_at IS NULL
        ")))->first();
    }
}
