<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PoShipmentDetail extends Model
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
     * Get Product data
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')
                ->withDefault([
                    'product_name' => '',
                    'product_code' => ''
                ])
                ->with('getQuantity')
                ->with('getIncoming')
                ->with('productCostDetails');
    }

    /**
     * Get PoShipmentDetail table
     *
     * @return HasMany
     */
    public function getShipped(){
        return $this->hasMany(PoShipmentDetail::class, 'order_purchase_id', 'order_purchase_id')
        ->select('product_id',DB::raw('po_shipment_details.order_purchase_id,sum(ship_quantity) as totalShipped'))
        ->groupBy(['product_id','order_purchase_id']);
    }

    /**
     * Get Order Purchase data
     *
     * @return BelongsTo
     */
    public function orderPurchase()
    {
        return $this->belongsTo(OrderPurchase::class, 'order_purchase_id', 'id');
    }

    /**
     * Get Exchange rate data
     *
     * @return BelongsTo
     */
    public function exchange_rate()
    {
        return $this->belongsTo(ExchangeRate::class, 'exchange_rate_id', 'id');
    }

    /**
     * Get supplier data
     *
     * @return BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id', 'id');
    }

    /**
     * Get seller data
     *
     * @return BelongsTo
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    // Delete Exisiting data from po_shipment_details by po_shipment_id
    public static function getDataByPoShipmentID($order_purchase_id){
        DB::table('po_shipment_details')->where('order_purchase_id', $order_purchase_id)->get();
    }

    // Delete Exisiting data from po_shipment_details by po_shipment_id
    public static function deleteByPoShipmentID($po_shipment_id){
        DB::table('po_shipment_details')->where('po_shipment_id', $po_shipment_id)->delete();
   }

   /**
     * Delete From `order_purchase_details` table By order_purchase_id
     *
     * @return
     */
    public static function deletePoShipmentDetailsByPOID($order_purchase_id)
    {
        DB::table('po_shipment_details')->where('order_purchase_id', $order_purchase_id)->delete();
    }


     /**
     * Update Po Shipments
     *
     * @param int old po id,new po id
     * @param null
     */
    public static function updatePoShipmentDetailsPurchaseID($old_order_purchase_id,$order_purchase_id){

        DB::table('po_shipment_details')->where('order_purchase_id', $old_order_purchase_id)
        ->update([
            'order_purchase_id' =>$order_purchase_id
        ]);
    }


}
