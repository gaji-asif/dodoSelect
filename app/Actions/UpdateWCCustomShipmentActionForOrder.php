<?php

namespace App\Actions;

use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UpdateWCCustomShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipmentDetails = Shipment::find($data['shipment_id']);
            $shipmentDetails->shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            $shipmentDetails->order_id = $data['order_id'];
            $shipmentDetails->shipment_status = $data['shipment_status'];
            $shipmentDetails->updated_at = new DateTime();
            $shipmentDetails->save();

            ShipmentProduct::where('shipment_id', $data['shipment_id'])->delete();

           if(isset($data['shipment_qty'])){
            foreach ($data['shipment_qty'] as $idx => $shipment_qtys) {

                $shipmentDetailsData = new ShipmentProduct();
                $shipmentDetailsData->shipment_id = $data['shipment_id'];
                $shipmentDetailsData->order_id =  $data['order_id'];
                $shipmentDetailsData->product_id = $data['product_id'][$idx];
                if(!empty($data['shipment_qty'][$idx])){
                    $shipmentDetailsData->quantity = $data['shipment_qty'][$idx];
                }
                else{
                    $shipmentDetailsData->quantity = 0;
                }
                
                $shipmentDetailsData->updated_at = new DateTime();
                $shipmentDetailsData->save();
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
                //$ordered_details->status = WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP;
                //$ordered_details->updated_at = new DateTime();
                //$ordered_details->save();
            }
            if($ordered_products != $total_shipped_products){
                //$ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSING;
                //$ordered_details->updated_at = new DateTime();
                //$ordered_details->save();
            }

            $shipment = Shipment::where('id', $data['shipment_id'])->first();

            DB::commit();

            return $shipment;

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            throw $th;
        }
        
     }

      
        
    
}