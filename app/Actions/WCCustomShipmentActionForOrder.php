<?php

namespace App\Actions;

use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WCCustomShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipment_date = null;
            if (!empty($data['shipment_date'])){
                $shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            }

            $shipmentId = DB::table($shipmentTable)
                    ->insertGetId([
                        'shipment_date' => $shipment_date,
                        'order_id' => $data['order_id'],
                        'shipment_status' => $data['shipment_status'],
                        'seller_id' => $data['seller_id'],
                        'is_custom' => 1,
                        'shipment_for' => Shipment::SHIPMENT_FOR_WOO,
                        'shop_id' => $data['shop_id'],
                        'created_at' => new DateTime()
                    ]);

            if(isset($data['product_id'])){
            foreach ($data['product_id'] as $idx => $productId) {
                $shipmentDetailsData = [
                    'shipment_id' => $shipmentId,
                    'order_id' => $data['order_id'],
                    'product_id' => $data['product_id'][$idx],
                    'quantity' => $data['shipment_qty'][$idx] ?? 0,
                    'created_at' => new DateTime()
                ];

                DB::table($shipmentProductsTable)->insert($shipmentDetailsData);
             }
            }

           
            $ordered_products = 0;
            $ordered_details = WooOrderPurchase::where('order_id',$data['order_id'])->where('seller_id',Auth::user()->id)->first();
            $orderProductDetails = json_decode($ordered_details->line_items);
            if(!empty($orderProductDetails)){
                foreach ($orderProductDetails as $product) {
                    $ordered_products += $product->quantity;
                }
            }

            $total_shipped_products = ShipmentProduct::where('shipment_products.order_id',$data['order_id'])
            ->where('shipments.shipment_for',Shipment::SHIPMENT_FOR_WOO)
            ->where('shipments.is_custom',1)
            ->leftJoin('shipments', 'shipments.id', '=', 'shipment_products.shipment_id')
            ->sum('shipment_products.quantity');

            if($ordered_products == $total_shipped_products){
                //$ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
               // $ordered_details->updated_at = new DateTime();
               // $ordered_details->save();
            }
            if($ordered_products != $total_shipped_products){
                //$ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSING;
                //$ordered_details->updated_at = new DateTime();
                //$ordered_details->save();
            }

            $shipment = Shipment::where('id', $shipmentId)->first();

            DB::commit();

            return $shipment;

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            throw $th;
        }
    }
}
