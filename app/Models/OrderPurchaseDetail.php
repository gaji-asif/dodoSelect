<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderPurchaseDetail extends Model
{
    use HasFactory;

    /**
     * Define value of `po_status` field
     *
     * @var string
     */
    CONST PO_STATUS_OPEN = 'open';
    CONST PO_STATUS_CLOSE = 'close';
    CONST PO_STATUS_ARRIVE = 'arrive';

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')
                ->withDefault([
                    'product_name' => '',
                    'product_code' => ''
                ])
                ->with('getQuantity')->with('getIncoming')
                ->with('productCostDetails');
    }

    /**
     * Relationship to `order_purchases` table
     *
     * @return mixed
     */
    public function orderPurchase()
    {
        return $this->belongsTo(OrderPurchase::class,'order_purchase_id', 'id');
    }

    /**
     * Relationsip to `product_reorders` table
     *
     * @return mixed
     */
    public function product_reorders()
    {
        return $this->hasMany(ProductReorders::class, 'product_id', 'product_id');
    }

       /**
     * Relationsip to `exchange_rate` table
     *
     * @return mixed
     */
    public function exchange_rate()
    {
        return $this->belongsTo(ExchangeRate::class, 'exchange_rate_id', 'id');
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
     * Relationship to `po_shipment_details` table
     *
     * @return mixed
     */
    public function po_shipment_details()
    {
        return DB::table('po_shipments')
        ->join('po_shipment_details', 'po_shipments.order_purchase_id', '=', 'po_shipment_details.order_purchase_id')
        ->where( 'po_shipment_details.product_id',$productId)
        ->groupBy( 'po_shipment_id')
        ->get();;
    }


    /**
     * Query to get incoming quantity only
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeIncomingQuantity($query)
    {
        $orderPurchaseTable = (new OrderPurchase())->getTable();
        $orderPurchaseDetailsTable = $this->getTable();

        return $query->selectRaw("{$orderPurchaseDetailsTable}.*, {$orderPurchaseTable}.status")
                ->join("{$orderPurchaseTable}", "{$orderPurchaseTable}.id", '=', "{$orderPurchaseDetailsTable}.order_purchase_id")
                ->where("{$orderPurchaseTable}.status", '<>', OrderPurchase::STATUS_CLOSE);
    }

    /**
     * Query to join with `products` table
     * contact the product_code
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinedProduct($query)
    {
        $orderPurchaseDetailsTable = (new OrderPurchaseDetail())->getTable();
        $productsTable = (new Product())->getTable();

        return $query->selectRaw("{$orderPurchaseDetailsTable}.*,
                {$productsTable}.product_code as products_code")
            ->leftJoin("{$productsTable}", function ($join) use ($orderPurchaseDetailsTable, $productsTable) {
                $join->on("{$orderPurchaseDetailsTable}.product_id", "=", "{$productsTable}.id")
                    ->where("{$productsTable}.deleted_at", null);
            });
    }

    /**
     * Update Status By order_purchase_id
     *
     * @return
     */
    public static function updatePOStatusByPOId($order_purchase_id,$status)
    {
        DB::table('order_purchase_details')->where('order_purchase_id',$order_purchase_id)->update(['po_status' =>$status]);
    }

    /**
     * Delete From `order_purchase_details` table By order_purchase_id
     *
     * @return
     */
    public static function deletePoDetailsByID($order_purchase_id,$sellerId)
    {
        DB::table('order_purchase_details')->where('order_purchase_id', $order_purchase_id)->where('seller_id',$sellerId)->delete();
    }

}
